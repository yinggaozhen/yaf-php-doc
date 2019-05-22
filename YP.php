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
    function internalCall($object, string $method, $params = [])
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
    function internalPropertyGet($object, string $property)
    {
        $reflectionProperty = new \ReflectionProperty($object, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
