<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Regex;
use Yaf\Router;

/**
 * Check for Yaf_Route_Regex with abnormal map
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P066Test.php
 */
class P066Test extends Base
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
        $request = new Http('/subdir/ap/1.2/xxx/name/value', '/subdir');
        $router = new Router();
        $router->addRoute('ap', new Regex("#^/ap/([^/]*)/([^/]*)/*#i", [
            'action' => 'ap',
        ], [
                1 => 23432,
                2 => null,
            ]
        ))->route($request);

        $this->assertSame('ap', $router->getCurrentRoute());
        $this->assertNull($request->getParam(1));
        $this->assertSame('ap', $request->getActionName());
        $this->assertNull($request->getControllerName());
        $this->assertNull($request->getParam('name'));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
