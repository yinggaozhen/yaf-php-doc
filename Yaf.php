<?php

namespace
{
    // PHP_RINIT_FUNCTION

    use Yaf\Application;

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

    // ================================================== 内部方法 ==================================================

    /**
     * YAF内部全局(内部方法,外部不可调用)
     *
     * @param $name
     * @param null $value
     * @return null|string
     */
    function YAF_G($name, $value = null)
    {
        static $internalVars = [];

        if (!is_null($value)) {
            return $internalVars[$name] = $value;
        }

        /**
         * 兼容写法
         *
         * @see \Yaf\Loader::clearLocalNamespace
         */
        if ($value === 'NULL') {
            return $internalVars[$name] = null;
        }

        if (isset($internalVars[$name])) {
            return $internalVars[$name];
        }

        if (ini_get('yaf.' . $name)) {
            return ini_get('yaf.' . $name);
        }

        if (isset($GLOBALS['yaf'][$name])) {
            return $GLOBALS['yaf'][$name];
        }

        return null;
    }

    /**
     * YAF内部触发错误方法(内部方法,外部不可调用)
     *
     * @param int $type
     * @param string[] $format
     * @throws Exception
     */
    function yaf_trigger_error(int $type, string ...$format): void
    {
        if (YAF_G('throw_exception')) {
            if ($type & \Yaf\Exception\Internal\YAF_ERR_BASE === \Yaf\Exception\Internal\YAF_ERR_BASE) {
                /** @var \Exception $exception */
                $exception = \Yaf\Exception\Internal\yaf_buildin_exceptions($type);
                throw new $exception($format[0], $type);
            }

            throw new \Exception($format[0], $type);
        } else {
            $property = new \ReflectionProperty(Application::class, '_app');
            $property->setAccessible(true);
            /** @var Application $app */
            $app = $property->getValue();

            $errNoProperty = new ReflectionProperty($app, '_err_no');
            $errNoProperty->setAccessible(true);
            $errNoProperty->setValue($app, $type);

            $errMsgProperty = new ReflectionProperty($app, '_err_msg');
            $errMsgProperty->setAccessible(true);
            $errMsgProperty->setValue($app, $format[0]);

            trigger_error($format[0], E_RECOVERABLE_ERROR);
        }
    }
}

/**
 * 只适用于内部的常量(PHP常量)
 */
namespace INTERNAL\PHP
{
    const DEFAULT_SLASH = DIRECTORY_SEPARATOR;
    const DEFAULT_DIR_SEPARATOR = DIRECTORY_SEPARATOR;

    const SUCCESS = 1;
    const FAILURE = 0;
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
