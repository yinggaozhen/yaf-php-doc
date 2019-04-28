<?php

namespace tests;

use Yaf\Config\Ini;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P010Test.php
 */
class P010Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        $file = __DIR__  . '/common/configs/simple.ini';

        $config = new Ini($file);
        // TODO 添加对比
    }

    public function tearDown()
    {
    }
}
