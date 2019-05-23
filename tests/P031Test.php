<?php

use tests\Base;
use Yaf\Application;
use function YP\internalPropertyGet;

/**
 * Check for application.dispatcher.defaultRoute
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P031Test.php
 */
class P031Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => realpath(__DIR__),
                'dispatcher' => [
                    'defaultRoute' => [
                        'type' => 'map',
                        'delimiter' => '##',
                        'controllerPrefer' => 1,
                    ],
                ],
            ],
        ];

        $app = new Application($config);
        $routes = $app->getDispatcher()->getRouter()->getRoutes()['_default'];

        $this->assertTrue(internalPropertyGet($routes, '_ctl_router'));
        $this->assertSame('##', internalPropertyGet($routes, '_delimiter'));
    }
}
