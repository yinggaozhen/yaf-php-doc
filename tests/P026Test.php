<?php

use tests\Base;
use Yaf\Response\Yaf_Response_Http;
use function YP\internalPropertyGet;

/**
 * Check for Yaf_Response::setBody/prependBody/appendBody
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P026Test.php
 */
class P026Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws ReflectionException
     */
    public function test()
    {
        $response = new Yaf_Response_Http();
        $response
            ->setBody('ell')
            ->appendBody('o')
            ->setBody(' W', 'footer')
            ->prependBody('H')
            ->appendBody('orld', 'footer');


        $this->assertSame('Hello', $response->getBody('content'));
        $this->assertSame(' World', $response->getBody('footer'));
        $this->assertTrue((bool) internalPropertyGet($response, '_sendheader'));
        $this->assertFalse((bool) internalPropertyGet($response, '_response_code'));
        $this->assertSame('Hello World', (string) $response);
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
