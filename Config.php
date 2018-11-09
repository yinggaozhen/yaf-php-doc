<?php

namespace Yaf;

abstract class Config_Abstract
{
    // 其实是protected
    public $_config;

    // 其实是protected
    public $_readonly;

    abstract function get();

    abstract function set();
}