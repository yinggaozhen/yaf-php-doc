<?php

namespace Yaf\Route;

// TODO 类名和目录名称不一致

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class SimpleTODO implements Route_Interface
{
    protected $controller;

    protected $module;

    protected $action;

    public function __construct(string $module, string $controller, string $action)
    {
        if (!is_string($module) || !is_string($controller) || !is_string($action)) {
            trigger_error("Expect 3 string parameters", TYPE_ERROR);

            return false;
        }
    }

    /**
     * @param Request_Abstract $request
     * @return bool
     */
    public function route(Request_Abstract $request): bool
    {
        // TODO YAF_TRIGGER_ERROR
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     */
    public function assemble(array $info, array $query = null): ?string
    {
    }
}