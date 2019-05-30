<?php

use const INTERNAL\PHP\DEFAULT_SLASH;
use const YAF\ERR\AUTOLOAD_FAILED;
use const YAF\ERR\DISPATCH_FAILED;
use const YAF\ERR\NOTFOUND\ACTION;
use const YAF\ERR\NOTFOUND\CONTROLLER;
use const YAF\ERR\NOTFOUND\MODULE;
use const YAF\ERR\ROUTE_FAILED;
use const YAF\ERR\STARTUP_FAILED;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Action_Abstract;
use Yaf\Application;
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;
use Yaf\Loader;
use Yaf\Plugin_Abstract;
use Yaf\Request_Abstract;
use Yaf\Response_Abstract;
use Yaf\Router;
use Yaf\View\Simple;
use Yaf\View_Interface;
use function YP\internalCall;
use function Yaf\Exception\Internal\yaf_buildin_exceptions;

/**
 * Yaf_Dispatcher实现了MVC中的C分发, 它由Yaf_Application负责初始化, 然后由Yaf_Application::run启动
 * 它协调路由来的请求, 并分发和执行发现的动作, 并收集动作产生的响应, 输出响应给请求者, 并在整个过程完成以后返回响应.
 *
 * @link http://www.laruence.com/manual/yaf.class.dispatcher.html
 */
class Yaf_Dispatcher
{
    /**
     * Yaf_Dispatcher实现了单利模式, 此属性保存当前实例
     *
     * @var Dispatcher
     */
    protected static $_instance = null;

    /**
     * 路由器, 在Yaf0.1之前, 路由器是可更改的, 但是Yaf0.2以后, 随着路由器和路由协议的分离, 各种路由都可以通过配置路由协议来实现, 也就取消了自定义路由器的功能
     *
     * @var Router
     */
    protected $_router;

    /**
     * 当前的视图引擎, 可以通过Yaf_Dispatcher::setView来替换视图引擎为自定义视图引擎(比如Smary/Firekylin等常见引擎)
     *
     * @var View_Interface
     */
    protected $_view;

    /**
     * 当前的请求
     *
     * @var Request_Abstract
     */
    protected $_request;

    /**
     * 已经注册的插件, 插件一经注册, 就不能更改和删除
     *
     * @var array
     */
    protected $_plugins;

    /**
     * 标示着,是否在动作执行完成后, 调用视图引擎的render方法, 产生响应.
     * 可以通过Yaf_Dispatcher::disableView和Yaf_Dispatcher::enableView来切换开关状态
     *
     * @var bool
     */
    protected $_auto_render = true;

    /**
     * 标示着,是否在产生响应以后, 不自动输出给客户端, 而是返回给调用者. 可以通过Yaf_Dispatcher::returnResponse来切换开关状态
     *
     * @var bool
     */
    protected $_return_response = false;

    /**
     * 标示着, 是否在有输出的时候, 直接响应给客户端, 不写入Yaf_Response_Abstract对象.
     *
     * @tip 如果此属性为TRUE, 那么将忽略Yaf_Dispatcher::$_return_response
     * @var bool
     */
    protected $_instantly_flush = false;

    /**
     * 默认的模块名, 在路由的时候, 如果没有指明模块, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultModule来指定
     *
     * @var string
     */
    protected $_default_module;

    /**
     * 默认的控制器名, 在路由的时候, 如果没有指明控制器, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultController来指定
     *
     * @var string
     */
    protected $_default_controller;

    /**
     * 默认的动作名, 在路由的时候, 如果没有指明动作, 则会使用这个值, 也可以通过配置文件中的ap.dispatcher.defaultAction来指定
     *
     * @var string
     */
    protected $_default_action;

    /**
     * Dispatcher constructor.
     *
     * @link https://www.php.net/manual/en/yaf-dispatcher.construct.php
     *
     * @throws \Exception
     */
    private function __construct()
    {
        $this->_plugins = [];
        $this->_router = new Router();
        $this->_default_module = YAF_G('default_module');
        $this->_default_controller = YAF_G('default_controller');
        $this->_default_action = YAF_G('default_action');

        self::$_instance = $this;
    }

