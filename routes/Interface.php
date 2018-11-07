<?php

namespace Yaf;

// TODO 类名和目录名称不一致

interface Route_Interface
{
    public function route(Request_Abstract $request);

    public function assemble();
}