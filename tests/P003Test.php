<?php

namespace tests;

use Yaf\Loader;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P003Test.php
 */
class P003Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        ini_set('use_spl_autoload', 0);
        ini_set('lowcase_path', 0);
        ini_set('use_namespace', 0);
        ini_set('ap.lowcase_path', false);
    }

    /**
     * Check for Yaf_Request_Simple
     *
     * @throws
     */
    public function test()
    {
        $loader = Loader::getInstance(dirname(__FILE__), dirname(__FILE__) . '/global');
        $loader->registerLocalNamespace('Baidu');
        $loader->registerLocalNamespace('Sina');
        $loader->registerLocalNamespace(['Wb', 'Inf', null, [], '123']);

        $this->assertEquals('Baidu;Sina;Wb;Inf;123', $loader->getLocalNamespace());

        //---------------------------------------

        $catch = false;
        try {
            $loader->autoload('Baidu_Name');
        } catch (\Exception $e) {
            $this->assertSame(E_WARNING, $e->getCode());
            $this->assertTrue((bool) preg_match('/^Failed opening script [\s\S]+/', $e->getMessage()));
            $catch = true;
        }
        $this->assertTrue($catch);

        //---------------------------------------

        $catch = false;
        try {
            $loader->autoload('Global_Name');
        } catch (\Exception $e) {
            $this->assertSame(E_WARNING, $e->getCode());
            $this->assertTrue((bool) preg_match('/^Failed opening script [\s\S]+/', $e->getMessage()));
            $catch = true;
        }
        $this->assertTrue($catch);
    }

    public function tearDown()
    {
        ini_set('yaf.use_spl_autoload', null);
        ini_set('yaf.lowcase_path', null);
        ini_set('yaf.use_namespace', null);
        ini_set('ap.lowcase_path', null);
    }
}
