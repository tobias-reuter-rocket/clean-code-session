<?php
/**
 * @author Rocket Internet SE
 * @copyright Copyright (c) 2015 Rocket Internet SE, Johannisstraße 20, 10117 Berlin, http://www.rocket-internet.de
 */

namespace Component\Cms\Service;

use Common\Db\Service\ServiceAbstract;
use Phalcon\Http\Request;
use Spot\Query;

/**
 * @see booster/docs/content/components/cms.md
 */
abstract class BaseContentService extends ServiceAbstract
{
    /**
     * @param array $files
     * @return Query
     */
    public function findUsageOfFiles(array $files)
    {
        /** @var Query $query */
        $query = $this->getMapper();
        foreach ($files as $i => $searchString) {
            $condition = ['content :like' => '%"' . $searchString . '"%'];

            $query = ($i == 0) ? $query->where($condition) : $query->orWhere($condition);
        }

        return $query;
    }
}
