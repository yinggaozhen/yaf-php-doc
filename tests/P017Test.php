<?php

use tests\Base;

/**
 * Bug (mem leak and crash in Yaf_Config_Ini)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P017Test.php
 */
class P017Test extends Base
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
        // skip
    }
}
