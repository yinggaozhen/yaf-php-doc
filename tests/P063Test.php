<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Rewrite;
use Yaf\Router;

/**
 * Check for Yaf_Route_Rewrite with dynamic mvc
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P063Test.php
 */
class P063Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws TypeError
     */
    public function test()
    {
        $request = new Http('/subdir/ctl/act/name/value');
        $router = new Router();

        $route_1 = new Rewrite('/subdir/:con/:a/*', [
                'module' => 'm',
                'controller' => ':1',
                'action' => ':a',
            ]
        );
        $route_2 = new Rewrite('/yaf/:action/*', [
                'action' => ':action',
                'controller' => 'index',
            ]
        );

        $router
            ->addRoute('subdir', $route_1)
            ->addRoute('yaf', $route_2)
            ->route($request);

        $this->assertSame('subdir', $router->getCurrentRoute());
        $this->assertSame([
            'con' => 'ctl',
            'a' => 'act',
            'name' => 'value'
        ], $request->getParams());
        $this->assertSame('act', $request->getActionName());
        $this->assertNull($request->getControllerName());
        $this->assertSame('m', $request->getModuleName());

        $request = new Http('/yaf/act/name/value');
        $router->route($request);

        $this->assertSame('yaf', $router->getCurrentRoute());
        $this->assertSame([
            'action' => 'act',
            'name' => 'value',
        ], $request->getParams());
        $this->assertSame('act', $request->getActionName());
        $this->assertSame('index', $request->getControllerName());
        $this->assertNull($request->getModuleName());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
