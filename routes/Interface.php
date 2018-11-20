<?php

namespace Yaf;

// TODO 类名和目录名称不一致

interface Route_Interface
{
    public function route(Request_Abstract $request);

    public function assemble(array $info, array $query = null);

    // ================================================== 内部常量 ==================================================

    public const YAF_ROUTE_ASSEMBLE_MODULE_FORMAT = ':m';
    public const YAF_ROUTE_ASSEMBLE_ACTION_FORMAT = ':a';
    public const YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT = ':c';
}