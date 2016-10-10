<?php

namespace CleanCode\Controllers\WithWidgets;

/**
 * Class IndexController
 * @package Frontend\Controller
 */
class IndexController extends ControllerBase
{
    /**
     * @return void
     */
    public function indexAction()
    {
        $this->pageSeo->setPageTitle('My page title');
        $this->pageSeo->setPageDescription('My page description');
    }
}
