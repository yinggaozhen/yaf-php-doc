<?php

use Yaf\Controller_Abstract;

/**
 * @link https://www.php.net/manual/en/class.yaf-action-abstract.php
 */
abstract class Yaf_Action_Abstract extends Controller_Abstract
{
    /**
     * @var \Yaf\Controller_Abstract
     */
    protected $_controller = null;

    /**
     * @link https://www.php.net/manual/en/yaf-action-abstract.execute.php
     *
     * @return mixed
     */
    abstract function execute();

    /**
     * @link https://www.php.net/manual/en/yaf-action-abstract.getcontroller.php
     *
     * @return \Yaf\Controller_Abstract
     */
    public function getController()
    {
        return $this->_controller;
    }
}
