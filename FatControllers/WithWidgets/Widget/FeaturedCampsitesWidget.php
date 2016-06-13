<?php

namespace CleanCode\Controllers\WithWidgets\Widget;

use CleanCode\Controllers\WithWidgets\WidgetCore\Widget;

/**
 * Class FeaturedCampsitesWidget
 * @package CleanCode\Controllers\WithWidgets\Widget
 */
class FeaturedCampsitesWidget extends Widget
{
    /** @var int number of the campsites shown */
    private $limit = 6;

    /**
     * @var array
     */
    private $data = [];

    /**
     * FeaturedCampsitesWidget constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($params['limit'])) {
            $this->limit = $params['limit'];
        }
    }

    public function init()
    {
        $campsites = $this->search->getHomePageProperties($this->limit);
        if (empty($campsites)) {
            return [];
        }
    }

    public function run()
    {
        $this->render('featureCampsitesWidget', [
            'featuredCampsites' => $this->data
        ]);
    }
}
