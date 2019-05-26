<?php

use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

/**
 * @link https://www.php.net/manual/en/class.yaf-plugin-abstract.php
 */
abstract class Yaf_Plugin_Abstract
{
    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.routerstartup.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function routerStartup(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.routershutdown.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function routerShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.dispatchloopstartup.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function dispatchLoopStartup(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.dispatchloopshutdown.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function dispatchLoopShutdown(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.predispatch.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function preDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.postdispatch.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function postDispatch(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-plugin-abstract.preresponse.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return bool
     */
    public function preResponse(Request_Abstract $request, Response_Abstract $response)
    {
        return true;
    }
}
