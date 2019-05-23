<?php

class IndexController extends \Yaf\Controller_Abstract
{
    public function init()
    {
        var_dump('init');
    }

    public function indexAction()
    {
        global $value;
        var_dump('action');
        var_dump(\Yaf\Registry::get('config')->application->dispatcher->catchException);
        $this->getView()->assignRef('ref', $value);
    }
}
