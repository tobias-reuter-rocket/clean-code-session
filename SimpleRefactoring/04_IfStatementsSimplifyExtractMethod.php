<?php

namespace CleanCode\SimpleRefactoring;

class IfStatementsSimplifyExtractMethod
{
    public function getWelcomeMessage($cookieBlock)
    {
        if (!$this->cookies->has('ckCYF')
            && !$this->cookies->has('ckCYOk')
            && $this->cookies->has('ckWg')
            && is_array($cookieBlock)
            && $cookieBlock['showBlock'] == true) {
            $this->view->setVar('rebrandingPopupContent', $cookieBlock);
            $this->view->setVar('displayRebranding', true);
        }


    }
}





/** Refactoring */

class IfStatementsSimplifyExtractMethod2
{
    /**
     * Makes it easier to understand the method
     * @param $cookieBlock
     */
    public function getWelcomeMessage($cookieBlock)
    {
        if ($this->isWelcomeMessageAllowed($cookieBlock)
        ) {
            $this->view->setVar('rebrandingPopupContent', $cookieBlock);
            $this->view->setVar('displayRebranding', true);
        }
    }

    /**
     * @param $cookieBlock
     * @return bool
     */
    private function isWelcomeMessageAllowed($cookieBlock)
    {
        return !$this->cookies->has('ckCYF')
        && !$this->cookies->has('ckCYOk')
        && $this->cookies->has('ckWg')
        && is_array($cookieBlock)
        && $cookieBlock['showBlock'] == true;
    }
}
