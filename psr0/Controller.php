<?php

use const INTERNAL\PHP\DEFAULT_SLASH;
use Yaf\Action_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response\Http;
use Yaf\Response_Abstract;
use Yaf\View\Simple;
use Yaf\View_Interface;

/**
 * Yaf_Controller_Abstract是Yaf的MVC体系的核心部分. MVC是指Model-View-Controller, 是一个用于分离应用逻辑和表现逻辑的设计模式.
 * Yaf_Controller_Abstract体系具有可扩展性, 可以通过继承已有的类, 来实现这个抽象类, 从而添加应用自己的应用逻辑.
 * 对于Controller来说, 真正的执行体是在Controller中定义的一个一个的动作, 当然这些动作也可以定义在Controller外:参看Yaf_Controller_Abstract::$action
 * 与一般的框架不同, 在Yaf中, 可以定义动作的参数, 这些参数的值来自对Request的路由结果中的同名参数值. 比如对于如下的控制器:
 *
 * @link http://www.laruence.com/manual/yaf.class.controller.html
 */
abstract class Yaf_Controller_Abstract
{
    /**
     * 有些时候为了拆分比较大的Controller, 使得代码更加清晰和易于管理
     *
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
     * 当前的请求实例, 属性的值由Yaf_Dispatcher保证, 一般通过Yaf_Controller_Abstract::getRequest来获取此属性.
     *
     * @var \Yaf\Request_Abstract
     */
    protected $_request;

    /**
     * 当前的响应对象, 属性的值由Yaf_Dispatcher保证, 一般通过Yaf_Controller_Abstract::getResponse来获取此属性.
     *
     * @var \Yaf\Response_Abstract
     */
    protected $_response;

    protected $_invoke_args;

    /**
     * 视图引擎, Yaf才会延时实例化视图引擎来提高性能
     * 这个属性直到显示的调用了Yaf_Controller_Abstract::getView或者Yaf_Controller_Abstract::initView以后才可用
     *
     * @var \Yaf\View_Interface
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
     * 渲染视图模板, 得到渲染结果
     *
     * @link http://www.laruence.com/manual/yaf.class.controller.render.html
     *
     * @param string     $action 要渲染的动作名
     * @param array|null $var_array 传递给视图引擎的渲染参数, 当然也可以使用Yaf_View_Interface::assign来替代
     * @return string|bool
     */
    protected function render($action, $var_array = [])
    {
        $output = $this->_render($action, $var_array);

        return $output ? $output : false;
    }

    /**
     * 渲染视图模板, 并直接输出渲染结果
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.display.html
     *
     * @param string $action 要渲染的动作名
     * @param array|null $var_array 传递给视图引擎的渲染参数, 当然也可以使用Yaf_View_Interface::assign来替代
     * @return bool
     */
    protected function display($action, $var_array = [])
    {
        return (bool) $this->_display($action, $var_array);
    }

    /**
     * 获取当前的请求实例
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.getRequest.html
     *
     * @return Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * 获取当前的响应实例
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.getResponse.html
     *
     * @return Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * 初始化视图引擎, 因为Yaf采用延迟实例化视图引擎的策略, 所以只有在使用前调用此方法, 视图引擎才会被实例化
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.initView.html
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
     * 获取当前控制器所属的模块名
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.getModuleName.html
     *
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * 获取当前的视图引擎
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.getView.html
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
     * 更改视图模板目录, 之后Yaf_Controller_Abstract::render就会在整个目录下寻找模板文件
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.setViewPath.html
     *
     * @param string $path 视图模板目录, 绝对目录.
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
     * 获取当前的模板目录
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.getViewPath.html
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
     * 将当前请求转给另外一个动作处理
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.forward.html
     *
     * @param string $module 要转给动作的模块, 注意要首字母大写, 如果为空, 则转给当前模块
     * @param string|array $controller 要转给动作的控制器, 注意要首字母大写, 如果为空, 则转给当前控制器
     * @param string|array $action 要转给的动作, 注意要全部小写
     * @param array $args 关联数组
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
     * 重定向请求到新的路径
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.controller.redirect.html
     *
     * @param string $location 要定向的路径
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
