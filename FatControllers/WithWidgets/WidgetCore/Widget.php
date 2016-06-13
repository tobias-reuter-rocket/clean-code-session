<?php

namespace CleanCode\Controllers\WithWidgets\WidgetCore;

use Phalcon\DI\Injectable;
use Phalcon\Mvc\View;

/**
 * Basic class for all widgets
 * Class Widget
 * @package Common\Widget
 */
abstract class Widget extends Injectable
{
    /**
     * An associative array of options
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * Gets an user defined option, returns $defaultValue if option does not exist.
     * @param $key
     * @param $defaultValue
     * @return mixed
     */
    public function getOption($key, $defaultValue = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $defaultValue;
    }

    /**
     * widget body itself
     * @return mixed
     */
    abstract public function init();

    /**
     * @return string
     */
    abstract public function run();

    /**
     * @return View
     */
    private function getView()
    {
        return $this->view;
    }

    /**
     * Render Widget view
     * @param string $viewFile
     * @param array  $params
     * @return string
     */
    protected function render($viewFile, array $params = [])
    {
        $viewFile = 'widget/' . $viewFile;
        return $this->getView()->getPartial($viewFile, $params);
    }

    /**
     * Returns the result of the run method.
     * @return string
     */
    public function __toString()
    {
        return $this->run();
    }

}
