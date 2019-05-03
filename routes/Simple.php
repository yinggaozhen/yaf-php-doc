<?php

namespace Yaf\Route;

use Yaf\Application;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Simple implements Route_Interface
{
    protected $controller;

    protected $module;

    protected $action;

    /**
     * @param string $module
     * @param string $controller
     * @param string $action
     * @throws \Exception
     */
    public function __construct(string $module, string $controller, string $action)
    {
        if (!is_string($module) || !is_string($controller) || !is_string($action)) {
            yaf_trigger_error(TYPE_ERROR, "Expect 3 string parameters");

            return false;
        }

        $this->module = $module;
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * @param Request_Abstract $request
     * @return bool
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

        return $str;
    }

    /**
     * @param array $info
     * @param array $query
     * @return null|string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query): ?string
    {
        $uri = '?';

        $nmodule = $this->module;
        $ncontroller = $this->controller;
        $naction = $this->action;

        do {
            if (!is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT])) {
                $uri .= $nmodule . '=' . $zv . '&';
            }

            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, "You need to specify the controller by ':c'");
                break;
            }

            $uri .= $ncontroller . '=' . $zv . '&';

            if (is_null($zv = $info[self::YAF_ROUTE_ASSEMBLE_ACTION_FORMAT])) {
                yaf_trigger_error(TYPE_ERROR, "You need to specify the action by ':a'");
                break;
            }

            $uri .= $naction . '=' . $zv;

            if (!empty($query) && is_array($query)) {
                $uri .= http_build_query($query);
            }

            return $uri;
        } while (0);

        return null;
    }

    private function _route(Request_Abstract $request): int
    {
        $nmodule = $this->module;
        $ncontroller = $this->controller;
        $naction = $this->action;

        // TODO yaf_request_query 其实就是 request::query_ex
        $module = Request_Abstract::_queryEx('GET', $nmodule);
        $controller = Request_Abstract::_queryEx('GET', $ncontroller);
        $action = Request_Abstract::_queryEx('GET', $naction);

        if ($module && is_string($module) && Application::isModuleName($module)) {
            $request->setModuleName($module);
        }

        if ($controller) {
            $request->setControllerName($controller);
        }

        if ($action) {
            $request->setActionName($action);
        }

        return 1;
    }
}