    /**
     * 获取当前的Yaf_Dispatcher实例
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.getInstance.html
     *
     * @return Dispatcher
     * @throws \Exception
     */
    public static function getInstance()
    {
        $instance = self::$_instance;

        if (!is_null($instance)) {
            return $instance;
        }

        self::$_instance = new Yaf_Dispatcher();

        return self::$_instance;
    }

    /**
     * 开始处理流程, 一般的不需要用户调用此方法, Yaf_Application::run 会自动调用此方法.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.dispatch.html
     *
     * @param Request_Abstract $request
     * @return Response_Abstract|bool
     * @throws \Exception
     */
    public function dispatch(Request_Abstract $request = null)
    {
        $this->_request = $request;
        /** @var Response_Abstract $rResponse */
        $rResponse = null;

        if ($response = $this->_dispatch($rResponse)) {
            return $response;
        }

        return false;
    }

    /**
     * 开启自动Render. 默认是开启的, 在动作执行完成以后, Yaf会自动render以动作名命名的视图模板文件.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.enableView.html
     *
     * @return $this 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function enableView()
    {
        $this->_auto_render = true;

        return $this;
    }

    /**
     * 关闭自动Render. 默认是开启的, 在动作执行完成以后, Yaf会自动render以动作名命名的视图模板文件.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.disableView.html
     *
     * @return Yaf_Dispatcher 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function disableView()
    {
        $this->_auto_render = false;

        return $this;
    }

    /**
     * 初始化视图引擎, 因为Yaf采用延迟实例化视图引擎的策略, 所以只有在使用前调用此方法, 视图引擎才会被实例化
     *
     * @since 1.0.0.9
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.initView.html
     *
     * @param string $templates_dir 视图的模板目录的绝对路径
     * @param array|null $options
     * @return null|Simple|View_Interface
     * @throws \Exception
     */
    public function initView($templates_dir, array $options = null)
    {
        $view = $this->_initView($templates_dir, $options);

        return $view;
    }

    /**
     * 设置视图引擎
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setView.html
     *
     * @param View_Interface $view 一个实现了Yaf_View_Interface的视图引擎实例
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
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
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setRequest.html
     *
     * @param Request_Abstract $request 一个Yaf_Request_Abstract实例
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     * @throws \Exception
     */
    public function setRequest(Request_Abstract $request)
    {
        if (!is_object($request) || !($request instanceof Request_Abstract)) {
            yaf_trigger_error(E_WARNING, "Expects a %s iniInstance", get_class($request));
            return false;
        }

        $this->_request = $request;
        return $this;
    }

    /**
     * 获取当前的Yaf_Application实例
     *
     * @since 1.0.0.8
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.getApplication.html
     *
     * @return Application
     */
    public function getApplication()
    {
        return Application::app();
    }

