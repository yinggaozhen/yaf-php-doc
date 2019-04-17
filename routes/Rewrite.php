<?php

namespace Yaf\Route;

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Rewrite implements Route_Interface
{
    /**
     * @var string
     */
    protected $_match;

    protected $_route;

    protected $_verify;

    /**
     * Rewrite constructor.
     * @param string $match
     * @param array $route
     * @param array|null $verify
     * @throws \Exception
     */
    public function __construct(string $match, array $route, array $verify = null)
    {
        if (!is_string($match) || empty($match)) {
            yaf_trigger_error(TYPE_ERROR, "Expects a valid string match as the first parameter");
            return false;
        }

        if ($verify && !is_array($verify)) {
            yaf_trigger_error(TYPE_ERROR, "Expects an array as third parameter");
            return false;
        }

        $this->_match = $match;
        $this->_route = $route;

        if (!$verify) {
            $this->_verify = null;
        } else {
            $this->_verify = $verify;
        }
    }

    /**
     * @param null|Request_Abstract $request
     * @return bool
     */
    public function route(?Request_Abstract $request): bool
    {
        if (!$request || !is_object($request) || !($request instanceof Request_Abstract)) {
            trigger_error("Expect a %s instance", get_class($request));
            return false;
        }

        // TODO 从这里开始写
        // RETURN_BOOL(yaf_route_rewrite_route(route, request));
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     */
    public function assemble(array $info, array $query = null): ?string
    {
        if ($str = $this->_assemble($info, $query)) {
            return $str;
        }

        return null;
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     */
    private function _assemble(array $info, ?array $query): ?string
    {
        $query_str = $wildcard = '';

        $uri = $match = $this->_match;
        $pidents = $info;

        $seg = strtok($match, Route_Interface::YAF_ROUTER_URL_DELIMIETER);
        while ($seg) {
            if (!empty($seg)) {
                if ($seg == '*') {
                    foreach ($pidents as $key => $zv) {
                        if ($key) {
                            if (is_string($zv)) {
                                $wildcard .= $key . Route_Interface::YAF_ROUTER_URL_DELIMIETER;
                                $wildcard .= Route_Interface::YAF_ROUTER_URL_DELIMIETER;
                                $wildcard .= $zv;
                                $wildcard .= Route_Interface::YAF_ROUTER_URL_DELIMIETER;
                            }
                        }
                    }

                    $uri = $uri . '*' . $wildcard;
                }

                if ($seg == ':') {
                    $zv = $info[$seg] ?? null;

                    if (!is_null($zv)) {
                        $val = $zv;
                        $uri = $uri . $seg . $val;

                        unset($pidents[$seg]);
                    }
                }
            }

            $seg = strtok(Route_Interface::YAF_ROUTER_URL_DELIMIETER);
        }

        if ($query && is_array($query)) {
            $query_str .= '?' . http_build_query($query);
        }

        return $uri . $query_str;
    }
}
