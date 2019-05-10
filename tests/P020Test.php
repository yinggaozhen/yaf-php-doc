<?php

use tests\Base;
use Yaf\Application;

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

        function error_handler($errno, $errstr, $errfile, $errline) {
            var_dump(Application::app()->getLastErrorNo());
            var_dump(Application::app()->getLastErrorMsg());
            Application::app()->clearLastError();
            var_dump(Application::app()->getLastErrorNo());
            var_dump(Application::app()->getLastErrorMsg());
        };

        $app = new Application($config);
        $app->getDispatcher()->setErrorHandler('error_handler', E_RECOVERABLE_ERROR);
        $app->run();
    }
}
