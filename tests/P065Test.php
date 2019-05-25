<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Route\Regex;
use Yaf\Router;

/**
 * Yaf_Route_Regex map is optional
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P065Test.php
 */
class P065Test extends Base
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
        $request = new Http('/subdir/ap/1.2/name/value', '/subdir');

        $router = new Router();
        $router->addConfig(
            [
                [
                    'type' => 'regex',
                    'match' => "#^/ap/([^/]*)/*#i",
                    'route' => [
                        [
                            'action' => 'ap',
                        ],
                    ],
                ]
            ]
        )->route($request);

        $this->assertSame(0, $router->getCurrentRoute());
        $this->assertNull($request->getActionName());

        $router->addRoute('regex', new Regex("#^/ap/([^/]*)/*#i", ['action' => 'ap']))->route($request);

        $this->assertSame('regex', $router->getCurrentRoute());
        $this->assertSame('ap', $request->getActionName());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
