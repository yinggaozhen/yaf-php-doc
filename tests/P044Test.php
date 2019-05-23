<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;

/**
 * Memleaks in Yaf_Dispatcher::getInstance()
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P044Test.php
 */
class P044Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        define('P044_APPLICATION_PATH', __DIR__);
        $app = new Application(__DIR__ . '/common/configs/p044_simple.ini');
        Dispatcher::getInstance();
        $a = Dispatcher::getInstance();
        unset($a);
        Dispatcher::getInstance();
        $b = Dispatcher::getInstance();
        $this->assertSame('Yaf\Dispatcher', get_class($b));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
