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
}
