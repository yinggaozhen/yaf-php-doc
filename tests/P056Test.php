<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;

/**
 * check for Yaf_Dispatcher::throwException
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P056Test.php
 */
class P056Test extends Base
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
        Dispatcher::getInstance()->throwException(true);
        $this->assertTrue(Dispatcher::getInstance()->throwException());
        Dispatcher::getInstance()->throwException(false);
        $this->assertFalse(Dispatcher::getInstance()->throwException());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
