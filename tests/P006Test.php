<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Route_Static;

/**
 * Check for Yaf_Route_Static
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P006Test.php
 */
class P006Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test()
    {
        $request_uri = '/prefix/controller/action/name/laruence/age/28';
        $base_uri	 = '/prefix/';

        $request = new Http($request_uri, $base_uri);
        $route = new Route_Static();

        $this->assertTrue($route->route($request));
        $this->assertNull($request->module);
        $this->assertSame('controller', $request->controller);
        $this->assertSame('action', $request->action);
        $this->assertSame('Cli', $request->method);
        $this->assertSame([
            'name' => 'laruence',
            'age' => 28
        ], $request->getParams());
        $this->assertSame('/prefix/', $request->getBaseUri());
        $this->assertSame('/prefix/controller/action/name/laruence/age/28', $request->getRequestUri());
    }

    public function tearDown()
    {
    }
}
