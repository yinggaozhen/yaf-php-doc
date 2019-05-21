<?php

use tests\Base;
use Yaf\Application;
use PHPUnit\Framework\TestCase;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P021Test.php
 */
class P021Test extends Base
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
        define('APPLICATION_PATH', dirname(__FILE__));
        $app = new Application(__DIR__ . '/common/configs/simple.ini', 'nocatch');

        $GLOBALS['P021Test'] = 0;
        function error_handler(...$errInfo) {
            echo PHP_EOL . 'P021Test begin. error occurrd instead of exception threw' . PHP_EOL;

            ++$GLOBALS['P021Test'];
            TestCase::assertSame(1, $GLOBALS['P021Test']);
        }

        $app->getDispatcher()->setErrorHandler('error_handler', E_ALL);
        $app->run();
    }

    public function tearDown()
    {
        parent::tearDown();
        define('APPLICATION_PATH', null);
    }
}
