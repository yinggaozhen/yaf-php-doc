<?php

use tests\Base;
use Yaf\Config\Simple;

/**
 * Bug #61493 (Can't remove item when using unset() with a Yaf_Config_Simple instance)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P0bug61493Test.php
 */
class P0bug61493Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws TypeError
     */
    public function test()
    {
        $config = new Simple(array(
            'foo' => 'bar',
        ), false);

        unset($config['foo']);
        $this->assertSame([], \YP\internalPropertyGet($config, '_config'));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
