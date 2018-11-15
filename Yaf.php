<?php

namespace
{
    // PHP_RINIT_FUNCTION
    ini_set('yaf.library',          '');
    ini_set('yaf.action_prefer',    '0');
    ini_set('yaf.lowcase_path',     '0');
    ini_set('yaf.use_spl_autoload', '0');
    ini_set('yaf.forward_limit',    '5');
    ini_set('yaf.name_suffix',      '1');
    ini_set('yaf.name_separator',   '');
    ini_set('yaf.st_compatible',    '0');
    ini_set('yaf.environ',          'product');
    ini_set('yaf.use_namespace',    '0');

    // RINIT_FUNCTION
    $GLOBALS['yaf']['throw_exception']    = 1;
    $GLOBALS['yaf']['ext']                = 'php';
    $GLOBALS['yaf']['view_ext']           = 'phtml';
    $GLOBALS['yaf']['default_module']     = 'Index';
    $GLOBALS['yaf']['default_action']     = 'Index';
    $GLOBALS['yaf']['default_controller'] = 'Index';

    /**
     * 内部YAF全局(内部方法,外部不可调用)
     *
     * @param $name
     * @param null $value
     * @return null|string
     */
    function YAF_G($name, $value = null)
    {
        static $internal_g = [];

        if (!is_null($value)) {
            return $internal_g[$name] = $value;
        }

        /**
         * 兼容写法
         *
         * @see \Yaf\Loader::clearLocalNamespace
         */
        if ($value === 'NULL') {
            return $internal_g[$name] = null;
        }

        if (isset($internal_g[$name])) {
            return $internal_g[$name];
        }

        if (ini_get('yaf.' . $name)) {
            return ini_get('yaf.' . $name);
        }

        if (isset($GLOBALS['yaf'][$name])) {
            return $GLOBALS['yaf'][$name];
        }

        return null;
    }
}

/**
 * 只适用于内部的常量(PHP常量)
 */
namespace INTERNAL\PHP
{
    const DEFAULT_SLASH = DIRECTORY_SEPARATOR;
}

// MINIT_FUNCTION
namespace YAF
{
    const ENVIRON          = '';
    const VERSION          = '3.0.8-dev';
}
namespace YAF\ERR
{
    const STARTUP_FAILED   = 512;
    const ROUTE_FAILED     = 513;
    const DISPATCH_FAILED  = 514;
    const CALL_FAILED      = 519;
    const AUTOLOAD_FAILED  = 520;
    const TYPE_ERROR       = 521;
}
namespace YAF\ERR\NOTFOUND
{
    const MODULE           = 515;
    const CONTROLLER       = 516;
    const ACTION           = 517;
    const VIEW             = 518;
}