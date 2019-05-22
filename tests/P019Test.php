<?php

use tests\Base;
use Yaf\Request\Http;
use Yaf\Router;

/**
 * Yaf_Router::getCurrent with number key
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P019Test.php
 */
class P019Test extends Base
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

        $router->addConfig(
            [
                [
                    'type' => 'regex',
                    'match' => '#^/ap/([^/]*)/*#i',
                    'route' => [
                        [
                            'action' => 'ap',
                        ],
                    ],
                    'map' => [
                        1 => 'version',
                    ]
                ]
            ]
        )->route($request);

        $this->assertSame(0, $router->getCurrentRoute());
        $this->assertSame('1.2', $request->getParam('version'));
    }
}
