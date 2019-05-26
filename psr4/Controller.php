<?php

namespace Yaf;

use const INTERNAL\PHP\DEFAULT_SLASH;
use Yaf\Response\Http;
use Yaf\View\Simple;

/**
 * @link https://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
abstract class Controller_Abstract
{
    /**
     * @var array
     */
    public $actions;

    /**
     * @var mixed
     */
    protected $_module;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var Request_Abstract
     */
    protected $_request;

    /**
     * @var Response_Abstract
     */
    protected $_response;

    protected $_invoke_args;

    /**
     * @var View_Interface
     */
    protected $_view;

    /**
     * Controller_Abstract constructor.
     *
     * @link https://www.php.net/manual/en/yaf-controller-abstract.construct.php
     *
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @param View_Interface $view
     * @param array|null $invokeArgs
     */
    final public function __construct(Request_Abstract $request, Response_Abstract $response, View_Interface $view, array $invokeArgs = null)
    {
        if ($invokeArgs) {
            $this->_invoke_args = $invokeArgs;
        }

        $module = $request->getModuleName();

        $this->_request = $request;
        $this->_response = $response;
        $this->_module = $module;
        $this->_view = $view;

        if (!($this instanceof Action_Abstract) && is_callable([$this, 'init'])) {
            call_user_func([$this, 'init']);
        }
    }

    /**
     * @node :
     *  对外其实没有暴露这个函数
     *  由于构造函数是final，所以部分工呢鞥无法通过重写构造函数来实现，但是可以实现`init`方法来实现
     *
     * @link https://www.php.net/manual/en/yaf-controller-abstract.init.php
     */
    public function init()
    {

    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.clone.php
     */
    final private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.render.php
     *
     * @param string     $action
     * @param array|null $var_array
     * @return string|bool
     */
    protected function render($action, $var_array = [])
    {
        $output = $this->_render($action, $var_array);

        return $output ? $output : false;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.display.php
     *
     * @param string $action
     * @param array|null $var_array
     * @return bool
     */
    protected function display($action, $var_array = [])
    {
        return (bool) $this->_display($action, $var_array);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getrequest.php
     *
     * @return Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getresponse.php
     *
     * @return Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.initview.php
     *
     * @return $this
     */
    public function initView()
    {
        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getinvokearg.php
     *
     * @param null|string $name
     * @return null|string
     */
    public function getInvokeArg($name)
    {
        if ($name) {
            $args = $this->_invoke_args;

            if (is_null($args)) {
                return null;
            }

            if (array_key_exists($name, $args)) {
                return $args[$name];
            }
        }

        return null;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getmodulename.php
     *
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getview.php
     *
     * @return View_Interface
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getinvokeargs.php
     *
     * @return array
     */
    public function getInvokeArgs()
    {
        return $this->_invoke_args;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.setviewpath.php
     *
     * @param string $path
     * @return bool
     */
    public function setViewpath($path)
    {
        if (!is_string($path)) {
            return false;
        }

        $view = $this->_view;
        if ($view instanceof Simple) {
            $view->setScriptPath($path);
        } else {
            call_user_func([$view, 'setscriptpath'], $path);
        }

        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.getviewpath.php
     *
     * @return mixed|null|string
     */
    public function getViewpath()
    {
        $view = $this->_view;

        if ($view instanceof View_Interface) {
            $tpl_dir = $view->getScriptPath();

            if (!is_string($tpl_dir) && YAF_G('view_directory')) {
                return YAF_G('view_directory');
            }

            return $tpl_dir;
        } else {
            $ret = call_user_func([$view, 'getscriptpath']);

            return $ret ?? null;
        }
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.forward.php
     *
     * @param string $module
     * @param string|array $controller
     * @param string|array $action
     * @param array $args
     * @return bool
     */
    public function forward($module, $controller = '', $action = '', array $args = null)
    {
        $request = $this->_request;

        if (empty($this->_request) || !($this->_request instanceof Request_Abstract)) {
            return false;
        }

        switch (func_num_args()) {
            case 1:
                if (!is_string($module)) {
                    trigger_error('Expect a string action name', E_USER_WARNING);
                    return false;
                }

                $request->setActionName($module);
                break;

            case 2:
                if (is_string($controller)) {
                    $request->controller = $module;
                    $request->action = $controller;
                } else if (is_array($controller)) {
                    $request->setActionName($module);
                    $request->setParam($controller);
                } else {
                    return false;
                }
                break;

            case 3:
                if (is_string($action)) {
                    $request->setModuleName($module);
                    $request->setControllerName($controller);
                    $request->setActionName($action);
                } else if (is_array($action)) {
                    $request->setControllerName($module);
                    $request->setActionName($controller);
                    $request->setParam($action);
                } else {
                    return false;
                }
                break;

            case 4:
                if (!is_array($args)) {
                    trigger_error('Parameters must be an array', E_USER_WARNING);
                    return false;
                }

                $request->setModuleName($module);
                $request->setControllerName($controller);
                $request->setActionName($action);
                $request->setParam($args);
                break;
        }

        $request->_setDispatched(0);

        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-controller-abstract.redirect.php
     *
     * @param string $location
     * @return bool
     */
    public function redirect($location)
    {
        /** @var Http $response */
        $response = $this->_response;

        $response->setRedirect($location);

        return true;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param string $action_name
     * @param array $var_array
     * @return string
     */
    private function _render($action_name, $var_array)
    {
        $view = $this->_view;
        $name = $this->_name;

        $view_ext = YAF_G('view_ext');
        $self_name = str_replace('_', DEFAULT_SLASH, strtolower($name));
        $action_name = strtolower(str_replace('_', DEFAULT_SLASH, $action_name));

        $path = sprintf("%s%s%s.%s", $self_name, DEFAULT_SLASH, $action_name, $view_ext);

        if ($var_array) {
            $ret = call_user_func([$view, 'render'], $path, $var_array);
        } else {
            $ret = call_user_func([$view, 'render'], $path);
        }

        if (is_null($ret) || !is_string($ret)) {
            return null;
        }

        return $ret;
    }

    /**
     * @param string $action_name
     * @param array $var_array
     * @return int
     */
    private function _display($action_name, $var_array)
    {
        $view = $this->_view;
        $name = $this->_name;

        $view_ext  = YAF_G('view_ext');
        $self_name = str_replace('_', DEFAULT_SLASH, strtolower($name));
        $action_name = str_replace('_', DEFAULT_SLASH, $action_name);

        $path = sprintf("%s%s%s.%s", $self_name, DEFAULT_SLASH, $action_name, $view_ext);

        if ($var_array) {
            $ret = call_user_func([$view, 'display'], $path, $var_array);
        } else {
            $ret = call_user_func([$view, 'display'], $path);
        }

        if (is_null($ret) || $ret === false) {
            return 0;
        }

        return 1;
    }
}
