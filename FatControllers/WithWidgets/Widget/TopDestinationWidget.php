<?php

namespace CleanCode\Controllers\WithWidgets\Widget;

use CleanCode\Controllers\WithWidgets\WidgetCore\Widget;

/**
 * Class TopDestinationWidget
 * @package CleanCode\Controllers\WithWidgets\Widget
 */
class TopDestinationWidget extends Widget
{
    /** array */
    private $topDestinations;

    public function init()
    {
        $this->topDestinations = $this->content->getContentData('top-destinations', []);
        $this->prepareDestinationsViewData();
    }


    public function run()
    {
        $this->render('topDestinationWidget', ['destinations' => $this->topDestinations]);
    }

    /**
     * @return array
     */
    protected function prepareDestinationsViewData()
    {
        // here there is a long code for preparing data
    }

}
