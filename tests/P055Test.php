<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;

/**
 * check for Yaf_Dispatcher::autoRender
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P055Test.php
 */
class P055Test extends Base
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
        Dispatcher::getInstance()->autoRender(true);
        $this->assertTrue(Dispatcher::getInstance()->autoRender());
        Dispatcher::getInstance()->autoRender(false);
        $this->assertFalse(Dispatcher::getInstance()->autoRender());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
