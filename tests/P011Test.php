<?php

namespace tests;

use Yaf\Request\Http;
use Yaf\Route\Rewrite;
use Yaf\Router;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P011Test.php
 */
class P011Test extends Base
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
        $request = new Http('/subdir/ap/1.2/name/value', '/subdir');
        $router = new Router();
        $route = new Rewrite(
            '/subdir/:name/:version', [
                'action' => 'version',
            ]
        );

        $router
            ->addRoute('subdir', $route)
            ->addRoute('ap', new Rewrite(
                '/ap/:version/*', [
                    'action' => 'ap',
                    ]
            ))
            ->route($request);

        var_dump($router->getCurrentRoute());
        var_dump($request->getParam('version'));
        var_dump($request->getActionName());
        var_dump($request->getControllerName());
        var_dump($request->getParam('name'));
    }
}
