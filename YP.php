<?php

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


namespace
{
    // TODO complete this
    // class Yaf_Exception extends \Yaf\Exception {}
    // class Yaf_Config_Ini extends \Yaf\Config\Ini {}
    // class Yaf_Config_Simple extends \Yaf\Config\Simple {}
    // class Yaf_Request_Http extends \Yaf\Request\Http {}
    // class Yaf_Dispatcher extends \Yaf\Dispatcher {}
    // class Yaf_Request_Simple extends \Yaf\Request\Simple {}
    // class Yaf_Response_Cli extends \Yaf\Response\Cli {}
    // class Yaf_Response_Http extends \Yaf\Response\Http {}
    // class Yaf_Route_Map extends \Yaf\Route\Map {}
    // class Yaf_Route_Regex extends \Yaf\Route\Regex {}
    // class Yaf_Route_Rewrite extends \Yaf\Route\Rewrite {}
    // class Yaf_Route_Simple extends \Yaf\Route\Simple {}
    // class Yaf_Route_Static extends \Yaf\Route\Route_Static {}
    // class Yaf_Route_Supervar extends \Yaf\Route\Supervar {}
    // class Yaf_View_Simple extends \Yaf\View\Simple {}
    // class Yaf_Application extends \Yaf\Application {}
    // class Yaf_Loader extends \Yaf\Loader {}
    // class Yaf_Registry extends \Yaf\Registry {}
}
