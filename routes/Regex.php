<?php

namespace Yaf\Route;

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

class Regex implements Route_Interface
{
    protected $_route = null;
    protected $_default = null;
    protected $_maps = null;
    protected $_verify = null;
    protected $_reverse = null;

    /**
     * Regex constructor.
     * @param string $match
     * @param array $route
     * @param array $map
     * @param array $verify
     * @param string $reverse
     * @throws \Exception
     */
    public function __construct($match, $route, $map = null, $verify = null, $reverse = null)
    {
        if (!is_string($match) || !strlen($match)) {
            yaf_trigger_error(TYPE_ERROR, 'Expects a valid string as the first parameter');
            return false;
        }

        if ($verify && !is_array($verify)) {
            yaf_trigger_error(TYPE_ERROR, 'Expects an array as third parameter');
            return false;
        }

        if ($reverse && !is_string($reverse)) {
            yaf_trigger_error(TYPE_ERROR, 'Expects a valid string reverse as fourth parameter');
            return false;
        }

        $this->_instance($match, $route, $map, $verify, $reverse);
    }

    /**
     * @param Request_Abstract $request
     * @return bool
     */
    public function route($request): bool
    {
        if (empty($request) || !is_object($request) || !($request instanceof Request_Abstract)) {
            trigger_error(sprintf('"Expects a %s instance"', Request_Abstract::class), E_USER_WARNING);
            return false;
        }

        return (bool)$this->_regexRoute($request);
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    public function assemble(array $info, array $query = null)
    {
        $str = $this->_assemble($info, $query);

        if (!is_null($str)) {
            return (string)$str;
        }

        return null;
    }

    private function _instance($route, $def, $map, $verify, $reverse)
    {
        $this->_route = $route;
        $this->_default = $def;

        if ($map) {
            $this->_maps = $map;
        }

        $this->_verify = $verify ?: null;
        $this->_reverse = !$reverse || !is_string($reverse) ? null : $reverse;
    }

    /**
     * @param Request_Abstract $request
     * @return int
     */
    private function _regexRoute(Request_Abstract $request): int
    {
        $zuri = $request->getRequestUri();
        $baseUri = $request->getBaseUri();

        if ($baseUri && is_string($baseUri) && !strncasecmp($zuri, $baseUri, strlen($baseUri))) {
            $requestUri = substr($zuri, strlen($baseUri));
        } else {
            $requestUri = $zuri;
        }

        $args = null;
        if (!$this->_regexMatch($requestUri, $args)) {
            return 0;
        } else {
            $routes = $this->_default;
            $module = $routes['module'] ?? null;
            if (isset($module) && is_string($module)) {
                if ($module[0] != ':') {
                    $request->setModuleName($module);
                } else {
                    $m = $args[substr($module, 1)];
                    if (isset($m) != NULL && is_string($m)) {
                        $request->setModuleName($m);
                    }
                }
            }

            $controller = $routes['controller'] ?? null;
            if (isset($controller) && is_string($controller)) {
                if ($controller[0] != ':') {
                    $request->setControllerName($controller);
                } else {
                    $c = $args[substr($controller, 1)];
                    if (isset($c) != NULL && is_string($c)) {
                        $request->setControllerName($c);
                    }
                }
            }

            $action = $routes['action'] ?? null;
            if (isset($action) && is_string($action)) {
                if ($action[0] != ':') {
                    $request->setActionName($action);
                } else {
                    $a = $args[substr($action, 1)];
                    if (isset($a) != NULL && is_string($a)) {
                        $request->setActionName($a);
                    }
                }
            }

            Request_Abstract::_setParamsMulti($request, $args);
        }

        return 1;
    }

    /**
     * @param array $info
     * @param array $query
     * @return null
     * @throws \Exception
     */
    private function _assemble($info, $query)
    {
        $reverse = $this->_reverse;

        if (!is_string($reverse)) {
            yaf_trigger_error(TYPE_ERROR, "Reverse property is not a valid string");
            return null;
        }

        $uri = $reverse;

        if (($zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT]) != null) {
            $inter = str_replace($uri, Route_Interface::YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT, (string)$zv);
            $uri = $inter;
        }

        if (($zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT]) != null) {
            $inter = str_replace($uri, Route_Interface::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT, (string)$zv);
            $uri = $inter;
        }

        if (($zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT]) != null) {
            $inter = str_replace($uri, Route_Interface::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT, (string)$zv);
            $uri = $inter;
        }

        if ($query && is_array($query)) {
            $uri .= '?' . http_build_query($query);
        }

        return $uri;
    }

    /**
     * @param string $uri
     * @param $result
     * @return int
     */
    private function _regexMatch($uri, &$result): int
    {
        if (strlen($uri) === 0) {
            return 0;
        }

		$matched = preg_match_all((string) $this->_route, $uri, $matches);
		if (!$matched) {
		    return 0;
        }

        foreach ($matches as $key => $pzval) {
		    if (!is_numeric($key)) {
		        $result[$key] = $pzval[0];
            } else {
		        $name = $this->_maps[$key] ?? null;
		        if (is_array($this->_maps) && !is_null($name) && is_string($name)) {
		            $result[$name] = $pzval[0];
                }
            }
        }

        return 1;
    }
}
