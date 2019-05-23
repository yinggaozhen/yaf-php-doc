<?php

use tests\Base;
use Yaf\Application;
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;
use Yaf\Request\Http;
use Yaf\View\Simple;

class ControllerController extends Controller_Abstract
{
    public function actionAction()
    {
    }

    public function indexAction()
    {
        $this->forward('controller', 'dummy');

        /* don't auto-render */
        return false;
    }

    public function dummyAction()
    {
        Dispatcher::getInstance()->enableView();
    }
}

/**
 * Check for controller return false preventing auto-renderring
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P041Test.php
 */
class P041Test extends Base
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
        $config = [
            'application' => [
                'directory' => realpath(__DIR__),
                'dispatcher' => [
                    'catchException' => 0,
                    'throwException' => 1,
                ],
                'modules' => 'module',
            ],
        ];

        $app = new Application($config);
        $request = new Http('/module/controller/action');

        // ============ test 1
        $exception = false;
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (\Exception $e) {
            $exception = true;
            $this->assertTrue(stripos($e->getMessage(), 'Failed opening template') !== false
                && stripos($e->getMessage(), 'modules/Module/views/controller/Action.phtml') !== false);
        }
        $this->assertTrue($exception);

        // ============ test 2
        $exception = false;
        $view = new Simple(__DIR__ . '/no-exists');
        $app->getDispatcher()->setView($view);
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (\Exception $e) {
            $exception = true;
            $this->assertTrue(stripos($e->getMessage(), 'Failed opening template') !== false
                && stripos($e->getMessage(), 'no-exists/controller/Action.phtml') !== false);
        }
        $this->assertTrue($exception);

        // TODO 和实际有点8太一样
        // ============ test 3
        $exception = false;
        $request = new Http('/module/controller/index');
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (\Exception $e) {
            $exception = true;
            $this->assertTrue(stripos($e->getMessage(), 'Failed opening template') !== false
                && stripos($e->getMessage(), 'controller/Dummy.phtml') !== false);
        }

        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
