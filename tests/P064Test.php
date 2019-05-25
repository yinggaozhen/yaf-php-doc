<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Regex;
use Yaf\Router;

/**
 * Check for Yaf_Route_Regex with dynamic mvc
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P064Test.php
 */
class P064Test extends Base
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

        $route_1 = new Regex("#subdir/(?<c>.*?)/(.*?)/.*#",
            [
                'module' => 'm',
                'controller' => ':c',
                'action' => ':a',
            ],
            [
                2 => 'a',
            ]
        );
        $route_2 = new Regex("#yaf/(.*?)/.*#",
            [
                'action' => ':action',
                'controller' => 'index',
            ],
            [
                1 => 'action',
            ]
        );

        $router
            ->addRoute('subdir', $route_1)
            ->addRoute('yaf', $route_2)
            ->route($request);

        $this->assertSame('subdir', $router->getCurrentRoute());
        $this->assertSame([
            'c' => 'ctl',
            'a' => 'act',
        ], $request->getParams());
        $this->assertSame('act', $request->getActionName());
        $this->assertSame('ctl', $request->getControllerName());
        $this->assertSame('m', $request->getModuleName());

        $request = new Http('/yaf/act/name/value');
        $router->route($request);

        $this->assertSame('yaf', $router->getCurrentRoute());
        $this->assertSame([
            'action' => 'act'
        ],$request->getParams());
        $this->assertSame('act', $request->getActionName());
        $this->assertSame('index', $request->getControllerName());
        $this->assertNull($request->getModuleName());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
