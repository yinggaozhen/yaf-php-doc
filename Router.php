<?php

namespace Yaf;

use Yaf\Route\Route_Static;

/**
 * @link https://www.php.net/manual/en/class.yaf-router.php
 */
class Router
{
    /**
     * @var Route_Interface[]
     */
    protected $_routes;

    /**
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
     * @link https://www.php.net/manual/en/yaf-router.addroute.php
     *
     * @param string $name
     * @param Route_Interface $route
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
     * @link https://www.php.net/manual/en/yaf-router.addconfig.php
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
     * @link https://www.php.net/manual/en/yaf-router.route.php
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
     * @link https://www.php.net/manual/en/yaf-router.getroute.php
     *
     * @param string $name
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
     * @link https://www.php.net/manual/en/yaf-router.getroutes.php
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-router.getcurrentroute.php
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
     * @param $params
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
     * @param $configs
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
