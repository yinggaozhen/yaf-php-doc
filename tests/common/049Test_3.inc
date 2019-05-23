<?php

class TestPlugin extends \Yaf\Plugin_Abstract
{
    public function routerStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        var_dump('routerStartup');
    }

    public function routerShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        var_dump('routerShutdown');
    }

    public function dispatchLoopStartup(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        var_dump('dispatchLoopStartup');
    }

    public function preDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        var_dump('preDispatch');
    }

    public function postDispatch(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        var_dump('postDispatch');
    }

    public function dispatchLoopShutdown(\Yaf\Request_Abstract $request, \Yaf\Response_Abstract $response)
    {
        global $value;
        var_dump('dispatchLoopShutdown, global var is:' . $value);
    }
}
