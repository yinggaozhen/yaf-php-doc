<?php

use tests\Base;
use Yaf\Config\Ini;

/**
 * Bug Yaf_Config_Ini crash due to inaccurate refcount
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P018Test.php
 */
class P018Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @dataProvider simpleData
     *
     * @throws \Exception
     */
    public function test($base)
    {
        $file = $file = __DIR__ . '/common/configs/simple.ini';

        $config = new Ini($file, 'base');
        $configReflection = new \ReflectionProperty($config, '_config');
        $configReflection->setAccessible(true);
        $this->assertSame($base, $configReflection->getValue($config));
    }

    public function simpleData(): array
    {
        $baseData = [
            'application' => [
                'directory' => 'APPLICATION_PATH/applcation'
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

        return [
            [
                $baseData,
            ]
        ];
    }
}
