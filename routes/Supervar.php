<?php

namespace Yaf\Route;

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Supervar implements Route_Interface
{
    protected $_var_name = null;

    /**
     * @param string $var
     * @throws \Exception
     */
    public function __construct($var)
    {
        if (!is_string($var) || empty($var)) {
            yaf_trigger_error(TYPE_ERROR, "Expects a valid string super var name");
            return false;
        }

        $this->_var_name = $var;
    }

    /**
     * @param Request_Abstract $request
     * @return bool
     * @throws \ReflectionException
     */
    public function route(Request_Abstract $request): bool
    {
        return (bool) $this->_route($request);
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    public function assemble(array $info, array $query = null): ?string
    {
        $str = $this->_assemble($info, $query);

        return is_null($str) ? $str : strval($str);
    }

    /**
     * @param array $info
     * @param array $query
     * @return null|string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query): ?string
    {
        $uri = '';
        $pname = $this->_var_name;

        do {
            $uri .= '?';
            $uri .= $pname;
            $uri .= '=';

            $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT];
            if (!is_null($zv)) {
                $uri .= '/' . $zv;
            }

            $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT];
            if (is_null($zv)) {
                yaf_trigger_error(TYPE_ERROR, "You need to specify the controller by ':c'");
                break;
            }
            $uri .= '/' . $zv;

            $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT];
            if (is_null($zv)) {
                yaf_trigger_error(TYPE_ERROR, "You need to specify the action by ':a'");
                break;
            }
            $uri .= '/' . $zv;

            if ($query && is_array($query)) {
                $uri .= http_build_query($query);
            }

            return $uri;
        } while (0);

        return null;
    }

    /**
     * @param Request_Abstract $request
     * @return int
     * @throws \ReflectionException
     */
    private function _route(Request_Abstract $request): int
    {
        $varname = $this->_var_name;
        $uri = Request_Abstract::_queryEx('GET', $varname);

        if (!$uri) {
            return 0;
        }

        $pathinfoRouteMethod = new \ReflectionMethod(Route_Static::class, '_pathinfoRoute');
        $pathinfoRouteMethod->invoke(null, $request, $uri);
        return 1;
    }
}
