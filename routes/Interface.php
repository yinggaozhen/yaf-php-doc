<?php

namespace Yaf;

// TODO 类名和目录名称不一致

abstract class Route_Interface
{
    function __construct(array $config)
    {
    }

    abstract public function route();

    abstract public function assemble();
}