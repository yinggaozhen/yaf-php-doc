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
 * @link https://www.php.net/manual/en/class.yaf-dispatcher.php
 */
class Yaf_Dispatcher
{
    /**
     * @var Dispatcher
     */
    protected static $_instance = null;

    /**
     * @var Router
     */
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.getinstance.php
     *
     * @return Yaf_Dispatcher
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.dispatch.php
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.enableview.php
     *
     * @return Yaf_Dispatcher
     */
    public function enableView()
    {
        $this->_auto_render = true;

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.disableview.php
     *
     * @return Yaf_Dispatcher
     */
    public function disableView()
    {
        $this->_auto_render = false;

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.initview.php
     *
     * @param string $templates_dir
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.setview.php
     *
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.setrequest.php
     *
     * @param Request_Abstract $request
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.getapplication.php
     */
    public function getApplication()
    {
        // TODO PHP_MN(yaf_application_app)(INTERNAL_FUNCTION_PARAM_PASSTHRU);什么意思
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.getrouter.php
     *
     * @return Router
     */
    public function getRouter()
    {
        return $this->_router;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.getrequest.php
     *
     * @return Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-dispatcher.setdefaultmodule.php
     *
     * @param string $module
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.setdefaultcontroller.php
     *
     * @param string $controller
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.setdefaultaction.php
     *
     * @param string $action
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.returnresponse.php
     *
     * @param bool $auto_response
     * @return $this|int
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.flushinstantly.php
     *
     * @param bool $flag
     * @return $this|int
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.seterrorhandler.php
     *
     * @param callable $callback
     * @param int $error_type
     * @return Yaf_Dispatcher | boolean
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.autorender.php
     *
     * @param bool $flag
     * @return $this|int
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.throwexception.php
     *
     * @param bool $flag
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.catchexception.php
     *
     * @param bool $flag
     * @return $this|bool
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
     * @link https://www.php.net/manual/en/yaf-dispatcher.registerplugin.php
     *
     * @param Plugin_Abstract $plugin
     * @return $this|bool
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
                                $response->setBody((string) $result, 'YAF_RESPONSE_APPEND');
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
