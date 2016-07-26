<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2015 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 */

namespace Component\Cms\Service;

use Component\Cms\Entity\Content;
use Phalcon\Cache\Multiple;
use Phalcon\Http\Request;
use Spot\Query;

/**
 * @see booster/docs/content/components/cms.md
 */
class ContentCacheService
{
    /**
     * @var Multiple
     */
    protected $cache;

    /**
     * @var ContentService
     */
    protected $content;

    /**
     * @param Multiple $cache
     */
    public function __construct(Multiple $cache)
    {
        $this->cache = $cache;
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
     * @param string $type
     * @param string $locale
     * @param string $key
     * @return string
     */
    public function get($type, $locale, $key)
    {
        return $this->cache->get($this->buildCacheKey($type, $locale, $key));
    }

    /**
     * @param string $type
     * @param string $locale
     * @param string $key
     * @param string $content
     */
    public function save($type, $locale, $key, $content)
    {
        $this->cache->save(
            $this->buildCacheKey($type, $locale, $key),
            $content
        );
    }

    /**
     * @param Content $content
     */
    public function clearByEntity(Content $content)
    {
        if (Content::TYPE_BLOCK === $content->getType()) {
            $this->clearSuperCacheBySubBlock($content);
        }

        $key = $this->buildCacheKey($content->getType(), $content->getLanguage(), $content->getKey());
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
        /** @var Content $content */
        $contents = $this->content->findSuperBySubBlock($subBlock);
        if ($contents->count() < 1) {
            return;
        }

        foreach ($contents as $content) {
            // Avoid infinite recursion in the event the block references itself
            if ($content->getKey() != $subBlock->getKey()) {
                $this->clearByEntity($content);
            }
        }
    }

    /**
     * @param string $type
     * @param string $locale
     * @param string $key
     * @return string
     */
    protected function buildCacheKey($type, $locale, $key)
    {
        return 'content_' . $type . '_' . $locale . '_' . $key;
    }
}
