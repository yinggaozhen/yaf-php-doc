<?php

namespace Yaf\Route;

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;
use Yaf\Router;

/**
 * @link https://www.php.net/manual/en/class.yaf-route-map.php
 */
final class Map implements Route_Interface
{
    /**
     * @var bool
     */
    protected $_ctl_router = false;

    protected $_delimiter;

    /**
     * Map constructor.
     *
     * @link https://www.php.net/manual/en/yaf-route-map.construct.php
     *
     * @param bool $controller_prefer
     * @param string $delimer
     */
    public function __construct($controller_prefer = false, $delimer = null)
    {
        $this->_ctl_router = $controller_prefer;

        if (!empty($delimer)) {
            $this->_delimiter = $delimer;
        }
    }

    /**
     * @link https://www.php.net/manual/en/yaf-route-map.route.php
     *
     * @param Request_Abstract $request
     * @return bool
     */
    public function route($request)
    {
        if (!($request instanceof Request_Abstract)) {
            return;
        }

        return (bool) $this->_route($request);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-route-map.assemble.php
     *
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    public function assemble(array $info, array $query = null)
    {
        $str = $this->_assemble($info, $query);

        return !is_null($str) ? $str : null;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param Request_Abstract $request
     * @return int
     */
    private function _route(Request_Abstract $request)
    {
        $uri = $request->getRequestUri();
        $base_uri = $request->getBaseUri();

        $ctl_prefer = $this->_ctl_router;
        $delimer = $this->_delimiter;

        if ($base_uri && is_string($base_uri) && !strncasecmp($uri, $base_uri, strlen($base_uri))) {
            $req_uri = substr($uri, strlen($base_uri));
        } else {
            $req_uri = $uri;
        }

        $query_str = null;
        if (is_string($delimer) && !empty($delimer)) {
            $query_str = explode($delimer, $req_uri)[1];

            if ($query_str[0] == '/') {
                $query_str = substr($query_str, 1);
            }
        }

        $route_result = implode('_', array_filter(explode(Route_Interface::YAF_ROUTER_URL_DELIMIETER, $req_uri)));
        if ($route_result) {
            if ($ctl_prefer === true) {
                $request->setControllerName($route_result);
            } else {
                $request->setActionName($route_result);
            }
        }

        if ($query_str) {
            Router::_parseParameters($query_str, $param);
		    Request_Abstract::_setParamsMulti($request, $param);
        }

        return 1;
    }

    /**
     * @param array $info
     * @param array $query
     * @return string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query)
    {
        $uri = '';
        $has_delim = false;

        $ctl_prefer = $this->_ctl_router;
        $delim = $this->_delimiter;

        if (is_string($delim) && !empty($delim)) {
            $has_delim = true;
        }

        do {
            if ($ctl_prefer === true) {
                $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT] ?? null;

                if (!is_null($zv) && is_string($zv)) {
                    $pname = $zv;
                } else {
                    yaf_trigger_error(TYPE_ERROR, "Undefined the 'action' parameter for the 1st parameter");
                    break;
                }

            } else {
                $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT] ?? null;
                if (!is_null($zv) && is_string($zv)) {
                    $pname = $zv;
                } else {
                    yaf_trigger_error(TYPE_ERROR, "Undefined the 'controller' parameter for the 1st parameter");
                    break;
                }
            }

            foreach (explode('_', $pname) as $seg) {
                $uri .= empty($seg) ? '' : '/' . $seg;
            }

            if ($query && is_array($query)) {
                $start = 1;

                if ($has_delim) {
                    $uri .= '/' . $delim;
                }

                foreach ($query as $key => $zv) {
                    $val = (string) $zv;

                    if ($has_delim) {
                        // 参数变成URI一部分，例如/uri/a/va/b/vb
                        $uri .= '/' . $key . '/' . $val;
                    } else {
                        // 参数变成QUERY，例如/uri?a=va&b=vb
                        if ($start) {
                            $uri .= '?' . $key . '=' . $val;
                            $start = 0;
                        } else {
                            $uri .= '&' . $key . '=' . $val;
                        }

                    }
                }
            }

            return $uri;
        } while (0);

        return null;
    }
}
