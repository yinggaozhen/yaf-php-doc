<?php

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function _initTest()
    {
        \Yaf\Loader::getInstance()->registerLocalNamespace('Test');
        \Yaf\Registry::set('test', new Test079());
    }
}
