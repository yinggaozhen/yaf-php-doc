<?php

use tests\Base;
use Yaf\Application;
use Yaf\Controller_Abstract;
use Yaf\Exception;
use Yaf\Request\Http;

class ControllerController extends Controller_Abstract
{
    public function init()
    {
        throw new Exception('exception');
    }

    public function indexAction()
    {
        echo 'okey';
        return false;
    }
}

/**
 * Check for throw exception in Yaf_Controller::init
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P042Test.php
 */
class P042Test extends Base
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
                    'throwException' => 1,
                ],
                'modules' => 'module',
            ],
        ];


        $app = new Application($config);
        $request = new Http('/module/controller/index');

        $exception = false;
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (\Exception $e) {
            $exception = true;
            $this->assertSame('exception', $e->getMessage());
        }
        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
