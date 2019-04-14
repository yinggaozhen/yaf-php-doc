<?php

namespace Yaf;

class Router
{
    /**
     * @var Route_Interface
     */
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
     * @throws \Exception
     */
    public function addConfig($config)
    {
        if ($config instanceof Config_Abstract) {
            $routes = $config->_config;
        } else if (is_array($config)) {
            $routes = $config;
        } else {
            yaf_trigger_error(E_WARNING, "Expect a %s instance or an array, %s given", Config_Abstract::class, gettype($config));
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
        $routes = array_reverse($this->_routes);

        foreach ($routes as $key => $route) {
            $result = call_user_func([$route, 'route'], $request);

            if (true === $result) {
                $this->_current = $key;
            }
            $request->setRouted();

            return true;
        }

        return false;
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

    /**
     * @param $configs
     * @return int
     * @throws \Exception
     */
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
                        yaf_trigger_error(E_WARNING, "Unable to initialize route at index '%ld'", $key);
                    } else {
                        yaf_trigger_error(E_WARNING, "Unable to initialize route named '%s'", $key);
                    }

                    continue;
                }

                $this->_routes[$key] = $route;
            }
            return 1;
        }
    }
}
