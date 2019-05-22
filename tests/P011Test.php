<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Rewrite;
use Yaf\Router;

/**
 * Check for Yaf_Route_Rewrite
 *
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
        $router
            ->addRoute('subdir', new Rewrite('/subdir/:name/:version', [
                    'action' => 'version',
                ]
            ))
            ->addRoute('ap', new Rewrite('/ap/:version/*', [
                    'action' => 'ap',
                ]
            ))
            ->route($request);

        $this->assertSame('ap', $router->getCurrentRoute());
        $this->assertSame('1.2', $request->getParam('version'));
        $this->assertSame('ap', $request->getActionName());
        $this->assertNull($request->getControllerName());
        $this->assertSame('value', $request->getParam('name'));
    }
}
