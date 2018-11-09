<?php

namespace Yaf;

final class DispatcherTODOView
{
    protected static $_instance;

    protected $_router;

    protected $_view;

    protected $_request;

    protected $_plugins;

    /**
     * @var bool
     */
    protected $_auto_render = true;

    /**
     * @var bool
     */
    protected $_return_response = false;

    /**
     * @var bool
     */
    protected $_instantly_flush = false;

    protected $_default_module;

    protected $_default_controller;

    protected $_default_action;

    private function __construct()
    {
    }

    public static function getInstance()
    {

    }

    public function enableView()
    {

    }

    public function disableView()
    {
    }

    public function initView($templates_dir, array $options = null)
    {
    }

    public function setView()
    {

    }

    public function setRequest()
    {

    }

    public function getApplication()
    {

    }

    public function getRouter()
    {

    }

    public function getRequest()
    {

    }

    public function setErrorHandler()
    {

    }

    public function setDefaultModule()
    {

    }

    public function setDefaultController()
    {

    }

    public function setDefaultAction()
    {

    }

    public function returnResponse()
    {

    }

    public function autoRender()
    {

    }

    public function flushInstantly()
    {

    }

    public function dispatch()
    {

    }

    public function throwException()
    {

    }

    public function catchException()
    {

    }

    public function registerPlugin()
    {
    }

    private function __clone()
    {
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }
}