    /**
     * 获取路由器
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.getRouter.html
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * 获取当前的请求实例
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.getRequest.html
     *
     * @return Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * 设置路由的默认模块, 如果在路由结果中不包含模块信息, 则会使用此默认模块作为路由模块结果
     *
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setDefaultModule.html
     *
     * @param string $module 默认模块名, 请注意需要首字母大写
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function setDefaultModule($module)
    {
        if (is_string($module) && !empty($module) && Application::isModuleName($module)) {
            $this->_default_module = ucfirst(strtolower($module));

            return $this;
        }

        return false;
    }

    /**
     * 设置路由的默认控制器, 如果在路由结果中不包含控制器信息, 则会使用此默认控制器作为路由控制器结果
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setDefaultController.html
     *
     * @param string $controller 默认控制器名, 请注意需要首字母大写
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function setDefaultController($controller)
    {
        if (is_string($controller) && !empty($controller)) {
            $this->_default_controller = strtoupper($controller);

            return $this;
        }

        return false;
    }

    /**
     * 设置路由的默认动作, 如果在路由结果中不包含动作信息, 则会使用此默认动作作为路由动作结果
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setDefaultAction.html
     *
     * @param string $action 默认动作名, 请注意需要全部小写
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function setDefaultAction($action)
    {
        if (is_string($action) && !empty($action)) {
            $this->_default_action = $action;

            return $this;
        }

        return false;
    }

    /**
     * 是否返回Response对象, 如果启用, 则Response对象在分发完成以后不会自动输出给请求端, 而是交给程序员自己控制输出.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.returnResponse.html
     *
     * @param bool $auto_response 开启状态
     * @return $this|int 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function returnResponse($auto_response = true)
    {
        $argc = func_num_args();

        if ($argc) {
            $this->_return_response = $auto_response;
            return $this;
        } else {
            return $this->_return_response === true ? 1 : 0;
        }
    }

    /**
     * 切换自动响应.
     * 在Yaf_Dispatcher::enableView()的情况下, 会使得Yaf_Dispatcher调用Yaf_Controller_Abstract::display方法, 直接输出响应给请求端
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.flushInstantly.html
     *
     * @param bool $flag 开启状态
     * @return $this|int 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function flushInstantly($flag = false)
    {
        $argc = func_num_args();

        if ($argc) {
            $this->_instantly_flush = $flag;
            return $this;
        } else {
            return boolval($this->_instantly_flush === true ? 1 : 0);
        }
    }

    /**
     * 设置错误处理函数, 一般在appcation.throwException关闭的情况下, Yaf会在出错的时候触发错误
     * 这个时候, 如果设置了错误处理函数, 则会把控制交给错误处理函数处理.
     *
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setErrorHandler.html
     *
     * @param callable $callback <p>
     * 错误处理函数, 这个函数需要最少接受俩个参数: 错误代码($error_code)和错误信息($error_message)
     * 可选的还可以接受三个参数: 错误文件($err_file), 错误行($err_line)和错误上下文($errcontext)
     * <p>
     * @param int $error_type 要捕获的错误类型
     * @return Dispatcher | boolean 成功返回Yaf_Dispatcher, 失败返回FALSE
     * @throws \Exception
     */
    public function setErrorHandler($callback, $error_type = E_ALL)
    {
        try {
            set_error_handler($callback, $error_type);
        } catch (\Throwable $e) {
            trigger_error('Call to set_error_handler failed', E_USER_WARNING);
            return false;
        }

        return $this;
    }

    /**
     * 开启/关闭自动渲染功能. 在开启的情况下(Yaf默认开启), Action执行完成以后, Yaf会自动调用View引擎去渲染该Action对应的视图模板.
     *
     * @since 1.0.0.11
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.autoRender.html
     *
     * @param bool $flag 开启状态
     * @return $this|int 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function autoRender($flag = false)
    {
        $argc = func_num_args();

        if ($argc) {
            $this->_auto_render = $flag ? true : false;
            return $this;
        } else {
            return boolval($this->_auto_render === true ? 1 : 0);
        }
    }

    /**
     * 切换在Yaf出错的时候抛出异常, 还是触发错误.
     * 当然,也可以在配置文件中使用ap.dispatcher.thorwException=$switch达到同样的效果, 默认的是开启状态.
     *
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.throwException.html
     *
     * @param bool $flag <p>
     * 如果为TRUE,则Yaf在出错的时候采用抛出异常的方式. 如果为FALSE, 则Yaf在出错的时候采用触发错误的方式.
     * </p>
     *
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function throwException($flag = false)
    {
        $argc = func_num_args();

        if ($argc) {
            YAF_G('throw_exception', $flag ? 1 : 0);
            return $this;
        } else {
            return (bool)YAF_G('throw_exception');
        }
    }

    /**
     * 在ap.dispatcher.throwException开启的状态下, 是否启用默认捕获异常机制
     * 当然,也可以在配置文件中使用ap.dispatcher.catchException=$switch达到同样的效果, 默认的是开启状态.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.catchException.html
     *
     * @param bool $flag 如果为TRUE, 则在有未捕获异常的时候, Yaf会交给Error Controller的Error Action处理.
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     */
    public function catchException($flag = false)
    {
        $argc = func_num_args();

        if ($argc) {
            YAF_G('catch_exception', $flag ? 1 : 0);
            return $this;
        } else {
            return (bool)YAF_G('catch_exception');
        }
    }

