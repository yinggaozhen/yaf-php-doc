<?php

use tests\Base;
use Yaf\Config\Ini;
use Yaf\Request\Simple;

/**
 * check for Various segfault
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P054Test.php
 */
class P054Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test()
    {
        $exception = false;
        try {
            $config = new Ini(__DIR__, "test");
        } catch (Exception $e) {
            $exception = true;
            $this->assertTrue(stripos($e->getMessage(), 'Argument is not a valid ini file ') !== false);
        }
        $this->assertTrue($exception);

        $request = new Simple(null);
        $this->assertFalse($request->isOptions());

        $config = new Simple([]);
        $config->key();
        $this->assertSame('okey', 'okey');
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
