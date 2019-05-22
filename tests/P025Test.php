<?php

use tests\Base;
use Yaf\Application;
use Yaf\Loader;

/**
 * Check for Yaf_Loader with namespace configuration
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P025Test.php
 */
class P025Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        YAF_G('bak_library', YAF_G('yaf.library'));
        YAF_G('yaf.library', '/php/global/dir');
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => realpath(dirname(__FILE__)),
                'library' => [
                    'directory' => '/tmp',
                    'namespace' => 'Foo, Bar',
                ],
            ],
        ];

        $app = new Application($config);
        Loader::getInstance()->registerLocalNamespace('Dummy');
        $loader = Loader::getInstance();
        $this->assertSame('/tmp', $loader->getLibraryPath());
        $this->assertSame('/php/global/dir', $loader->getLibraryPath(true));
        $this->assertTrue(Loader::getInstance()->isLocalName('Bar_Name'));
    }

    public function tearDown()
    {
        parent::tearDown();

        YAF_G('yaf.library', YAF_G('bak_library'));
    }
}
