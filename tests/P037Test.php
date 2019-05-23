<?php

use tests\Base;
use Yaf\Loader;

/**
 * Check for Yaf_Loader and open_basedir
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P037Test.php
 */
class P037Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        @unlink(__DIR__ . '/common/tmp_build/Dummy.php');

        YAF_G('bak_use_spl_autoload', YAF_G('yaf.use_spl_autoload'));
        YAF_G('bak_lowcase_path', YAF_G('yaf.lowcase_path'));
        YAF_G('yaf.lowcase_path', 0);
        YAF_G('yaf.use_spl_autoload', 0);
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $dir = __DIR__;
        $odir = $dir . '/foo';
        file_put_contents($dir . '/common/tmp_build/Dummy.php', '');

        ini_set('open_basedir',  $odir);
        $loader = Loader::getInstance($dir);
        $loader->import($dir . '/Dummy.php');
        $loader->autoload('Dummy');
    }

    public function tearDown()
    {
        parent::setUp();
        @unlink(__DIR__ . '/common/tmp_build/Dummy.php');

        YAF_G('yaf.lowcase_path', YAF_G('bak_lowcase_path'));
        YAF_G('yaf.use_spl_autoload', YAF_G('bak_use_spl_autoload'));
    }
}
