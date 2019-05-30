<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Map;
use Yaf\Route\Regex;
use Yaf\Route\Rewrite;
use Yaf\Route_Static;

/**
 * FR #62702 (Make baseuri case-insensitive)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P0bug62702Test.php
 */
class P0bug62702Test extends Base
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
        // 1. static router
        $router = new Route_Static();

        $request = new Http('/sample', '/sample');
        $router->route($request);
        $this->assertNull($request->getControllerName());

        $request = new Http('/Sample/ABC', '/sample');
        $router->route($request);
        $this->assertSame('ABC', $request->getControllerName());

        // 2. map router
        $router = new Map(true);

        $request = new Http('/sample/A/B/C', '/sample');
        $router->route($request);
        $this->assertSame('A_B_C', $request->getControllerName());

        $request = new Http('/sample', '/sAmplE');
        $router->route($request);
        $this->assertNull($request->getControllerName());

        // 3. regex router
        $router = new Regex('#^/test#', ['controller' => 'info'], []);

        $request = new Http('/test/', '/Test');
        $router->route($request);
        $this->assertNull($request->getControllerName());

        $request = new Http('/sample/test', '/sAmplE');
        $router->route($request);
        $this->assertSame('info', $request->getControllerName());

        // 4. rewrite router
        $router = new Rewrite('/test', ['controller' => 'info'], []);

        $request = new Http('/test/', '/Test');
        $router->route($request);
        $this->assertNull($request->getControllerName());

        $request = new Http('/sample/test', '/sAmplE');
        $router->route($request);
        $this->assertSame('info', $request->getControllerName());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
