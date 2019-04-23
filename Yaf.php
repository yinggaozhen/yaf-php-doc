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

    /**
     * @internal
     * Class YAF_G_V
     */
    class YAF_G_V
    {
        public static $globals = [];
    }

    // RINIT_FUNCTION
    YAF_G_V::$globals['throw_exception']    = 1;
    YAF_G_V::$globals['ext']                = 'php';
    YAF_G_V::$globals['view_ext']           = 'phtml';
    YAF_G_V::$globals['default_module']     = 'Index';
    YAF_G_V::$globals['default_action']     = 'index';
    YAF_G_V::$globals['default_controller'] = 'Index';

    // ================================================== 内部方法 ==================================================

    /**
     * YAF内部全局(内部方法,外部不可调用)
     *
     * @param array $args
     * @return null|*
     */
    function YAF_G(...$args)
    {
        @list($name, $value) = $args;

        if (count($args) == 1) {
            return YAF_G_V::$globals[$name] ?? null;
        }

        YAF_G_V::$globals[$name] = $value;
    }

    /**
     * YAF内部触发错误方法(内部方法,外部不可调用)
     *
     * @param int $type
     * @param string[] $format
     * @throws Exception
     */
    function yaf_trigger_error(int $type, ...$format)
    {
        $message = call_user_func_array('sprintf', $format);

        if (YAF_G('throw_exception')) {
            if (($type & \Yaf\Exception\Internal\YAF_ERR_BASE) === \Yaf\Exception\Internal\YAF_ERR_BASE) {
                /** @var \Exception $exception */
                $exception = \Yaf\Exception\Internal\yaf_buildin_exceptions($type);
                throw new $exception($message, $type);
            }

            throw new \Exception($message, $type);
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
            $errMsgProperty->setValue($app, $message);

            trigger_error(E_RECOVERABLE_ERROR, $message);
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
