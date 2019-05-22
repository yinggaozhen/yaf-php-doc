<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Regex;
use Yaf\Router;

/**
 * Check for Yaf_Route_Regex
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P012Test.php
 */
class P012Test extends Base
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
            ->addRoute('subdir', new Regex('#/subdir/(.*)#',
                [
                    'action' => 'version',
                ],
                []
            ))
            ->addRoute('ap', new Regex('#^/ap/([^/]*)/*#i',
                [
                    'action' => 'ap',
                ],
                [
                    1 => 'version',
                ]
            ))->route($request);

        $this->assertSame('ap', $router->getCurrentRoute());
        $this->assertSame('1.2', $request->getParam('version'));
        $this->assertSame('ap', $request->getActionName());
        $this->assertNull($request->getControllerName());
        $this->assertNull($request->getParam('name'));
    }
}
