<?php

namespace
{
    // PHP_RINIT_FUNCTION

    use Yaf\Application;

    /**
     * @internal
     * Class YAF_G_V
     */
    class YAF_INI_V
    {
        public static $ini = [];
    }

    YAF_INI_V::$ini['yaf.library']          = '';
    YAF_INI_V::$ini['yaf.action_prefer']    = '0';
    YAF_INI_V::$ini['yaf.lowcase_path']     = '0';
    YAF_INI_V::$ini['yaf.use_spl_autoload'] = '0';
    YAF_INI_V::$ini['yaf.forward_limit']    = '5';
    YAF_INI_V::$ini['yaf.name_suffix']      = '1';
    YAF_INI_V::$ini['yaf.name_separator']   = '';
    YAF_INI_V::$ini['yaf.st_compatible']    = '0';
    YAF_INI_V::$ini['yaf.environ']          = 'product';
    YAF_INI_V::$ini['yaf.use_namespace']    = '0';

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
        $name = $args[0] ?? null;
        $value = $args[1] ?? null;

        if (count($args) == 1) {
            if (array_key_exists($name, YAF_INI_V::$ini)) {
                return YAF_INI_V::$ini[$name];
            }

            return YAF_G_V::$globals[$name] ?? null;
        }

        if (array_key_exists($name, YAF_INI_V::$ini)) {
            YAF_INI_V::$ini[$name] = $value;
        } else {
            YAF_G_V::$globals[$name] = $value;
        }
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
            $base_exception = \YAF\Exception::class;
            if (($type & \Yaf\Exception\Internal\YAF_ERR_BASE) === \Yaf\Exception\Internal\YAF_ERR_BASE) {
                /** @var \Exception $exception */
                $base_exception = \Yaf\Exception\Internal\yaf_buildin_exceptions($type);
            }

            throw new $base_exception($message, $type);
        } else {
            $property = new \ReflectionProperty(Application::class, '_app');
            $property->setAccessible(true);
            /** @var Application $app */
            $app = $property->getValue(null);

            $errNoProperty = new ReflectionProperty($app, '_err_no');
            $errNoProperty->setAccessible(true);
            $errNoProperty->setValue($app, $type);

            $errMsgProperty = new ReflectionProperty($app, '_err_msg');
            $errMsgProperty->setAccessible(true);
            $errMsgProperty->setValue($app, $message);

            trigger_error($message);
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

/**
 * Yaf-PHP Internal Namespace
 */
namespace YP
{
    /**
     * @param $object
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \ReflectionException
     */
    function internalCall($object, $method, ...$params)
    {
        $reflectionMethod = new \ReflectionMethod($object, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $params);
    }


    /**
     * @param $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    function internalPropertyGet($object, $property)
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }

    /**
     * 判断是否为绝对路径
     *
     * @param string $path
     * @return bool
     */
    function isAbsolutePath($path)
    {
        return is_string($path) && preg_match('/^\//', $path);
    }

    function getClassEntry($clazz)
    {
        $declaredClasses = get_declared_classes();

        foreach ($declaredClasses as $class) {
            if (!strnatcasecmp($class, $clazz)) {
                return $class;
            }
        }

        return null;
    }
}
