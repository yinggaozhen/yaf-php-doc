<?php

require_once __DIR__ . '/plugins/Test.php';

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    public function _initConfig(\Yaf\Dispatcher $dispatcher)
    {
        \Yaf\Registry::set('config', \Yaf\Application::app()->getConfig());
    }

    /**
     * @param \Yaf\Dispatcher $dispatcher
     * @throws Exception
     */
    public function _initPlugin(\Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->registerPlugin(new TestPlugin());
    }

    public function _initReturn(\Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->returnResponse(true);
    }
}
