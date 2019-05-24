<?php

function my_autoload($class)
{
    eval("class $class {}");
    return true;
}

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    public function _initAutoload(\Yaf\Dispatcher $dispatcher)
    {
        spl_autoload_register('my_autoload');
    }

    public static function errorHandler($error, $errstr)
    {
        var_dump($errstr);
    }

    /**
     * @param \Yaf\Dispatcher $dispatcher
     * @throws Exception
     */
    public function _initErrorHandler(\Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->setErrorHandler(['Bootstrap', 'errorHandler']);
    }
}
