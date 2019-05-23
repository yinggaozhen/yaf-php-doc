<?php

use tests\Base;
use Yaf\Application;
use Yaf\Controller_Abstract;
use Yaf\Dispatcher;
use Yaf\Exception;
use Yaf\Request\Http;

class ControllerController extends Controller_Abstract
{
    public function actionAction()
    {
    }

    public function indexAction()
    {
        Dispatcher::getInstance()->disableView();
        $this->forward('dummy');
    }

    public function dummyAction()
    {
        Dispatcher::getInstance()->enableView();
    }
}

/**
 * TODO PLZ FIX ME
 * Check for Yaf_View_Simple with predefined template dir
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P033Test.php
 */
class P033Test extends Base
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
                    'throwException' => 1,
                ],
                'modules' => 'module',
            ],
        ];

        $app = new Application($config);
        $request = new Http('/module/controller/action');

        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (Exception $e) {
            echo $e->getMessage(), '\n';
        }

        $view = new Simple(dirname(__FILE__) . 'no-exists');
        $app->getDispatcher()->setView($view);
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (Exception $e) {
            echo $e->getMessage(), '\n';
        }

        $request = new Http('/module/controller/index');
        try {
            $app->getDispatcher()->returnResponse(false)->dispatch($request);
        } catch (Exception $e) {
            echo $e->getMessage(), '\n';
        }
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
