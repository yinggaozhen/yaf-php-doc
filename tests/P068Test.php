<?php

use tests\Base;
use Yaf\Config\Ini;

/**
 * Check for multi inheritance of section
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P068Test.php
 */
class P068Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        // TODO
        $this->markTestSkipped('multi ini parse incomplete');
    }

    /**
     * @throws Exception
     * @throws \Yaf\Exception\TypeError
     */
    public function test()
    {
        $config = new Ini(__DIR__ . '/common/multi-section.ini');
        print_r($config->toArray());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
