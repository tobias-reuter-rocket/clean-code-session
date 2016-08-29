<?php

namespace CleanCode\SimpleRefactoring;

/**
 * There is often a method of blocks of related lines of code that each perform a sub-task of the whole problem.
 * Sometimes the code blocks are then provided with commentaries, as the following example:
 *
 */
class AspectsSeparateExtractMethod
{
    public function initialize()
    {
        parent::initialize();

        // content blocks
        $this->content = $this->contentFactory->getByType(Content::TYPE_BLOCK);
        $blocks = $this->content->getContentBlocksData([
            'phone-number',
            'cookie-popup',
            'head-block',
            'rebranding-popup'
        ], '');

        // UI: JS modules, body classes, layout
        $this->gtmDataLayer = $this->googleTagManager->createInitialDataLayer();

        if ($this->config->tracking->gtm->enabled) {
            $this->addClientModules(['tracking']);
        }
        if ($this->config->application->useCampsy) {
            $this->addBodyClass('campsy');
        }
        $this->setAssetGroup('splash');
        $this->addClientModules([]);
        $this->addBodyClass('theme-silver page-signin');

        $this->view->setViewsDir(APP_DIR . '/Frontend/intoyo-views');
        $this->view->setMainView('layouts/main');
        $this->view->gtmDataLayer = $this->gtmDataLayer;
        $this->view->viewDataHelper = new ViewDataHelper();
        $this->view->formViewHelper = new FormViewHelper();


        $this->view->setVar('phoneNumber', $blocks['phone-number']);
        $this->view->setVar('pitches', $this->search->getPitches());

        // show cookie popup
        $this->view->setVar('displayCookie', !$this->cookies->has('ckWg'));
        $this->view->setVar('cookiePopupContent', $blocks['cookie-popup']);
        $this->view->setVar('headBlock', $blocks['head-block']);

        // rebranding cookie popup
        $displayRebranding = !$this->cookies->has('ckCYF')
            && !$this->cookies->has('ckCYOk')
            && $this->cookies->has('ckWg')
            && is_array($blocks['rebranding-popup'])
            && $blocks['rebranding-popup']['showBlock'] == true;

        $this->view->setVar('rebrandingPopupContent', $blocks['rebranding-popup']);
        $this->view->setVar('displayRebranding', $displayRebranding);
    }
}



/** Refactoring */

/**
 * Class AspectsSeparateExtractMethod2
 * Readability can be significantly improved by the individual code blocks are swapped in methods.
 */
class AspectsSeparateExtractMethod2
{
    public function initialize()
    {
        parent::initialize();

        $this->setUI();

        $blocks = $this->getContentBlocks();

        $this->view->setVar('phoneNumber', $blocks['phone-number']);
        $this->view->setVar('pitches', $this->search->getPitches());
        $this->view->setVar('headBlock', $blocks['head-block']);

        $this->setCookieBlocks($blocks);
    }

    /**
     * @return array
     */
    private function getContentBlocks()
    {
        $this->content = $this->contentFactory->getByType(Content::TYPE_BLOCK);
        $blocks = $this->content->getContentBlocksData([
            'phone-number',
            'cookie-popup',
            'head-block',
            'rebranding-popup'
        ], '');

        return $blocks;
    }

    /**
     * JS modules, body classes, layout
     */
    private function setUI()
    {
        $this->gtmDataLayer = $this->googleTagManager->createInitialDataLayer();

        if ($this->config->tracking->gtm->enabled) {
            $this->addClientModules(['tracking']);
        }
        if ($this->config->application->useCampsy) {
            $this->addBodyClass('campsy');
        }
        $this->setAssetGroup('splash');
        $this->addClientModules([]);
        $this->addBodyClass('theme-silver page-signin');

        $this->view->setViewsDir(APP_DIR . '/Frontend/intoyo-views');
        $this->view->setMainView('layouts/main');
        $this->view->gtmDataLayer = $this->gtmDataLayer;
        $this->view->viewDataHelper = new ViewDataHelper();
        $this->view->formViewHelper = new FormViewHelper();
    }

    /**
     * show cookie popup
     */
    private function setCookieBlocks($blocks)
    {
        $this->view->setVar('displayCookie', !$this->cookies->has('ckWg'));
        $this->view->setVar('cookiePopupContent', $blocks['cookie-popup']);

        // rebranding cookie popup
        $displayRebranding = !$this->cookies->has('ckCYF')
            && !$this->cookies->has('ckCYOk')
            && $this->cookies->has('ckWg')
            && is_array($blocks['rebranding-popup'])
            && $blocks['rebranding-popup']['showBlock'] == true;

        $this->view->setVar('rebrandingPopupContent', $blocks['rebranding-popup']);
        $this->view->setVar('displayRebranding', $displayRebranding);
    }

}
