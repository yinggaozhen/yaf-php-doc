<?php

namespace Yaf;

use Yaf\View\Simple;

final class Dispatcher
{
    /**
     * @var Dispatcher
     */
    protected static $_instance = null;

    protected $_router;

    /**
     * @var View_Interface
     */
    protected $_view;

    /**
     * @var Request_Abstract
     */
    protected $_request;

    /**
     * @var array
     */
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

    /**
     * @var string
     */
    protected $_default_module;

    /**
     * @var string
     */
    protected $_default_controller;

    /**
     * @var string
     */
    protected $_default_action;

    private function __construct()
    {
        $this->_plugins            = [];
        $this->_router             = new Router();
        $this->_default_module     = YAF_G('default_module');
        $this->_default_controller = YAF_G('default_controller');
        $this->_default_action     = YAF_G('default_action');

        self::$_instance = new self();
    }

    /**
     * @return Dispatcher
     */
    public static function getInstance(): Dispatcher
    {
        $instance = self::$_instance;

        if (!is_null($instance)) {
            return $instance;
        }

        self::$_instance = new Dispatcher();

        return self::$_instance;
    }

    /**
     * @return Dispatcher
     */
    public function enableView(): Dispatcher
    {
        $this->_auto_render = true;

        return $this;
    }

    /**
     * @return Dispatcher
     */
    public function disableView(): Dispatcher
    {
        $this->_auto_render = false;

        return $this;
    }

    /**
     * @param string $templates_dir
     * @param array|null $options
     * @return null|Simple|View_Interface
     * @throws \Exception
     */
    public function initView(string $templates_dir, array $options = null): ?View_Interface
    {
        $view = $this->_initView($templates_dir, $options);

        return $view;
    }

    /**
     * @param View_Interface $view
     * @return $this|bool
     */
    public function setView(View_Interface $view)
    {
        if (is_object($view) && $view instanceof View_Interface) {
            $this->_view = $view;

            return $this;
        }

        return false;
    }

    /**
     * @param Request_Abstract $request
     * @return $this|bool
     */
    public function setRequest(Request_Abstract $request)
    {
        if (!is_object($request) || !($request instanceof Request_Abstract)) {
            trigger_error(sprintf("Expects a %s instance", get_class($request)), E_WARNING);
            return false;
        }

        $this->_request = $request;
        return $this;
    }

    public function getApplication()
    {
        // TODO PHP_MN(yaf_application_app)(INTERNAL_FUNCTION_PARAM_PASSTHRU);什么意思
    }

    public function getRouter()
    {
        // TODO
    }

    public function getRequest()
    {
        // TODO
    }

    public function setDefaultModule()
    {
        // TODO
    }

    public function setDefaultController()
    {
        // TODO
    }

    public function setDefaultAction()
    {
        // TODO
    }

    /**
     * @param bool $auto_response
     * @return $this|int
     */
    public function returnResponse($auto_response = true)
    {
        $argc = func_get_args();

        if ($argc) {
            $this->_return_response = $auto_response;
            return $this;
        } else {
            return $this->_return_response === true ? 1 : 0;
        }
    }

    /**
     * @param bool $flag
     * @return $this|int
     */
    public function flushInstantly(bool $flag)
    {
        $argc = func_get_args();

        if ($argc) {
            $this->_instantly_flush = $flag;
            return $this;
        } else {
            return $this->_instantly_flush === true ? 1 : 0;
        }
    }

    public function dispatch()
    {

    }

    /**
     * @param callable $callback
     * @param int $error_type
     * @return Dispatcher
     */
    public function setErrorHandler(callable $callback, int $error_type = E_ALL | E_STRICT): Dispatcher
    {
        try {
            set_error_handler($callback, $error_type);
        } catch (\Throwable $e) {
            trigger_error("Call to set_error_handler failed", E_WARNING);
        }

        return $this;
    }

    /**
     * @param bool $flag
     * @return $this|int
     */
    public function autoRender(bool $flag = false)
    {
        $argc = func_get_args();

        if ($argc) {
            $this->_auto_render = $flag ? true : false;
            return $this;
        } else {
            return $this->_auto_render === true ? 1 : 0;
        }
    }

    /**
     * @param bool $flag
     * @return $this|bool
     */
    public function throwException(bool $flag = false)
    {
        $argc = func_get_args();

        if ($argc) {
            // TODO YAF_G 赋值问题
            YAF_G('throw_exception', $flag ? 1 : 0);
            return $this;
        } else {
            return (bool) YAF_G('throw_exception');
        }
    }

    /**
     * @param bool $flag
     * @return $this|bool
     */
    public function catchException(bool $flag = false)
    {
        $argc = func_get_args();

        if ($argc) {
            YAF_G('catch_exception', $flag ? 1 : 0);
            return $this;
        } else {
            return (bool) YAF_G('catch_exception');
        }
    }

    /**
     * @param Plugin_Abstract $plugin
     * @return $this|bool
     */
    public function registerPlugin(Plugin_Abstract $plugin)
    {
        if (!is_object($plugin) || !($plugin instanceof Plugin_Abstract)) {
            trigger_error(sprintf("Expect a %s instance", get_class($plugin)), E_WARNING);
            return false;
        }

        $this->_plugins[] = $plugin;

        return $this;
    }

    /**
     * @param string $tpl_dir
     * @param array $options
     * @return Simple|View_Interface
     * @throws \Exception
     */
    private function _initView(string $tpl_dir, array $options): ?View_Interface
    {
        $view = $this->_view;

        if (is_object($view) && $view instanceof View_Interface) {
            return $view;
        }

        $view = new Simple($tpl_dir, $options);
        if (empty($view)) {
            return null;
        }
        $this->_view = $view;

        return $view;
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