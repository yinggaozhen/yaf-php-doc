<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;

/**
 * check for Yaf_Dispatcher::catchException
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P057Test.php
 */
class P057Test extends Base
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
        Dispatcher::getInstance()->catchException(true);
        $this->assertTrue(Dispatcher::getInstance()->catchException());
        Dispatcher::getInstance()->catchException(false);
        $this->assertFalse(Dispatcher::getInstance()->catchException());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
