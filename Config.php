<?php

namespace Yaf;

use Yaf\Config\Ini;
use Yaf\Config\Simple;
use const YAF\ERR\TYPE_ERROR;

abstract class Config_Abstract
{
    /**
     * @access protected
     * @array
     */
    public $_config;

    /**
     * @access protected
     * @var bool
     */
    public $_readonly = true;

    abstract function get();

    abstract function set();

    /**
     * @param string|array $arg1
     * @param string $arg2
     * @return null|Ini|Simple
     * @throws \Exception
     */
    private static function instance($arg1, $arg2)
    {
        if (is_string($arg1)) {
            if (pathinfo($arg1, PATHINFO_EXTENSION) === 'ini') {
                /** @var \Yaf\Config\Ini $instance */
                $instance = new Ini($arg1, $arg2);

                return $instance ?: null;
            }
            yaf_trigger_error(TYPE_ERROR, "Expects a path to *.ini configuration file as parameter");

            return null;
        } else if (is_array($arg1)) {
            $readonly = true;
            /** @var \Yaf\Config\Simple $instance */
            $instance = new Simple($arg1, $readonly);

            return $instance;
        }

        yaf_trigger_error(TYPE_ERROR, "Expects a string or an array as parameter");
        return null;

    }
}
