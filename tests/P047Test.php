<?php

use tests\Base;

/**
 * Check for Yaf_Loader with spl_autoload
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P047Test.php
 */
class P047Test extends Base
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
        $this->markTestSkipped();
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
