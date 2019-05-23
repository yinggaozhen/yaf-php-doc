<?php

use tests\Base;
use Yaf\Application;
use Yaf\Loader;

/**
 * Check for Yaf_Loader with single class
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P046Test.php
 */
class P046Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => __DIR__,
                'library' => [
                    'directory' => '/tmp',
                    'namespace' => 'Foo',
                ],
            ],
        ];

        new Application($config);
        $loader = Loader::getInstance();
        $this->assertTrue($loader->isLocalName('Foo_Bar'));
        $this->assertTrue($loader->isLocalName('Foo'));
        $loader->clearLocalNamespace();
        $loader->registerLocalNamespace('Bar');
        $this->assertFalse($loader->isLocalName('Foo_Bar'));
        $this->assertTrue($loader->isLocalName('Bar'));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
