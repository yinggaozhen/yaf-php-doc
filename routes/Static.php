<?php

namespace Yaf\Route;

// TODO 类名和目录名称不一致

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Route_Static implements Route_Interface
{
    /**
     * @return true
     */
    public function match(): bool
    {
        return true;
    }

    public function route(Request_Abstract $request)
    {
        return (bool) $this->_route($request);
    }

    /**
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

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query = null): ?string
    {
        $str = '';

        do {
            if (!is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_MODULE_FORMAT])) {
                $str .= '/' . $zv;
            }

            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, sprintf("%s", "You need to specify the controller by ':c'"));
                break;
            }

            $str .= '/' . $zv;
            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, sprintf("%s", "You need to specify the action by ':a'"));
                break;
            }

            $str .= '/' . $zv;

            if ($query && is_array($query)) {
                $str .= http_build_query($query);
            }

            return $str;
        } while (0);

        return null;
    }

    private function _route(Request_Abstract $request): int
    {
        $zuri = $request->getRequestUri();
        $base_uri = $request->getBaseUri();

        $req_uri = $zuri;
        if ($base_uri && is_string($base_uri) && !strcasecmp($zuri, $base_uri)) {
            $req_uri = substr($zuri, strlen($base_uri));
        }

        $this->_pathinfoRoute($request, $req_uri);

        return 1;
    }

    private function _pathinfoRoute(Request_Abstract $request, string $req_uri)
    {
        do {
            if (empty($req_uri) || $req_uri === '/') {
                break;
            }

            // TODO pathinfo_route 看不懂
            $url = $req_uri;
        } while (0);

    }

    /**
     * @param null|string $string
     * @return null|string
     */
    private function stripSlashs(?string $string): ?string
    {
        $result = preg_split('/( |\/)/', $string);

        return end($result);
    }
}