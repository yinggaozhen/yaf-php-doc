<?php

use tests\Base;
use Yaf\Config\Ini;

/**
 * Check for Yaf_Config_Ini
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P010Test.php
 */
class P010Test extends Base
{
    public function setUp()
    {
        if (!defined('P010_APPLICATION_PATH')) {
            define('P010_APPLICATION_PATH', 'p010_path');
        }

        parent::setUp();
    }

    /**
     * @dataProvider simpleData
     * @param array $base
     * @param array $extra
     * @param array $product
     * @param array $nocatch
     * @param array $envtest
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function test($base, $extra, $product, $nocatch, $envtest)
    {
        $file = __DIR__ . '/common/configs/p010_simple.ini';

        $config = new Ini($file);
        $configReflection = new \ReflectionProperty($config, '_config');
        $readonlyReflection = new \ReflectionProperty($config, '_readonly');
        $configReflection->setAccessible(true);
        $this->assertSame($base, $configReflection->getValue($config)['base']);
        $this->assertSame($extra, $configReflection->getValue($config)['extra']);
        $this->assertSame($product, $configReflection->getValue($config)['product']);
        $this->assertSame($nocatch, $configReflection->getValue($config)['nocatch']);
        $this->assertSame($envtest, $configReflection->getValue($config)['envtest']);
        $this->assertTrue($readonlyReflection->getValue($config));
    }

    /**
     * @dataProvider simpleData
     * @param array $base
     * @param array $extra
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function testSection($base, $extra)
    {
        $file = __DIR__ . '/common/configs/p010_simple.ini';

        $config = new Ini($file, 'extra');
        $configReflection = new \ReflectionProperty($config, '_config');
        $readonlyReflection = new \ReflectionProperty($config, '_readonly');
        $configReflection->setAccessible(true);
        $this->assertSame($extra, $configReflection->getValue($config));
        $this->assertTrue($readonlyReflection->getValue($config));
    }

    /**
     * @throws \Exception
     */
    public function testReadonly()
    {
        $file = __DIR__ . '/common/configs/p010_simple.ini';

        $config = new Ini($file);
        $readonlyReflection = new \ReflectionProperty($config, '_readonly');
        $readonlyReflection->setAccessible(true);
        @$config->longtime = 23424234324;
        $this->assertSame('Yaf_Config_Ini is readonly', error_get_last()['message']);
        $this->assertTrue($config->readonly());

        ob_start();
        foreach ($config as $key => $value) {
            echo $key;
        }
        $content = ob_get_contents();
        ob_end_clean();
        $this->assertSame('baseextraproductnocatchenvtest', $content);
    }

    /**
     * @throws \Exception
     */
    public function testCount()
    {
        $sick = new Ini();
        $this->assertFalse($sick->__isset(1));
        $this->assertNull($sick->__get(1));
        @$sick->total = 1;
        $this->assertSame('Yaf_Config_Ini is readonly', error_get_last()['message']);
        $this->assertSame(0, count($sick));
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

        // product
        $productData = $extraData;

        // nocatch
        $nocatchData = $extraData;
        $nocatchData['application']['dispatcher']['throwException'] = false;
        $nocatchData['application']['dispatcher']['catchException'] = true;
        $nocatchData['routes']['rewrite']['match'] = '/yaf/:name/:age';

        // envtest
        $envtestData['env'] = '';
        $envtestData['ini'] = '';
        $envtestData['const'] = 'FOO';

        return [
            [
                $baseData,
                $extraData,
                $productData,
                $nocatchData,
                $envtestData
            ]
        ];
    }

    public function tearDown()
    {
    }
}
