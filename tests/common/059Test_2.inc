<?php

class IndexController extends \Yaf\Controller_Abstract
{
    public function init()
    {
        \Yaf\Dispatcher::getInstance()->flushInstantly(true);
    }

    public function indexAction()
    {
        var_dump($this->_view->getScriptPath());
    }
}
