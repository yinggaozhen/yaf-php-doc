<?php

use Yaf\Config_Abstract;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;
use Yaf\Route_Static;
use Yaf\Router;

/**
 * Yaf的路由器, 负责分析请求中的request uri, 得出目标模板, 控制器, 动作.
 *
 * @link http://www.laruence.com/manual/yaf.class.router.html
 */
class Yaf_Router
{
    /**
     * 路由器已有的路由协议栈, 默认的栈底总是名为"default"的Yaf_Route_Static路由协议的实例.
     *
     * @var Route_Interface[]
     */
    protected $_routes;

    /**
     * 在路由成功后, 路由生效的路由协议名
     *
     * @var string
     */
    protected $_current;

    /**
     * Router constructor.
     *
     * @link https://www.php.net/manual/en/yaf-router.construct.php
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->instance();
    }

    /**
     * 给路由器增加一个名为$name的路由协议
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.addRoute.html
     *
     * @param string $name 要增加的路由协议的名字
     * @param Route_Interface $route 要增加的路由协议, Yaf_Route_Interface的一个实例
     * @return $this|bool
     */
    public function addRoute($name, $route)
    {
        if (empty($name)) {
            return false;
        }

        if (!is_object($route) || !($route instanceof Route_Interface)) {
            trigger_error(sprintf('Expects a %s instance', Route_Interface::class), E_USER_WARNING);
            return false;
        }

        $this->_routes[$name] = $route;

        return $this;
    }

    /**
     * 给路由器通过配置增加一簇路由协议
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.addConfig.html
     *
     * @param array|Config_Abstract $config
     * @return $this|bool
     * @throws \Exception
     */
    public function addConfig($config)
    {
        if ($config instanceof Config_Abstract) {
            $routes = $config->_config;
        } else if (is_array($config)) {
            $routes = $config;
        } else {
            yaf_trigger_error(E_WARNING, "Expect a %s iniInstance or an array, %s given", Config_Abstract::class, gettype($config));
            return false;
        }

        if ($this->_addConfig($routes)) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * 路由一个请求, 本方法不需要主动调用, Yaf_Dispatcher::dispatch会自动调用本方法
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.route.html
     *
     * @param Request_Abstract $request
     * @return bool
     */
    public function route(Request_Abstract $request)
    {
        $routes = array_reverse($this->_routes);

        foreach ($routes as $key => $route) {
            $result = call_user_func([$route, 'route'], $request);

            if (true === $result) {
                $this->_current = $key;
                $request->setRouted();
                return true;
            }
        }

        return false;
    }

    /**
     * 获取当前路由器的路由协议栈中名为$name的协议
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.getRoute.html
     *
     * @param string $name 要获取的协议名
     * @return bool|null|Route_Interface
     */
    public function getRoute($name)
    {
        if (empty($name)) {
            return false;
        }

        return $this->_routes[$name] ?? null;
    }

    /**
     * 获取当前路由器中的所有路由协议
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.getRoutes.html
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * 在路由结束以后, 获取路由匹配成功, 路由生效的路由协议名
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.router.getCurrentRoute.html
     *
     * @return string
     */
    public function getCurrentRoute()
    {
        return $this->_current;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param string $uri
     * @param array $params
     */
    public static function _parseParameters($uri, &$params)
    {
        $params = [];
        $key = strtok($uri, Route_Interface::YAF_ROUTER_URL_DELIMIETER);

        while ($key !== false) {
            if (strlen($key)) {
                $value = strtok(Route_Interface::YAF_ROUTER_URL_DELIMIETER);
                $params[$key] = $value && strlen($value) ? $value : null;
            }
            $key = strtok(Route_Interface::YAF_ROUTER_URL_DELIMIETER);
        }
    }

    /**
     * @throws \Exception
     */
    private function instance()
    {
        /** @var Router $route */
        $route = null;
        /** @var Router[] $route */
        $routes = [];

        if (!YAF_G('default_route')) {
            static_route:
            $route = new Route_Static();
        } else {
            $route = routerInstance(YAF_G('default_route'));
            if (!is_object($route)) {
                \trigger_error(sprintf('Unable to initialize default route, use %s instead', Route_Static::class), E_USER_WARNING);
                goto static_route;
            }
        }

        $routes['_default'] = $route;
        $this->_routes = $routes;
    }

    /**
     * @param array $configs
     * @return int
     * @throws \Exception
     */
    private function _addConfig($configs)
    {
        if (empty($configs) || !is_array($configs)) {
            return 0;
        } else {
            foreach ($configs as $key => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                $route = \Yaf\routerInstance($entry);

                if (is_numeric($key)) {
                    if (empty($route)) {
                        trigger_error(sprintf("Unable to initialize route at index '%ld'", $key), E_USER_WARNING);
                        continue;
                    }
                    $this->_routes[$key] = $route;
                } else {
                    if (empty($route)) {
                        trigger_error(sprintf("Unable to initialize route named '%s'", $key), E_USER_WARNING);
                        continue;
                    }
                    $this->_routes[$key] = $route;
                }
            }

            return 1;
        }
    }
}
