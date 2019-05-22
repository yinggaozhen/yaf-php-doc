<?php

use tests\Base;
use Yaf\Loader;

/**
 * Check for Yaf_Loader::set/get(library_path)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P023Test.php
 */
class P023Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        YAF_G('bak_use_spl_autoload', YAF_G('yaf.use_spl_autoload'));
        YAF_G('bak_lowcase_path', YAF_G('yaf.lowcase_path'));

        YAF_G('yaf.use_spl_autoload', 0);
        YAF_G('yaf.lowcase_path', 0);
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test()
    {
        $loader = Loader::getInstance('/foo', '/bar');
        $loader->registerLocalNamespace(['Foo']);

        @$loader->autoload('Foo_Bar');
        $this->assertSame('Failed opening script /foo/Foo/Bar.php', error_get_last()['message']);
        @$loader->autoload('Bar_Foo');
        $this->assertSame('Failed opening script /bar/Bar/Foo.php', error_get_last()['message']);

        $loader->setLibraryPath('/foobar', false);
        @$loader->autoload('Foo_Bar');
        $this->assertSame('Failed opening script /foobar/Foo/Bar.php', error_get_last()['message']);
        @$loader->autoload('Bar_Foo');
        $this->assertSame('Failed opening script /bar/Bar/Foo.php', error_get_last()['message']);

        $loader->setLibraryPath('/foobar', true);
        @$loader->autoload('Foo_Bar');
        $this->assertSame('Failed opening script /foobar/Foo/Bar.php', error_get_last()['message']);
        @$loader->autoload('Bar_Foo');
        $this->assertSame('Failed opening script /foobar/Bar/Foo.php', error_get_last()['message']);

        @$loader->autoload('Bar_Model');
        $this->assertSame("Couldn't load a MVC class unless an Yaf\Application is initialized", error_get_last()['message']);
    }

    public function tearDown()
    {
        parent::tearDown();

        YAF_G('yaf.use_spl_autoload', YAF_G('bak_use_spl_autoload'));
        YAF_G('yaf.lowcase_path', YAF_G('bak_lowcase_path'));
    }
}
