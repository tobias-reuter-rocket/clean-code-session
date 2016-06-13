<?php

namespace Common\Cms\Service;

use Common\Cms\Entity\Content;
use Common\Cms\Entity\ContentRevision;
use Common\Db\Service\Traits\CrudInterface;
use Common\Db\Service\Traits\CrudTrait;
use Common\Db\Service\Traits\GetInterface;
use Common\Db\Service\Traits\GetTrait;
use Common\Db\Service\ServiceAbstract;
use Common\Seo\Entity\SeoOptions;
use Spot\Query;

/**
 * Class ContentRevisionService
 * @package Common\Cms\Service
 */
class ContentRevisionService extends ServiceAbstract implements GetInterface, CrudInterface
{
    use CrudTrait;
    use GetTrait;
    use FindUsageOfFilesTrait;

    protected static $copyBlacklist = [
        'created_by',
        'created_at',
        'updated_by',
        'updated_at',
        'deleted',
    ];

    /**
     * Get revisions ordered by id
     *
     * @param $contentId
     * @return \Spot\Query
     */
    public function findAllByContentId($contentId)
    {
        return $this->getMapper()
            ->where(['content_id' => $contentId, 'status <>' => ContentRevision::STATUS_DRAFT])
            ->order(['id' => 'DESC']);
    }

    /**
     * Create revision from content
     *
     * @param Content $content
     * @param SeoOptions|null $seo
     * @return ContentRevision
     */
    public function createFromContent(Content $content, SeoOptions $seo = null)
    {
        $data = $content->toArray();

        $data['content_id'] = $content->getId();
        unset($data['id']);

        if ($seo && $seo->getEntityId()) {
            $data['seo_settings'] = $seo->getDataArray();
        }

        /** @var ContentRevision $revision */
        $revision = $this->getMapper()->create($data);

        return $revision;
    }

    /**
     * Returns the latest editions
     * @param int $count
     * @return Query
     */
    public function findLatest($count = 10)
    {
        return $this->getMapper()
            ->where(['status <>' => ContentRevision::STATUS_DRAFT])
            ->order(['created_at' => 'DESC'])->limit($count);
    }

    /**
     * Finds first revision for a given $contentId
     * @param $contentId
     * @return ContentRevision|null
     */
    public function findFirstByContentId($contentId)
    {
        return $this->getMapper()
            ->where(['content_id' => $contentId])
            ->order(['id' => 'ASC'])
            ->first();
    }

    /**
     * Finds the draft version of given Content
     *
     * @param Content $content
     * @return ContentRevision|false
     */
    public function findDraftForContent(Content $content)
    {
        return $this->getMapper()
            ->where([
                'content_id' => $content->getId(),
                'status' => ContentRevision::STATUS_DRAFT,
                'deleted' => 0,
            ])
            ->order(['id' => 'DESC'])
            ->first();
    }

    /**
     * Finds the draft version of given Content, by key and module
     *
     * @param string $type
     * @param string $key
     * @param string $module
     * @param string $locale
     * @return ContentRevision|false
     */
    public function findDraft($type, $key, $module, $locale)
    {
        $query = $this->getMapper()
            ->where(['type' => $type, 'key' => $key, 'status' => ContentRevision::STATUS_DRAFT, 'language' => $locale])
            ->with('parent')
            ->order(['id' => 'DESC']);

        /** @var ContentRevision $draft */
        foreach ($query as $draft) {
            if ($draft->getParent()->getModule() === $module) {
                return $draft;
            }
        }

        return false;
    }

    /**
     * Copies Content data to the Draft entity
     *
     * @param Content $content
     * @param ContentRevision $draft
     * @return ContentRevision|false
     */
    public function saveDraft(Content $content, ContentRevision $draft)
    {
        $data = $content->toArray();

        $data['content_id'] = $content->getId();
        unset($data['id']);
        $data['status'] = ContentRevision::STATUS_DRAFT;

        foreach ($data as $field => $value) {
            if (!in_array($field, static::$copyBlacklist)) {
                $draft->set($field, $value);
            }
        }

        return $this->save($draft);
    }

    /**
     * Copies Draft data to the Content entity
     *
     * @param Content $content
     * @param ContentRevision $draft
     * @return Content
     */
    public function restoreDraft(Content $content, ContentRevision $draft)
    {
        $data = $draft->toArray();

        unset($data['id']);
        unset($data['content_id']);
        unset($data['status']);

        foreach ($data as $field => $value) {
            if (!in_array($field, static::$copyBlacklist)) {
                $content->set($field, $value);
            }
        }

        return $content;
    }

}
