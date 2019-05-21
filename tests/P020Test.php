<?php

use tests\Base;
use Yaf\Application;
use PHPUnit\Framework\TestCase;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P020Test.php
 */
class P020Test extends Base
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
        $config = [
            'application' => [
                'directory' => realpath(dirname(__FILE__)),
                'dispatcher' => [
                    'catchException' => 0,
                    'throwException' => 0,
                ],
            ],
        ];

        function error_handler(...$errInfo) {
            echo PHP_EOL . 'P020Test begin' . PHP_EOL;

            $GLOBALS['P020Test']++;
            TestCase::assertSame(516, Application::app()->getLastErrorNo());
            TestCase::assertTrue(
                strncmp(
                    'Failed opening controller script %s: %s',
                    Application::app()->getLastErrorMsg(),
                    strlen('Failed opening controller script ')
                ) === 0
            );
            Application::app()->clearLastError();
            TestCase::assertSame(0, Application::app()->getLastErrorNo());
            TestCase::assertSame("", Application::app()->getLastErrorMsg());
            TestCase::assertSame(1, $GLOBALS['P020Test']);
        };

        $app = new Application($config);
        // $app->getDispatcher()->setErrorHandler('error_handler', E_RECOVERABLE_ERROR);
        $app->getDispatcher()->setErrorHandler('error_handler', E_ALL);
        $app->run();
    }
}
