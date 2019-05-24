<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;

/**
 * check for Yaf_Dispatcher::flushInstantly
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P058Test.php
 */
class P058Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        define('P055_APPLICATION_PATH', __DIR__);
        $app = new Application(__DIR__ . '/common/configs/p055_simple.ini');
        Dispatcher::getInstance()->flushInstantly(true);
        $this->assertTrue(Dispatcher::getInstance()->flushInstantly());
        Dispatcher::getInstance()->flushInstantly(false);
        $this->assertFalse(Dispatcher::getInstance()->flushInstantly());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
