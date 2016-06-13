<?php

namespace CleanCode\Controllers\WithWidgets;

/**
 * Class IndexController
 * @package CleanCode\Controllers\WithWidgets
 */
class IndexController extends ControllerBase
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $topDestinations = $this->content->getContentData('top-destinations', []);
        $this->prepareDestinationsViewData($topDestinations);
        $this->view->setVar('destinations', $topDestinations);

        $this->pageSeo->setPageTitle('My page title');
        $this->pageSeo->setPageDescription('My page description');

        $this->view->setVar('regions', $this->getRegionsData());
    }

    /**
     * @param array $data
     * @return array
     */
    protected function prepareDestinationsViewData(array &$data)
    {
        // long legacy code is here
    }

    /**
     * Get Rooms set to display on the homepage
     *
     * @return array
     */
    protected function getFeaturedCampsites()
    {
        // long legacy code is here
    }
}
