<?php

use tests\Base;
use Yaf\Config\Simple;

/**
 * return type in Yaf_Simple_Config::valid() should be boolean
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P071Test.php
 */
class P071Test extends Base
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
        $new = new Simple([]);
        $this->assertFalse($new->valid());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
