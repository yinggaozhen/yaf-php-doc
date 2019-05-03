<?php

namespace tests;

use Yaf\Config\Ini;
use Yaf\Route\Regex;
use Yaf\Route\Rewrite;
use Yaf\Route\Route_Static;
use Yaf\Route\Simple;
use Yaf\Route\Supervar;
use Yaf\Router;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P013Test.php
 */
class P013Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider simpleData
     * @throws \Exception
     */
    public function test($extra)
    {
        $config = new Ini(__DIR__ . '/common/configs/simple.ini', 'extra');

        $routes = $config->routes;
        $this->assertInstanceOf(Ini::class, $routes);
        $this->assertSame($extra['routes'], $routes->_config);

        $router = new Router();
        $router->addConfig($routes);

        // 1. test default route
        $default = $router->getRoutes()['_default'];
        $this->assertInstanceOf(Route_Static::class, $default);

        // 2. test regex route
        $regex = $router->getRoutes()['regex'];
        $this->assertInstanceOf(Regex::class, $regex);
        $this->assertSame('^/ap/(.*)', $this->proxyGetProperty($regex, '_route'));
        $this->assertSame([
            'controller' => 'Index',
            'action' => 'action'
        ], $this->proxyGetProperty($regex, '_default'));
        $this->assertSame(['name', 'name', 'value'], $this->proxyGetProperty($regex, '_maps'));
        $this->assertSame([
            'controller' => 'Index',
            'action' => 'action'
        ], $this->proxyGetProperty($regex, '_verify'));
        $this->assertNull($this->proxyGetProperty($regex, '_reverse'));

        // 3. test simple route
        $simple = $router->getRoutes()['simple'];
        $this->assertInstanceOf(Simple::class, $simple);
        $this->assertSame('c', $this->proxyGetProperty($simple, 'controller'));
        $this->assertSame('m', $this->proxyGetProperty($simple, 'module'));
        $this->assertSame('a', $this->proxyGetProperty($simple, 'action'));

        // 4. test supervar route
        $supervar = $router->getRoutes()['supervar'];
        $this->assertInstanceOf(Supervar::class, $supervar);
        $this->assertSame('c', $this->proxyGetProperty($supervar, '_var_name'));

        // 5. test rewrite route
        $rewrite = $router->getRoutes()['rewrite'];
        $this->assertInstanceOf(Rewrite::class, $rewrite);
        $this->assertSame('/yaf/:name/:value', $this->proxyGetProperty($rewrite, '_route'));
        $this->assertSame([
            'controller' => 'Index',
            'action' => 'action'
        ], $this->proxyGetProperty($rewrite, '_default'));
        $this->assertSame([
            'controller' => 'Index',
            'action' => 'action'
        ], $this->proxyGetProperty($rewrite, '_verify'));
    }

    public function simpleData(): array
    {
        $baseData = [
            'application' => [
                'directory' => 'p010_path/applcation'
            ],
            'name' => 'base',
            'array' => [
                1 => 1,
                'name' => 'name'
            ],
            '5' => 6,
            'routes' => [
                'regex' => [
                    'type' => 'regex',
                    'match' => '^/ap/(.*)',
                    'route' => [
                        'controller' => 'Index',
                        'action' => 'action'
                    ],
                    'map' => ['name', 'name', 'value']
                ],
                'simple' => [
                    'type' => 'simple',
                    'controller' => 'c',
                    'module' => 'm',
                    'action' => 'a',
                ],
                'supervar' => [
                    'type' => 'supervar',
                    'varname' => 'c'
                ],
                'rewrite' => [
                    'type' => 'rewrite',
                    'match' => '/yaf/:name/:value',
                    'route' => [
                        'controller' => 'Index',
                        'action' => 'action'
                    ]
                ]
            ],

        ];

        // extra
        $extraData = $baseData;
        $extraData['value'] = '2';
        $extraData['name'] = 'extra';
        $extraData['array']['name'] = 'new_name';
        $extraData['array'][2] = 'test';

        return [
            [
                $extraData
            ]
        ];
    }
}
