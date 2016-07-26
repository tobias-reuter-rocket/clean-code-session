<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2015 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 */

namespace Component\Cms\Service;

use cebe\markdown\Markdown;
use Common\AttachmentManager\AttachmentManager;
use Component\Cms\Entity\Content;
use Common\Mvc\Url;
use Phalcon\Http\Request;
use Spot\Query;

/**
 * @see booster/docs/content/components/cms.md
 */
class ContentProcessorService
{
    /**
     * @var ContentService
     */
    protected $content;

    /**
     * @var AttachmentManager
     */
    protected $attachmentManager;

    /**
     * @var URL
     */
    protected $url;

    /**
     * @param AttachmentManager $attachmentManager
     * @param Url $url
     */
    public function __construct(
        AttachmentManager $attachmentManager,
        Url $url
    ) {
        $this->attachmentManager = $attachmentManager;
        $this->url = $url;
    }

    /**
     * @param ContentService $contentService
     * @return $this
     */
    public function setContentService(ContentService $contentService)
    {
        $this->content = $contentService;

        return $this;
    }

    /**
     * Processes and modifies the content before storing it to db.
     *
     * Modifies $content
     *
     * @param Content $content
     * @throws \Common\AttachmentManager\Exception\InvalidMediaType
     */
    public function processBeforeSave(Content $content)
    {
        if ($content->getContentType() !== Content::CONTENT_TYPE_HTML) {
            return;
        }

        try {
            $this->processImages($content);
        } catch (\Exception $e) {
            $content->error('content', $e->getMessage());
        }

        $content->setContent(trim($content->getContent()));
    }

    /**
     * Automatically downloads remote images in img-tags and replaces src value to a local reference.
     * Also updates style attributes with new sizes when appropriate.
     *
     * Modifies $content
     *
     * @param Content $content
     */
    protected function processImages(Content $content)
    {
        $imgList = [];

        if (!preg_match_all('/<img[^>]*>/i', $content->getContent(), $images)) {
            return;
        }

        foreach ($images[0] as $img) {
            $src = $this->getHtmlTagAttribute($img, 'src');

            if (!$this->attachmentManager->isLocal($src)) {
                if (strpos($src, '://') === false) {
                    // relative image
                    $imgList['old'][] = $img;
                    $imgList['new'][] = str_replace($src, $this->url->getStatic($src), $img);
                }

                continue;
            }

            $result = $this->importImage($img);
            if (!empty($result)) {
                list($oldImg, $newImg) = $result;
                $imgList['old'][] = $oldImg;
                $imgList['new'][] = $newImg;
            }
        }

        if (!empty($imgList)) {
            $content->setContent(
                str_replace($imgList['old'], $imgList['new'], $content->getContent())
            );
        }
    }

    /**
     * Download remote image and if successful, return old and new img tag tuple.
     *
     * @param string $img
     * @return array
     * @throws \Exception
     */
    protected function importImage($img)
    {
        $src = $this->getHtmlTagAttribute($img, 'src');
        if (empty($src) === true) {
            return [];
        }

        $oldStyle = $this->getHtmlTagAttribute($img, 'style');
        $styles = $this->getSizesFromStyles($oldStyle ?: '');

        try {
            $newSize = $this->attachmentManager->resizeAndUploadImage($src, $styles['width'], $styles['height']);
        } catch (\Exception $e) {
            throw new \Exception('Cannot load media from ' . $src);
        }

        if (empty($newSize)) {
            return [];
        }

        // copy img tag and set new src and style attribute values
        $newImg = str_replace($src, $newSize->getUrl(), $img);
        if ($oldStyle) {
            $newStyle = $this->addSizesToStyles(
                $newSize->getWidth(),
                $newSize->getHeight(),
                $styles['otherStyles']
            );
            $newImg = str_replace($oldStyle, $newStyle, $newImg);
        }

        return [ $img, $newImg ];
    }

    /**
     * Return passed html tag attribute's value.
     *
     * @param string $tag
     * @param string $attribute
     * @return string|null
     */
    protected function getHtmlTagAttribute($tag, $attribute)
    {
        $pattern = sprintf('/%s\s*=\s*"([^"]*)"/i', $attribute);
        if (preg_match($pattern, $tag, $value) && isset($value[1])) {
            return $value[1];
        }

        return null;
    }

    /**
     * Return combined style attribute with sizes and existing style information passed.
     *
     * @param int $width
     * @param int $height
     * @param string $styles
     * @return string
     */
    protected function addSizesToStyles($width, $height, $styles)
    {
        if (!$width || !$height) {
            return $styles;
        }

        return sprintf(
            'width: %dpx; height: %dpx; %s',
            $width,
            $height,
            $styles
        );
    }

    /**
     * Extract width, height and remaining styles and return them as a separate array elements.
     *
     * @param string $style
     * @return array
     */
    protected function getSizesFromStyles($style)
    {
        $result = [
            'height' => null,
            'width' => null,
            'otherStyles' => null
        ];

        $styleAttrs = explode(';', $style);
        foreach ($styleAttrs as $key => &$propertyString) {
            $properties = explode(':', $propertyString);
            $properties = array_map('trim', $properties);
            if (array_key_exists($properties[0], $result)) {
                $result[$properties[0]] = round((int)rtrim($properties[1], 'px%'));
                unset($styleAttrs[$key]);
            }
        }

        $result['otherStyles'] = trim(implode(';', $styleAttrs), '; ');

        return $result;
    }

    /**
     * Processes and modifies the content before displaying it.
     *
     * Evaluates SubBlock-Macros.
     * Transforms to html if content-type is different.
     *
     * @param Content $content
     * @return Content|void
     */
    public function processBeforeDisplay(Content $content)
    {
        $content = $this->parseContentForSubBlocks($content);

        if ($content->getContentType() == $content::CONTENT_TYPE_MARKDOWN) {
            $this->parseMarkdownContent($content);
        }

        return $content;
    }

    /**
     * looks for all the strings that matches SUB_BLOCKS_KEYS_REGEX and replace the given keys with the specified block
     *
     * @param Content $content
     * @return Content
     */
    protected function parseContentForSubBlocks(Content $content)
    {
        $contentBody = $content->getContent();

        $numMatches = preg_match_all(
            ContentService::SUB_BLOCKS_KEYS_REGEX,
            $contentBody,
            $cmsKeys
        );

        if ($numMatches < 1) {
            return $content;
        }

        foreach ($cmsKeys[1] as $key) {
            $block = $this->content->findBlockByReference($key, $content->getModule());
            $keyMacro = sprintf(ContentService::SUB_BLOCKS_MACRO, $key);
            $contentBody = str_replace($keyMacro, $block->getContent() ?: '', $contentBody);
        }
        $content->setContent($contentBody);

        return $content;
    }

    /**
     * Parse content as markdown into html.
     *
     * Modifies $content
     *
     * @param Content $content
     */
    protected function parseMarkdownContent(Content $content)
    {
        $parser = new Markdown();
        $parser->html5 = true;
        $parsedContent = $parser->parse($content->getContent());
        $content->setContent($parsedContent);
    }
}
