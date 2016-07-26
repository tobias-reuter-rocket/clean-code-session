<?php
/**
 * @author Rocket Internet AG
 * @copyright Copyright (c) 2015 Rocket Internet AG, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 * @created 24.02.15 17:54
 */

namespace Common\Cms\Service;

use Admin\AdminRoutes;
use Common\Admin\Service\AdminService;
use Common\Authorization\Service\FeatureService;
use Common\Cms\Entity\Content;
use Common\Cms\Entity\ContentRevision;
use Common\Cms\Mapper\ContentMapper;
use Common\Db\Entity\Admin;
use Common\Db\Entity\EntityAbstract;
use Common\Db\Fixture\FixtureServiceInterface;
use Common\Db\Service\ServiceAbstract;
use Common\Db\Service\Traits\CleanUpTrait;
use Common\Db\Service\Traits\CreateTrait;
use Common\Db\Service\Traits\DeleteTrait;
use Common\Db\Service\Traits\ExistsTrait;
use Common\Db\Service\Traits\FindTrait;
use Common\Form\Validator\NotExistsValidationService;
use Common\Localisation\Locale\LocaleInterface;
use Common\Localisation\Translator\TranslatorInterface;
use Common\Localisation\TranslatorFactory;
use Common\AttachmentManager\AttachmentManager;
use Common\Db\Service\Traits\BuildInterface;
use Common\Db\Service\Traits\BuildTrait;
use Common\Db\Service\Traits\GetInterface;
use Common\Db\Service\Traits\GetTrait;
use Common\Db\Service\Traits\MapperTrait;
use Common\Mvc\Application;
use Common\Plugin\Auth;
use Common\Token\Service\TokenService;
use Common\User\AuthenticationInterface;
use Common\Util\Slug;
use Phalcon\Cache\Multiple;
use Phalcon\Http\Request;
use Phalcon\Http\Response\Cookies;
use Spot\Query;

/**
 * Class Content
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) TODO: ContentService needs refactoring (TooManyPublicMethods).
 * @SuppressWarnings(PHPMD.TooManyMethods) TODO: ContentService needs refactoring (TooManyMethods).
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) TODO: ContentService needs refactoring (ExcessiveClassComplexity).
 *
 * @package Common\Service\Cms
 */
