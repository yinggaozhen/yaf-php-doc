<?php

namespace tests;

use Yaf\Exception;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P015Test.php
 */
class P015Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test()
    {
        $previous = new Exception("Previous", 100);
        $exception = new Exception("Exception", 200, $previous);

        $this->assertTrue($previous === $exception->getPrevious());
        $this->assertSame('Exception', $exception->getMessage());
        $this->assertSame(100, $exception->getPrevious()->getCode());
    }
}
