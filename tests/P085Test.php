<?php

use tests\Base;
use Yaf\Request\Http;

/**
 * Check Yaf_Request_Http::getRaw
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P085Test.php
 */
class P085Test extends Base
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
        $request = new Http('/');
        $request->getRaw();

        $this->markTestSkipped();
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