class ContentService extends ServiceAbstract implements
    NotExistsValidationService,
    GetInterface,
    BuildInterface,
    FixtureServiceInterface
{
    use BuildTrait;
    use CleanUpTrait;
    use CreateTrait;
    use DeleteTrait {
        DeleteTrait::delete as traitDelete;
    }
    use ExistsTrait;
    use GetTrait;
    use FindTrait;
    use MapperTrait;
    use FindUsageOfFilesTrait;

    /**
     * Authorization Feature name for the CMS preview
     */
    const PREVIEW_FEATURE = 'cms_preview';

    /**
     * @var ContentMapper
     */
    protected $mapper = null;

    /**
     * @var ContentRevisionService
     */
    protected $contentRevision = null;
    /**
     * @var AttachmentManager
     */
    protected $attachmentManager = null;

    /**
     * @const string
     */
    const SUB_BLOCKS_KEYS_REGEX = '/\\{cmsblock key=([a-zA-Z0-9-\\_\\-\\/\.]+)\\}/';

    /**
     * @const string
     */
    const SUB_BLOCKS_MACRO = '{cmsblock key=%s}';

    /**
     * Content Key Regexp
     */
    const PARAM_CMS_REGEXP = '([a-zA-Z0-9-\\_\\-\\/]+)';

    /**
     * @var Slug
     */
    protected $slug = null;

    /**
     * @var TranslatorFactory
     */
    private $translatorFactory;

    /**
     * @var AdminService
     */
    private $adminService;

    /**
     * @var LocaleInterface
     */
    protected $localeService;

    /**
     * @var Multiple
     */
    protected $cache;

    /**
     * @var TokenService
     */
    protected $token;

    /**
     * @var FeatureService
     */
    protected $feature;

    /**
     * @var TranslatorInterface
     */
    protected $translate;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList) TODO: ContentService needs refactoring (ExcessiveParameterList).
     *
     * @param ContentMapper $mapper
     * @param ContentRevisionService $contentRevision
     * @param Slug $slug
     * @param AttachmentManager $attachmentManager
     * @param TranslatorFactory $translatorFactory
     * @param AdminService $adminService
     * @param LocaleInterface $localeService
     * @param Multiple $cache
     * @param TokenService $token
     * @param FeatureService $feature
     * @param TranslatorInterface $translate
     */
    public function __construct(
        ContentMapper $mapper,
        ContentRevisionService $contentRevision,
        Slug $slug,
        AttachmentManager $attachmentManager,
        TranslatorFactory $translatorFactory,
        AdminService $adminService,
        LocaleInterface $localeService,
        Multiple $cache,
        TokenService $token,
        FeatureService $feature,
        TranslatorInterface $translate
    ) {
        $this->mapper = $mapper;
        $this->contentRevision = $contentRevision;
        $this->slug = $slug;
        $this->attachmentManager = $attachmentManager;
        $this->translatorFactory = $translatorFactory;
        $this->adminService = $adminService;
        $this->localeService = $localeService;
        $this->cache = $cache;
        $this->token = $token;
        $this->feature = $feature;
        $this->translate = $translate;
    }

    /**
     * @param $type
     * @return \Spot\Query
     */
    public function findAllByType($type)
    {
        $contentMapper = $this->getMapper();

        return $contentMapper->where([
            'type' => $type
        ]);
    }

    /**
     * @param $type
     * @return Content
     */
    public function getEmptyByType($type)
    {
        /** @var Content $entity */
        $entity = $this->buildEmptyEntity();
        $entity->setType($type);
        $entity->setStatus($entity::STATUS_PUBLISHED);
        $entity->setDeleted(false);
        $entity->setLanguage($this->translatorFactory->getFallbackLocale());

        return $entity;
    }

    /**
     * @param Content|EntityAbstract $content
     * @return bool
     */
    public function save(EntityAbstract $content)
    {
        if (empty($content->dataModified('content')) === false) {
            $this->processContent($content);
        }
        $saved = $this->getMapper()->save($content);

        $this->clearCacheByEntity($content);

        return $saved;
    }

    /**
     * @param Content $content
     * @return mixed
     */
    public function delete(Content $content)
    {
        $revisions = $content->getContentRevision();

        foreach ($revisions as $revision) {
            $this->contentRevision->delete($revision);
        }

        $this->clearCacheByEntity($content);

        return $this->traitDelete($content);
    }

    /**
     * @param Content $content
     * @return bool
     */
    public function restore(Content $content)
    {
        $revisions = $content->getContentRevision();

        foreach ($revisions as $revision) {
            $revision->setDeleted(false);
            $this->contentRevision->save($revision);
        }

        $content->setDeleted(false);
        return $this->save($content);
    }

    /**
     * @inheritdoc
     *
     * @param null $entityId Entity ID which should be excluded on validation / look up
     * @param array $queryParameters Assoc array
     * @return boolean
     */
    public function validateNotExists($entityId = null, array $queryParameters = null)
    {
        $not = [];
        if ($entityId) {
            $not['id <>'] = $entityId;
        }
        $queryParameters = $queryParameters + $not;

        return !(boolean)$this->getMapper()->first($queryParameters);
    }

    /**
     * Generate slug based on content title
     *
     * @param $data array Data coming from form-save request. It should contain at least title field
     * @return string
     */
    public function generateSlugFromData(array $data)
    {
        $data = array_map('trim', $data);
        $data = array_filter($data);

        if (isset($data['key'])) {
            return mb_strtolower($data['key']);
        }
        if (isset($data['title'])) {
            return $this->slug->generate($data['title']);
        }

        return '';
    }

    /**
     * Generate slug based on content title
     *
     * @param string $title
     * @return string
     */
    public function generateSlugFromTitle($title)
    {
        return $this->slug->generate($title);
    }

    /**
     * @param Content $content
     */
    public function togglePublishingStatus(Content $content)
    {
        $newStatus = ($content->getStatus() == Content::STATUS_PUBLISHED)
            ? Content::STATUS_UNPUBLISHED
            : Content::STATUS_PUBLISHED;
        $content->setStatus($newStatus);
        $this->save($content);
        $this->clearCacheByEntity($content);
    }

    /**
     * Create content entity from revision entity
     *
     * @param ContentRevision $revision
     * @return Content
     */
    public function createFromContentRevision(ContentRevision $revision)
    {
        $data = $revision->toArray();
        unset($data['id'], $data['content_id']);

        /**
         * @var Content $content
         */
        $content = $this->buildEntityFromArray($data);
        $content->isNew(false);
        $content->setId($revision->getContentId());

        $this->clearCacheByEntity($content);

        return $content;
    }

    /**
     * Find page by its key
     *
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    public function findPageByReference($key, $module = APP_MODULE)
    {
        return $this->findContentByReference(Content::TYPE_PAGE, $key, $module);
    }

    /**
     * Find block by its key
     *
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    public function findBlockByReference($key, $module = APP_MODULE)
    {
        return $this->findContentByReference(Content::TYPE_BLOCK, $key, $module)
            ?: $this->getEmptyByType(Content::TYPE_BLOCK);
    }

    /**
     * Find content by its type and key
     *
     * @param string $type
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    public function findContentByReference($type, $key, $module = APP_MODULE)
    {
        $key = trim($key, '/');

        if ($this->feature->has(self::PREVIEW_FEATURE)) {
            $draft = $this->contentRevision->findDraft($type, $key, $module, $this->localeService->getLocale());
            if ($draft) {
                $content = $this->contentRevision->restoreDraft($draft->getParent(), $draft);
                return $this->preProcessContent($content);
            }

            return $this->retrieveContentFromDatabase($type, $key, $module, $this->localeService->getLocale(), true);
        }

        $pageCacheKey = $this->getCacheKey($type, $this->localeService->getLocale(), $key);
        $fromCache = $this->cache->get($pageCacheKey);
        if (!is_null($fromCache)) {
            return $fromCache;
        }

        $page = $this->retrieveContentFromDatabase($type, $key, $module, $this->localeService->getLocale());
        if ($page) {
            $this->cache->save($pageCacheKey, $page);
        }

        return $page;
    }

    /**
     * @param string $type
     * @param string $key
     * @param string $module
     * @param string $locale
     * @param bool $preview
     * @return bool|Content
     */
    protected function retrieveContentFromDatabase($type, $key, $module, $locale, $preview = false)
    {
        $query = $this->getMapper()
            ->where(['type' => $type, 'key' => $key, 'module' => $module, 'language' => $locale]);

        if (!$preview) {
            /** @noinspection PhpUndefinedMethodInspection */
            $query->published()->active();
        }

        $first = $query->first();

        return false === $first
            ? false
            : $this->preProcessContent($first);
    }

    /**
     * Get allowed status list
     * @return array
     */
    public function getStatusList()
    {
        $fields = Content::fields();

        return $fields['status']['options'];
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
    protected function preProcessContent(Content $content)
    {
        $content = $this->parseContentForSubBlocks($content);
        $content = $this->transformContentToHtml($content);
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
            self::SUB_BLOCKS_KEYS_REGEX,
            $contentBody,
            $cmsKeys
        );

        if ($numMatches < 1) {
            return $content;
        }

        foreach ($cmsKeys[1] as $key) {
            $block = $this->findBlockByReference($key, $content->getModule());
            $keyMacro = sprintf(self::SUB_BLOCKS_MACRO, $key);
            $contentBody = str_replace($keyMacro, $block->getContent() ?: '', $contentBody);
        }
        $content->setContent($contentBody);

        return $content;
    }

    /**
     * Transforms content to HTML if the different type is diffenrent.
     *
     * If content-type is html, nothing happens.
     *
     * @param Content $content
     * @return Content
     */
    protected function transformContentToHtml(Content $content)
    {
        if ($content->getContentType() == $content::CONTENT_TYPE_MARKDOWN) {
            $this->parseMarkdownContent($content);
        }

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
        $parser = new \cebe\markdown\Markdown();
        $parser->html5 = true;
        $parsedContent = $parser->parse($content->getContent());
        $content->setContent($parsedContent);
    }

    /**
     * Processes and modifies the content before storing it to db.
     *
     * Modifies $content
     *
     * @param Content $content
     * @throws \Common\AttachmentManager\Exception\InvalidMediaType
     */
    protected function processContent(Content $content)
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
     * @param Content $content
     * @return \Spot\EntityInterface|Admin
     */
    public function getUpdatedByUsername(Content $content)
    {
        $updatedBy = $content->getUpdatedBy();
        $user = null;
        if (array_key_exists('reference_id', $updatedBy)) {
            $user = $this->adminService->findById($updatedBy['reference_id']);
        }

        return $user ?: $this->adminService->buildEmptyEntity();
    }

    /**
     * @param Content $contentToDuplicate
     * @return Content
     */
    public function createDuplicate(Content $contentToDuplicate)
    {
        $alternateGroupingId = $contentToDuplicate->getAlternateGroupingId() ?: $contentToDuplicate->getId();

        $data = $contentToDuplicate->toArray();
        unset($data['id']);

        /**
         * @var Content $content
         */
        $content = $this->buildEntityFromArray($data);
        $content->setTitle(
            $this->translate->_('cms.content.duplicate.titlewrapper', ['title' => $content->getTitle()])
        );
        $content->setAlternateGroupingId($alternateGroupingId);

        return $content;
    }

    /**
     * @param Content $content
     */
    public function clearCacheByEntity(Content $content)
    {
        if (Content::TYPE_BLOCK === $content->getType()) {
            $this->clearSuperCacheBySubBlock($content);
        }

        $key = $this->getCacheKey($content->getType(), $content->getLanguage(), $content->getKey());
        if ($this->cache->exists($key)) {
            $this->cache->delete($key);
        }
    }

    /**
     * clear all the content (pages|blocks) (super content) that includes a given blocks (sub content)
     *
     * @param Content $subBlock
     */
    protected function clearSuperCacheBySubBlock(Content $subBlock)
    {
        $contents = $this->findSuperBySubBlock($subBlock);
        if ($contents->count() < 1) {
            return;
        }
        /** @var Content $content */
        foreach ($contents as $content) {
            // Avoid infinite recursion in the event the block references itself
            if ($content->getKey() != $subBlock->getKey()) {
                $this->clearCacheByEntity($content);
            }
        }
    }

    /**
     * @param string $type
     * @param string $locale
     * @param string $key
     * @return string
     */
    protected function getCacheKey($type, $locale, $key)
    {
        return 'content_' . $type . '_' . $locale . '_' . $key;
    }

    /**
     * finds all the content (pages|blocks) (super content) that includes other blocks (sub content)
     *
     * @param Content $subBlock
     * @return \Spot\Query
     */
    public function findSuperBySubBlock(Content $subBlock)
    {
        $contentMapper = $this->getMapper();
        $keyMacro = sprintf(self::SUB_BLOCKS_MACRO, $subBlock->getKey());

        return $contentMapper->where([
            'content :like' =>  '%' . $keyMacro . '%',
            'language' => $subBlock->getLanguage()
        ]);
    }

    /**
     * @return string[]
     */
    public function getUniqueKeyFields()
    {
        return ['key', 'type', 'language', 'module'];
    }

    /**
     * find out if media files are used
     * currently there are two places to look for: content, content revisions
     *
     * @param string[] $files URLs
     * @return array
     */
    public function checkMediaIsUsed($files)
    {
        if (empty($files)) {
            return [];
        }

        $used = [];
        // searching in cms content pages/blocks
        $usages = $this->findUsageOfFiles($files);
        foreach ($usages as $entry) {
            $used[$entry->getKey()][] = [
                'type' => 'content',
                'content_type' => $entry->getType(),
                'id' => $entry->getId(),
            ];
        }
        // searching in cms content revisions
        $usages = $this->contentRevision->findUsageOfFiles($files);
        foreach ($usages as $entry) {
            $used[$entry->getKey()][] = [
                'type' => 'revision',
                'content_type' => $entry->getType(),
                'id' => $entry->getId(),
            ];
        }

        return $used;
    }

    /**
     * Transforms a list of usages of a media to a list with links to sources
     *
     * @param array $usages [key => [type, id]]
     * @return array
     */
    public function addLinksToMediaUsagesList($usages)
    {
        $list = [];

        foreach ($usages as $key => $usage) {
            $line = [];
            foreach ($usage as $data) {
                switch ($data['type']) {
                    case 'content':
                        $line[] = '<a href="'.$this->url->get([
                                'for' => $data['content_type'] == 'page'
                                    ? AdminRoutes::CONTENT_PAGE_EDIT
                                    : AdminRoutes::CONTENT_BLOCK_EDIT,
                                'id' => $data['id'],
                            ]).'">content</a>';
                        break;
                    case 'revision':
                        $line[] = '<a href="'.$this->url->get([
                                'for' => AdminRoutes::REVISION_SHOW,
                                'id' => $data['id'],
                            ]).'">revision</a>';
                        break;
                    default:
                        $line[] = $data['type'];
                }
            }
            $list[] = $key . ': ' . implode(', ', $line);
        }

        return $list;
    }
    
    /**
     * Check if a key is deleted.
     *
     * @param $key
     * @param string $module
     * @return bool
     */
    public function isKeyDeleted($key, $module = Application::MODULE_FRONTEND)
    {
        $count = $this->mapper->where(['key' => $key, 'deleted' => true, 'type' => Content::TYPE_PAGE, 'module' => $module])->count();

        return (bool) $count;
    }
}