    /**
     * 一个Yaf_Plugin_Abstract派生类的实例.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.registerPlugin.html
     *
     * @param Plugin_Abstract $plugin
     * @return $this|bool 成功返回Yaf_Dispatcher, 失败返回FALSE
     * @throws \Exception
     */
    public function registerPlugin(Plugin_Abstract $plugin)
    {
        if (!is_object($plugin) || !($plugin instanceof Plugin_Abstract)) {
            yaf_trigger_error(E_WARNING, "Expect a %s iniInstance", get_class($plugin));
            return false;
        }

        $this->_plugins[] = $plugin;

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.sleep.php
     */
    private function __sleep()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.wakeup.php
     */
    private function __wakeup()
    {
    }

    // ================================================== 内部常量 ==================================================

    /**
     * @internal
     */
    private const YAF_PLUGIN_HOOK_ROUTESTARTUP  = "routerstartup";
    private const YAF_PLUGIN_HOOK_ROUTESHUTDOWN = "routershutdown";
    private const YAF_PLUGIN_HOOK_LOOPSTARTUP   = "dispatchloopstartup";
    private const YAF_PLUGIN_HOOK_PREDISPATCH   = "predispatch";
    private const YAF_PLUGIN_HOOK_POSTDISPATCH  = "postdispatch";
    private const YAF_PLUGIN_HOOK_LOOPSHUTDOWN  = "dispatchloopshutdown";
    private const YAF_PLUGIN_HOOK_PRERESPONSE   = "preresponse";
    private const YAF_ERROR_CONTROLLER          = "Error";
    private const YAF_ERROR_ACTION              = "error";

    // ================================================== 内部方法 ==================================================

    /**
     * @param null|Response_Abstract $response
     * @return null|Response_Abstract
     * @throws \Exception
     */
    public function _dispatch(?Response_Abstract &$response)
    {
        $nesting = YAF_G('yaf.forward_limit');

        $response = Response_Abstract::instance(php_sapi_name());
        $request = $this->_request;
        $plugins = $this->_plugins;

        if (!is_object($request)) {
            yaf_trigger_error(TYPE_ERROR, 'Expect a %s iniInstance', get_class($request));
            return null;
        }

        if (!$request->isRouted()) {
            try {
                $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_ROUTESTARTUP, $request, $response);
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }

            if (!$this->_route($request)) {
                try {
                    yaf_trigger_error(ROUTE_FAILED, 'Routing request failed');
                } catch (\Exception $e) {
                    $this->_exceptionHandle($e, $request, $response);
                    return null;
                }
            }

            $this->_fixDefault($request);
            try {
                $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_ROUTESHUTDOWN, $request, $response);
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }

            $request->setRouted();
        } else {
            $this->_fixDefault($request);
        }

