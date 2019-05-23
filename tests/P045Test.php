<?php

use tests\Base;
use Yaf\Application;
use Yaf\Bootstrap_Abstract;
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;

class Bootstrap extends Bootstrap_Abstract
{
    /**
     * @param Dispatcher $dispatcher
     * @throws Exception
     */
    protected function _initErrorHandler(Dispatcher $dispatcher)
    {
        $dispatcher->setErrorHandler(function($errorCode, $errorMessage, $file, $line) {
            throw new ErrorException($errorMessage, 0, $errorCode, $file, $line);
        });
    }
}

class IndexController extends Controller_Abstract
{
    public function indexAction()
    {
        echo $undefined_var;
        return false;
    }
}

/**
 * Check for segfault while use closure as error handler
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P045Test.php
 */
class P045Test extends Base
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
                'directory' => realpath(__DIR__),
                'dispatcher' => [
                    'catchException' => 0,
                    'throwException' => 0,
                ],
            ],
        ];

        $exception = false;
        $app = new Application($config);
        try {
            $app->bootstrap()->run();
        } catch (\Exception $e) {
            $exception = true;
            $this->assertSame('Undefined variable: undefined_var', $e->getMessage());
        }
        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
