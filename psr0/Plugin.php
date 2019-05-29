<?php

use Yaf\Request_Abstract;
use Yaf\Response_Abstract;

/**
 * Yaf_Plugin_Abstract是Yaf的插件基类, 所有应用在Yaf的插件都需要继承实现这个类, 这个类定义了7个方法, 依次在7个时机的时候被调用.
 *
 *  插件有两种部署方式
 *  1.  一种是部署在plugins目录下
 *      通过名称中的后缀(可通过ap.name_suffix和ap.name_separator来改变具体命名形式),来使得自动加载器可以正确加载.
 *  2.
 *      放置在类库, 由普通加载规则加载, 但无论哪种方式, 用户定义的插件都需要继承自Yaf_Plugin_Abstract.
 *
 * @link http://www.laruence.com/manual/yaf.class.plugin.html
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
