<?php

use tests\Base;
use Yaf\Application;
use Yaf\Loader;
use Yaf\Yaf_Loader;

/**
 * Check for Yaf_Loader::getInstace() paramters
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P024Test.php
 */
class P024Test extends Base
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
        $loader = Loader::getInstance('/foo', '/bar');
        $this->assertSame('/foo', $loader->getLibraryPath());
        $this->assertSame('/bar', $loader->getLibraryPath(true));

        $config = [
            'application' => [
                'directory' => realpath(dirname(__FILE__)),
            ],
        ];

        $app = new Application($config);
        $this->assertRegExp('/^[\w\/-]+library$/', $loader->getLibraryPath());
        $this->assertSame('/php/global/dir', $loader->getLibraryPath(true));
    }

    public function tearDown()
    {
        parent::tearDown();

        YAF_G('yaf.library', YAF_G('bak_library'));
    }
}
