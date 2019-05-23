<?php

use tests\Base;
use Yaf\Config\Ini;
use function YP\internalPropertyGet;

/**
 * Check for Yaf_Config_Ini with env
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P032Test.php
 */
class P032Test extends Base
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
        putenv('P032FOO=bar');
        define('P032FOO', 'Dummy');

        $config = new Ini(__DIR__ . '/common/configs/p032_simple.ini', 'envtest');

        $this->assertSame([
            'env' => 'bar',
            'ini' => '',
            'const' => 'Dummy'
        ], internalPropertyGet($config, '_config'));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
