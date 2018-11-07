<?php

namespace Yaf;

abstract class Plugin_Abstract
{
    public function routerStartup(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function routerShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function preDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function postDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    public function preResponse(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }
}