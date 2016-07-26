<?php

namespace Component\Cms\Service;

use Common\Authorization\Service\FeatureService;
use Component\Cms\Entity\Content;
use Component\Cms\Entity\ContentRevision;
use Component\Cms\Mapper\ContentMapper;
use Common\Db\Entity\EntityAbstract;
use Common\Db\Fixture\FixtureServiceInterface;
use Common\Db\Service\Traits\CleanUpTrait;
use Common\Db\Service\Traits\CrudTrait;
use Common\Form\Validator\NotExistsValidationService;
use Common\Localisation\Translator\TranslatorInterface;
use Common\Db\Service\Traits\BuildInterface;
use Common\Db\Service\Traits\GetInterface;
use Common\Db\Service\Traits\GetTrait;
use Common\Mvc\Application;
use Phalcon\Http\Request;
use Spot\Query;

/**
 * ContentService is a CRUD for CMS entities. Caching, processing and revisions are delegated to subservices.
 * @see booster/docs/content/components/cms.md
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ContentService extends BaseContentService implements
    NotExistsValidationService,
    GetInterface,
    BuildInterface,
    FixtureServiceInterface
{
    use CrudTrait {
        CrudTrait::delete as traitDelete;
    }
    use GetTrait;
    use CleanUpTrait;

    /**
     * Authorization Feature name for the CMS preview
     */
    const PREVIEW_FEATURE = 'cms_preview';

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
     * @var ContentMapper
     */
    protected $mapper;

    /**
     * @var ContentRevisionService
     */
    public $contentRevision;

    /**
     * @var ContentCacheService
     */
    protected $contentCache;

    /**
     * @var ContentProcessorService
     */
    protected $contentProcessor;

    /**
     * @var FeatureService
     */
    protected $feature;

    /**
     * @var TranslatorInterface
     */
    protected $translate;

    /**
     * @var string
     */
    protected $fallbackLocale;

    /**
     * @param ContentMapper $mapper
     * @param ContentRevisionService $contentRevision
     * @param ContentCacheService $contentCache
     * @param ContentProcessorService $contentProcessor
     * @param FeatureService $feature
     * @param TranslatorInterface $translate
     * @param string $fallbackLocale
     */
    public function __construct(
        ContentMapper $mapper,
        ContentRevisionService $contentRevision,
        ContentCacheService $contentCache,
        ContentProcessorService $contentProcessor,
        FeatureService $feature,
        TranslatorInterface $translate,
        $fallbackLocale
    ) {
        parent::__construct($mapper);
        $this->contentRevision = $contentRevision;
        $this->contentCache = $contentCache;
        $this->contentProcessor = $contentProcessor;
        $this->feature = $feature;
        $this->translate = $translate;
        $this->fallbackLocale = $fallbackLocale;
    }

    /**
     * @param string $type
     * @return \Spot\Query
     */
    public function findAllByType($type)
    {
        return $this->getMapper()->where([
            'type' => $type
        ]);
    }

    /**
     * @param string $type
     * @return Content
     */
    public function buildEmptyByType($type)
    {
        /** @var Content $entity */
        $entity = $this->buildEmptyEntity();
        $entity->setType($type);
        $entity->setStatus($entity::STATUS_PUBLISHED);
        $entity->setDeleted(false);
        $entity->setLanguage($this->fallbackLocale);

        return $entity;
    }

    /**
     * @param Content|EntityAbstract $content
     * @return bool
     */
    public function save(EntityAbstract $content)
    {
        if (empty($content->dataModified('content')) === false) {
            $this->contentProcessor->processBeforeSave($content);
        }
        $saved = $this->getMapper()->save($content);

        $this->contentCache->clearByEntity($content);

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

        $this->contentCache->clearByEntity($content);

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
     * @param int|null $entityId
     * @param array $queryParameters
     * @return bool
     */
    public function validateNotExists($entityId = null, array $queryParameters = null)
    {
        $not = [];
        if ($entityId) {
            $not['id <>'] = $entityId;
        }
        $queryParameters = $queryParameters + $not;

        return !$this->getMapper()->first($queryParameters);
    }

    /**
     * @param array $data
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
            return $this->generateSlug($data['title']);
        }

        return '';
    }

    /**
     * Creates a slug to be used for pretty URLs
     *
     * @link http://cubiq.org/the-perfect-php-clean-url-generator
     * @param  string $string
     * @param  array  $replace
     * @param  string $delimiter
     * @return string
     */
    protected static function generateSlug($string, array $replace = array(), $delimiter = '-')
    {
        // Save the old locale and set the new locale to UTF-8
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        $clean = str_replace((array) $replace, ' ', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower($clean);
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
        $clean = trim($clean, $delimiter);

        // Revert back to the old locale
        setlocale(LC_ALL, $oldLocale);

        return $clean;
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
    }

    /**
     * @param ContentRevision $revision
     * @return Content
     */
    public function createFromContentRevision(ContentRevision $revision)
    {
        $data = $revision->toArray();
        unset($data['id'], $data['content_id']);

        /** @var Content $content */
        $content = $this->buildEntityFromArray($data);
        $content->isNew(false);
        $content->setId($revision->getContentId());

        return $content;
    }

    /**
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    public function findPageByReference($key, $module = APP_MODULE)
    {
        return $this->findContentByReference(Content::TYPE_PAGE, $key, $module);
    }

    /**
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    public function findBlockByReference($key, $module = APP_MODULE)
    {
        return $this->findContentByReference(Content::TYPE_BLOCK, $key, $module)
            ?: $this->buildEmptyByType(Content::TYPE_BLOCK);
    }

    /**
     * @param string $type
     * @param string $key
     * @param string $module
     * @return false|Content
     */
    protected function findContentByReference($type, $key, $module = APP_MODULE)
    {
        $key = trim($key, '/');
        $locale = $this->translate->getLocale();

        if ($this->feature->has(self::PREVIEW_FEATURE)) {
            $draft = $this->contentRevision->findDraft($type, $key, $module, $locale);
            if ($draft) {
                $content = $this->contentRevision->restoreDraft($draft->getParent(), $draft);
                return $this->contentProcessor->processBeforeDisplay($content);
            }

            return $this->retrieveContentFromDatabase($type, $key, $module, $locale, true);
        }

        $fromCache = $this->contentCache->get($type, $locale, $key);
        if (!is_null($fromCache)) {
            return $fromCache;
        }

        $page = $this->retrieveContentFromDatabase($type, $key, $module, $locale);
        if ($page) {
            $this->contentCache->save($type, $locale, $key, $page);
        }

        return $page;
    }

    /**
     * @param string $type
     * @param string $key
     * @param string $module
     * @param string $locale
     * @param bool $preview
     * @return Content|false
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
            : $this->contentProcessor->processBeforeDisplay($first);
    }

    /**
     * @return array
     */
    public function getStatusList()
    {
        $fields = Content::fields();

        return $fields['status']['options'];
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

        /** @var Content $content */
        $content = $this->buildEntityFromArray($data);
        $content->setTitle(
            $this->translate->_('cms.content.duplicate.titlewrapper', ['title' => $content->getTitle()])
        );
        $content->setAlternateGroupingId($alternateGroupingId);

        return $content;
    }

    /**
     * finds all the content (pages|blocks) (super content) that includes other blocks (sub content)
     *
     * @param Content $subBlock
     * @return \Spot\Query
     */
    public function findSuperBySubBlock(Content $subBlock)
    {
        $keyMacro = sprintf(self::SUB_BLOCKS_MACRO, $subBlock->getKey());

        return $this->getMapper()->where([
            'content :like' =>  '%' . $keyMacro . '%',
            'language' => $subBlock->getLanguage()
        ]);
    }

    /**
     * finds all the content (pages|blocks) that includes a $keyPrefix
     *
     * @param string $keyPrefix
     * @return \Spot\Query
     */
    public function findPagesByKeyPrefix($keyPrefix)
    {
        $query = $this->getMapper()->where([
            'key :like' =>  $keyPrefix . '%'
        ]);

        return $query->published()->active();
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
    public function checkMediaIsUsed(array $files)
    {
        if (empty($files)) {
            return [];
        }

        $used = [];

        foreach ($this->findUsageOfFiles($files) as $entry) {
            $used[$entry->getKey()][] = [
                'type' => 'content',
                'content_type' => $entry->getType(),
                'id' => $entry->getId(),
            ];
        }

        foreach ($this->contentRevision->findUsageOfFiles($files) as $entry) {
            $used[$entry->getKey()][] = [
                'type' => 'revision',
                'content_type' => $entry->getType(),
                'id' => $entry->getId(),
            ];
        }

        return $used;
    }
    
    /**
     * @param string $key
     * @param string $module
     * @return bool
     */
    public function isPageDeleted($key, $module = Application::MODULE_FRONTEND)
    {
        $count = $this->mapper->where([
            'key' => $key,
            'deleted' => true,
            'type' => Content::TYPE_PAGE,
            'module' => $module
        ])->count();

        return (bool) $count;
    }
}
