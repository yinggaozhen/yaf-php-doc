<?php

namespace Yaf;

abstract class Action_Abstract
{
    protected $_controller = null;

    abstract function execute();

    public function getController()
    {
        return $this->_controller;
    }
}