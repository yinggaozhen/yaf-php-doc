<?php

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    public function _initView(\Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setView(new SimpleView(YAF_TEST_APPLICATION_PATH . '/views'));
    }
}
