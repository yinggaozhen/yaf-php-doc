<?php

use tests\Base;
use Yaf\Response\Cli;

/**
 * PHP7 Yaf_Response leak memory
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P081Test.php
 */
class P081Test extends Base
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
        $response = new Cli();
        $string = "Jason is a good boy";
        $response->setBody($string);
        $response->setBody($string);
        $this->assertSame('Jason is a good boy', $response->getBody());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
