<?php

use Yaf\Application;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;
use Yaf\Router;

/**
 * @link https://www.php.net/manual/en/class.yaf-route-static.php
 */
final class Yaf_Route_Static implements Route_Interface
{
    /**
     * @link https://www.php.net/manual/en/yaf-route-static.match.php
     *
     * @return true
     */
    public function match()
    {
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-route-static.route.php
     *
     * @param Request_Abstract $request
     * @return bool
     */
    public function route($request)
    {
        if (!($request instanceof Request_Abstract)) {
            return;
        }

        return (bool)$this->_route($request);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-route-static.assemble.php
     *
     * @param array $info
     * @param array|null $query
     * @return bool|null|string
     * @throws \Exception
     */
    public function assemble(array $info, array $query = null)
    {
        $str = $this->_assemble($info, $query);

        return $str ?? false;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query = null)
    {
        $str = '';

        do {
            if (!is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT])) {
                $str .= '/' . $zv;
            }

            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, "%s", "You need to specify the controller by ':c'");
                break;
            }

            $str .= '/' . $zv;
            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, "%s", "You need to specify the action by ':a'");
                break;
            }

            $str .= '/' . $zv;

            foreach ($query as $key => $value) {
                if (!is_string($key)) {
                    unset($query[$key]);
                }
            }
            if ($query && is_array($query)) {
                $str .= '?' . http_build_query($query);
            }

            return $str;
        } while (0);

        return null;
    }

    private function _route(Request_Abstract $request)
    {
        $zuri = $request->getRequestUri();
        $baseUri = $request->getBaseUri();

        $req_uri = $zuri;
        if ($baseUri && is_string($baseUri) && !strcasecmp($zuri, $baseUri)) {
            $req_uri = substr($zuri, strlen($baseUri));
        }

        $this->_pathInfoRoute($request, $req_uri);

        return 1;
    }

    /**
     * @param Request_Abstract $request
     * @param string $req_uri
     * @return bool
     */
    private function _pathInfoRoute(Request_Abstract $request, $req_uri)
    {
        $module = $controller = $action = $rest = null;

        do {
            if (empty($req_uri) || $req_uri === '/') {
                break;
            }

            $path = trim($req_uri, Route_Interface::YAF_ROUTER_URL_DELIMIETER);
            if (!empty($path)) {
                $path = array_filter(explode(Route_Interface::YAF_ROUTER_URL_DELIMIETER, $path), 'strlen');

                if (Application::isModuleName($path[0])) {
                    $module = array_shift($path);
                }
                if (!empty($path[0])) {
                    $controller = array_shift($path);
                }
                if (!empty($path[0])) {
                    $action = array_shift($path);
                }

                $rest = implode(Route_Interface::YAF_ROUTER_URL_DELIMIETER, $path);
                $actionPrefer = YAF_G('yaf.action_prefer');

                if (!$module && !$controller && !$action) {
                    if ($actionPrefer) {
                        $action = $rest;
                    } else {
                        $controller = $rest;
                    }
                    $rest = null;
                } else if (!$module && !$action && !$rest) {
                    if ($actionPrefer) {
                        $action = $controller;
                        $controller = null;
                    }
                } else if (!$controller && !$action && $rest) {
                    $controller = $module;
                    $action = $rest;
                    $module = null;
                    $rest = null;
                } else if (!$action && !$rest) {
                    $action = $controller;
                    $controller = $module;
                    $module = null;
                } else if (!$controller && !$action) {
                    $controller = $module;
                    $action = $rest;
                    $module = null;
                    $rest = null;
                } else if (!$action) {
                    $action = $rest;
                    $rest = null;
                }
            }
        } while (0);

        $module and $request->setModuleName($module);
        $controller and $request->setControllerName($controller);;
        $action and $request->setActionName($action);

        $params = [];
        if (!empty(trim($rest))) {
            Router::_parseParameters($rest, $params);
            Request_Abstract::_setParamsMulti($request, $params);
        }

        return true;
    }

    /**
     * @param null|string $string
     * @return null|string
     */
    private function stripSlashs($string)
    {
        $result = preg_split('/( |\/)/', $string);

        return end($result);
    }
}