        try {
            $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_LOOPSTARTUP, $request, $response);
        } catch (\Exception $e) {
            $this->_exceptionHandle($e, $request, $response);
            return null;
        }

        $view = $this->_initView(null, null);
        if (!$view) {
            return null;
        }

        do {
            try {
                $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_PREDISPATCH, $request, $response);
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }

            try {
                $this->_handle($request, $response, $view);
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }

            $this->_fixDefault($request);
            try {
                $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_POSTDISPATCH, $request, $response);
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }


        } while (--$nesting > 0 && !$request->isDispatched());

        try {
            $this->_pluginHandle($plugins, self::YAF_PLUGIN_HOOK_LOOPSHUTDOWN, $request, $response);
        } catch (\Exception $e) {
            $this->_exceptionHandle($e, $request, $response);
            return null;
        }

        if (0 == $nesting && !$request->isDispatched()) {
            try {
                yaf_trigger_error(DISPATCH_FAILED, "The max dispatch nesting %ld was reached", YAF_G('yaf.forward_limit'));
            } catch (\Exception $e) {
                $this->_exceptionHandle($e, $request, $response);
                return null;
            }
       }

        $return_response = $this->_return_response;

        if ($return_response === false) {
            call_user_func([$response, 'response']);
            $response->clearBody();
        }

        return $response;
    }

    /**
     * @internal
     * @param string $tpl_dir
     * @param array $options
     * @return Simple|View_Interface
     * @throws \Exception
     */
    private function _initView($tpl_dir, $options)
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

    /**
     * @param \Exception $e
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function _dispatcherExceptionHandler(\Exception $e, Request_Abstract $request, Response_Abstract $response)
    {
        if (YAF_G('in_exception')) {
            return;
        }

        YAF_G('in_exception', 1);

        $module = $request->module;

        if (!is_string($module) || empty($module)) {
            $module = $this->_default_module;
            $request->setModuleName($module);
        }

        $controller = Yaf_Dispatcher::YAF_ERROR_CONTROLLER;
        $action = Yaf_Dispatcher::YAF_ERROR_ACTION;
        $exception = $e;

        $request->setControllerName($controller);
        $request->setActionName($action);
        $reflectProperty = new \ReflectionProperty($request, '_exception');
        $reflectProperty->setAccessible(true);
        $reflectProperty->setValue($request, $exception);

        /** @see Request_Abstract::_setParamsSingle() */
        if (internalCall($request, '_setParamsSingle', 'exception', $exception)) {
            // DO NOTHING IN PHP
        } else {
            return;
        }

        $request->setDispatched();
        $view = $this->_initView(null, null);
        if (!$view) {
            return;
        }

        try {
            $this->_handle($request, $response, $view);
        } catch (\Exception $e) {
            if (is_subclass_of($e, yaf_buildin_exceptions(CONTROLLER))) {
                $request->setModuleName($this->_default_module);
                $this->_handle($request, $response, $view);
            }
        }

        $response->response();
    }

    /**
     * @param Request_Abstract $request
     * @return int
     * @throws \Exception
     */
    private function _route(Request_Abstract $request)
    {
        $router = $this->getRouter();

        if (is_object($router)) {
            if ($router instanceof Router) {
                if ($router->route($request)) {
                    return 1;
                }
            } else {
                $result = call_user_func([$router, 'route'], $request);

                if (!$result) {
                    throw new \Exception("Routing request faild", ROUTE_FAILED);
                }
            }

            return 1;
        }

        return 0;
    }

    /**
     * @param Request_Abstract $request
     */
    private function _fixDefault(Request_Abstract $request)
    {
        $module     = $request->getModuleName();
        $action     = $request->getActionName();
        $controller = $request->getControllerName();

        // module
        if (!is_string($module) || empty($module)) {
            $request->setModuleName($this->_default_module);
        } else {
            $request->setModuleName(ucfirst(strtolower($module)));
        }

        // controller
        if (!is_string($controller) || empty($controller)) {
            $request->setControllerName($this->_default_controller);
        } else {
            /**
             * Upper controller name
             * eg: Index_sub -> Index_Sub
             */
            $default_controller = ucfirst(strtolower($controller));
            $default_controller = preg_replace_callback('/_(\w+)/', function($match) {
                return '_' . ucfirst($match[1]);
            }, $default_controller);

            $request->setControllerName($default_controller);
        }

        // action
        if (!is_string($action) || empty($action)) {
            $request->setActionName($this->_default_action);
        } else {
            $request->setActionName(ucfirst(strtolower($action)));
        }
    }

    /**
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @param View_Interface $view
     * @return int
     * @throws \Exception
     */
    private function _handle(Request_Abstract $request, Response_Abstract $response, View_Interface $view)
    {
        $app_dir = YAF_G('directory');

        $request->setDispatched();
        if (!$app_dir) {
            $error_message = sprintf("%s requires %s(which set the application.directory) to be initialized first", get_class($this), Application::class);
            yaf_trigger_error(STARTUP_FAILED, $error_message);
        } else {
            $is_def_module = 0;

            $module     = $request->getModuleName();
            $controller = $request->getControllerName();
            $dmodule    = $this->_default_module;

            if (!is_string($module) || empty($module)) {
                yaf_trigger_error(DISPATCH_FAILED, 'Unexcepted a empty module name');
            } else if (!Application::isModuleName($module)) {
                yaf_trigger_error(MODULE, 'There is no module %s', $module);
            }

            if (!is_string($controller) || empty($controller)) {
                yaf_trigger_error(DISPATCH_FAILED, 'Unexcepted a empty controller name');
            }

            if ($dmodule == $module) {
                $is_def_module = 1;
            }

            /** @var Controller_Abstract $ce */
            $ce = $this->_getController($app_dir, $module, $controller, $is_def_module);

            if (empty($ce)) {
                return 0;
            } else {
                // TODO controller 入参问题
                $iController = new $ce($request, $response, $view);

                if (!$request->isDispatched()) {
                    return $this->_handle($request, $response, $view);
                }

                /* view template directory for application, please notice that view engine's directory has high priority */
                if ($is_def_module) {
                    $view_dir = sprintf("%s%s%s", $app_dir, DEFAULT_SLASH, "views");
                } else {
                    $view_dir = sprintf("%s%s%s%s%s%s%s", $app_dir, DEFAULT_SLASH, "modules", DEFAULT_SLASH, $module, DEFAULT_SLASH, "views");
                }

                if (YAF_G('view_directory')) {
                    YAF_G('view_directory', 'NULL');
                }

                YAF_G('view_directory', $view_dir);
                $property = new \ReflectionProperty($iController, '_name');
                $property->setAccessible(true);
                $property->setValue($iController, $controller);

                $action = $request->getActionName();
                $func_name = sprintf('%s%s', strtolower($action), 'action');

                try {
                    $reflectionMethod = new \ReflectionMethod($iController, $func_name);
                } catch (\ReflectionException $e) {
                    $reflectionMethod = null;
                }

                if ($reflectionMethod) {
                    $executor = $iController;

                    if ($reflectionMethod->getNumberOfParameters()) {
                        $call_args = $this->_getCallParameters($request, $reflectionMethod->getParameters()) ?: [];

                        $result = $reflectionMethod->invokeArgs($iController, $call_args);
                    } else {
                        $result = $reflectionMethod->invoke($iController);
                    }

                    if ($result === false) {
                        /* no auto-renderring */
                        return 1;
                    }

                 } else if (($ce = $this->_getAction($app_dir, $iController, $module, $is_def_module, $action)) && is_callable([$ce, 'execute'])) {
                    $fptr = new \ReflectionMethod($ce, 'execute');

                    /** @var Action_Abstract $iAction */
                    $iAction = new $ce($request, $response, $view);
                    $executor = $iAction;

                    $nameProperty = new \ReflectionProperty($iAction, '_name');
                    $nameProperty->setAccessible(true);
                    $nameProperty->setValue($iAction, $controller);
                    $controllerProperty = new \ReflectionProperty($iAction, '_controller');
                    $controllerProperty->setAccessible(true);
                    $controllerProperty->setValue($iAction, $iController);

                    if ($fptr->getNumberOfParameters()) {
                        $call_args = $this->_getCallParameters($request, $fptr->getParameters());
                        $result = call_user_func([$iAction, 'execute'], ...$call_args);
                    } else {
                        $result = call_user_func([$iAction, 'execute']);
                    }

                    if ($result === false) {
                        /* no auto-renderring */
                        return 1;
                    }
                } else {
                    return 0;
                }

                if ($executor) {
                    /* controller's property can override the Dispatcher's */

                    $render = $executor->yafAutoRender ?? null;
                    if (!isset($render)) {
                        $render = $this->_auto_render;
                    }

                    $auto_render = $render === true || (is_int($render));
                    $instantly_flush = $this->_instantly_flush;
                    if ($auto_render) {
                        if ($instantly_flush === false) {
                            $result = internalCall($executor, 'render', $action);

                            if (!isset($result) || $result === false) {
                                return 0;
                            }

                            if (is_string($result) && !empty($result)) {
                                /** @see \Yaf\Response_Abstract::alterBody */
                                internalCall($response, 'alterBody', null, (string) $result, 'YAF_RESPONSE_APPEND');
                            }
                        } else {
                            $result = internalCall($executor, 'display', $action);

                            if (!isset($result) || $result === false) {
                                return 0;
                            }
                        }
                    } else {
                        // DO NOTHING IN PHP
                    }
                }
            }

            return 1;
        }
    }

    /**
     * @param string $app_dir
     * @param string $module
     * @param string $controller
     * @param int $def_module
     * @return int|string|null
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function _getController($app_dir, $module, $controller, $def_module)
    {
        if ($def_module) {
            $directory = sprintf("%s%s%s", $app_dir, DS, Loader::YAF_CONTROLLER_DIRECTORY_NAME);
        } else {
            $directory = sprintf("%s%s%s%s%s%s%s",
                $app_dir, DS, Loader::YAF_MODULE_DIRECTORY_NAME, DS, $module, DS, Loader::YAF_CONTROLLER_DIRECTORY_NAME);
        }

        if ($directory) {
            if (YAF_G('yaf.name_suffix')) {
                $class = sprintf("%s%s%s", $controller, YAF_G('yaf.name_separator'), 'Controller');
            } else {
                $class = sprintf("%s%s%s", 'Controller', YAF_G('yaf.name_separator'), $controller);
            }

            $class_lowercase = strtolower($class);

            if (!($ce = \YP\getClassEntry($class_lowercase))) {
                if (!Loader::_internalAutoload($controller, strlen($controller), $directory)) {
                    yaf_trigger_error(CONTROLLER, "Failed opening controller script %s", $directory);
                    return null;
                } else if (!($ce = \YP\getClassEntry($class_lowercase))) {
                    yaf_trigger_error(AUTOLOAD_FAILED, 'Could not find class %s in controller script %s', $class, $directory);
                    return 0;
                } else if (get_parent_class($ce) !== 'Yaf\Controller_Abstract' && get_parent_class($ce) !== 'Yaf_Controller_Abstract') {
                    yaf_trigger_error(TYPE_ERROR, 'Controller must be an iniInstance of %s', Controller_Abstract::class);
                    return 0;
                }
            }

            return $class;
        }

        return null;
    }

    /**
     * @param string $app_dir
     * @param Controller_Abstract $controller
     * @param string $module
     * @param int $def_module
     * @param string $action
     * @return null|string
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function _getAction($app_dir, Controller_Abstract $controller, $module, $def_module, $action)
    {
        $actions_map = $controller->actions;

        if (is_array($actions_map)) {
            $action_upper = ucfirst($action);

            if (YAF_G('yaf.name_suffix')) {
                $class = sprintf("%s%s%s", $action_upper, YAF_G('yaf.name_separator'), 'Action');
            } else {
                $class = sprintf("%s%s%s", 'Action', YAF_G('yaf.name_separator'), $action_upper);
            }

            $class_lowercase = $class;

            if (class_exists($class_lowercase)) {
                if (get_parent_class($class_lowercase) == 'Yaf\Action_Abstract' || get_parent_class($class_lowercase) == 'Yaf_Action_Abstract') {
                    return $class_lowercase;
                } else {
                    yaf_trigger_error(TYPE_ERROR, "Action %s must extends from %s", $class);
                    return null;
                }
            }

            $paction = $actions_map[$action] ?? null;
            if (!is_null($paction)) {

                $action_path = sprintf('%s%s%s', $app_dir, DEFAULT_SLASH, $paction);
                if (Loader::import($action_path)) {
                    if (class_exists($class_lowercase)) {
                        if ($class_lowercase instanceof Action_Abstract) {
                            return $class_lowercase;
                        } else {
                            yaf_trigger_error(TYPE_ERROR, "Action %s must extends from %s", $class, Action_Abstract::class);
                        }
                    } else {
                        yaf_trigger_error(ACTION, "Could not find action %s in %s", $class, $action_path);
                    }
                } else {
                    yaf_trigger_error(ACTION, "Failed opening action script %s", $action_path);
                }
            }
        } else if (YAF_G('yaf.st_compatible')) {
            /* This only effects internally */
            $action_upper = preg_replace_callback('/_(\w+)/', function($match) {
                return '_' . ucfirst($match[1]);
            }, $action);

            if ($def_module) {
                $directory = sprintf("%s%s%s", $app_dir, DEFAULT_SLASH, "actions");
            } else {
                $directory = sprintf("%s%s%s%s%s%s%s", $app_dir, DEFAULT_SLASH, "modules", DEFAULT_SLASH, $module, DEFAULT_SLASH, "actions");
            }

            if (YAF_G('yaf.name_suffix')) {
                $class = sprintf("%s%s%s", $action_upper, YAF_G('yaf.name_separator'), "Action");
            } else {
                $class = strlen(sprintf("%s%s%s", "Action", YAF_G('yaf.name_separator'), $action_upper));
            }

            $class_lowercase = strtolower($class);

            $ce = null;
            if (!class_exists($class_lowercase)) {
                if (!Loader::_internalAutoload($action_upper, strlen($action), $directory)) {
                    yaf_trigger_error(ACTION, "Failed opening action script %s: %s", $directory);
                    return null;
                }

                $ce = $action_upper;
                if (!class_exists($action_upper)) {
                    yaf_trigger_error(AUTOLOAD_FAILED, "Could find class %s in action script %s", $class, $directory);
                    return null;
                }

                if (!($ce instanceof Action_Abstract)) {
                    yaf_trigger_error(TYPE_ERROR, "Action must be an iniInstance of %s", Action_Abstract::class);
                    return null;
                }

                return $ce;
            }
        } else {
            $property = new \ReflectionProperty($controller, 'name');
            $property->setAccessible(true);

            yaf_trigger_error(ACTION, "There is no method %s%s in %s", $action, "Action", $property->getValue());
        }

        return null;
    }

    /**
     * @param Request_Abstract $request
     * @param \ReflectionParameter[] $callParams
     * @return array
     */
    private function _getCallParameters(Request_Abstract $request, $callParams = [])
    {
        $params = [];
        $requestParams = $request->getParams() ?: [];

        if (empty($requestParams)) {
            return $params;
        }

        foreach ($callParams as $callParam) {
            if (isset($requestParams[$callParam->getName()])) {
                $params[] = $requestParams[$callParam->getName()];
            }
        }

        return $params;
    }

    // ================================================== 内部宏 ==================================================

    /**
     * 内部宏
     *
     * @internal
     * @param Plugin_Abstract[] $plugins
     * @param $hook
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     */
    private function _pluginHandle(array $plugins, $hook, Request_Abstract $request, Response_Abstract $response)
    {
        if (!is_null($plugins)) {
            foreach ($plugins as $plugin) {
                if (is_callable([$plugin, $hook])) {
                    call_user_func([$plugin, $hook], $request, $response);
                }
            }
        }
    }

    /**
     * 内部方法, YAF对外无此方法
     *
     * 原宏位置在
     * @see yaf_exception.h
     *
     * @internal
     * @param \Exception $e
     * @param Request_Abstract $request
     * @param Response_Abstract $response
     * @return null|void
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function _exceptionHandle(\Exception $e, Request_Abstract $request, Response_Abstract $response)
    {
        if (YAF_G('catch_exception')) {
            $this->_dispatcherExceptionHandler($e, $request, $response);
        } else {
            throw $e;
        }
    }
}
