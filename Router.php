<?php

namespace Yaf;

class Router
{
    protected $_routes;

    protected $_current;

    public function __construct()
    {
        $route = ['default' => []];

        $this->_routes = $route;
    }

    /**
     * @param string $name
     * @param Route_Interface $route
     * @return $this|bool
     */
    public function addRoute(string $name, Route_Interface $route)
    {
        if (empty($name)) {
            return false;
        }

        $routes = $this->_routes;
        $this->_routes[$name] = $route;

        return $this;
    }

    /**
     * @param array|Config_Abstract $config
     * @return $this|bool
     */
    public function addConfig($config)
    {
        if ($config instanceof Config_Abstract) {
            $routes = $config->_config;
        } else if (is_array($config)) {
            $routes = $config;
        } else {
            trigger_error(sprintf("Expect a %s instance or an array, %s given", Config_Abstract::class, gettype($config)), E_WARNING);
            return false;
        }

        if ($this->_addRoute($routes)) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * @param Request_Abstract $request
     * @return bool
     */
    public function route(Request_Abstract $request): bool
    {
        $routes = $this->_routes;

        foreach ($routes as $key => $route) {
            // TODO 这里看不懂
        }
    }

    /**
     * @param string $name
     * @return bool|null
     */
    public function getRoute(string $name): ?bool
    {
        if (empty($name)) {
            return false;
        }

        $route = $this->_routes[$name];
        return is_null($route) ? $route : null;
    }

    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->_routes;
    }

    /**
     * @return mixed
     */
    public function getCurrentRoute()
    {
        return $this->_current;
    }

    private function _addRoute($configs): int
    {
        if (empty($configs) || !is_array($configs)) {
            return 0;
        } else {
            $routes = $this->_routes;

            foreach ($configs as $key => $entry) {
                if (!is_array($entry)) {
                    continue;
                }

                // TODO 源码是有问题的，会实例化比存在的路由类
                try {
                    $route = new $entry();
                } catch (\Exception $e) {
                    if (is_numeric($key)) {
                        trigger_error(sprintf("Unable to initialize route at index '%ld'", $key), E_WARNING);
                    } else {
                        trigger_error(sprintf("Unable to initialize route named '%s'", $key), E_WARNING);
                    }

                    continue;
                }

                $this->_routes[$key] = $route;
            }
            return 1;
        }
    }
}