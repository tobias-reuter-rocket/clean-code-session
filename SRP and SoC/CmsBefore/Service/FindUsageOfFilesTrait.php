<?php

namespace Common\Cms\Service;

use Spot\Query;

/**
 * Trait FindUsageOfFilesTrait
 * @package Common\Cms\Service
 *
 * @method Query getMapper()
 */
trait FindUsageOfFilesTrait
{
    /**
     * find usage of media files in content
     *
     * @param array $files
     * @return Query
     */
    public function findUsageOfFiles($files)
    {
        $query = $this->getMapper();
        foreach ($files as $cnt => $searchString) {
            $condition = ['content :like' => '%"' . $searchString . '"%'];

            $query = ($cnt == 0) ? $query->where($condition) : $query->orWhere($condition);
        }

        return $query;
    }
}
