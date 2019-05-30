<?php

use tests\Base;
use Yaf\Response\Http;

/**
 * check for Yaf_Response_Http::setHeader() and Yaf_Response_Http::getHeader()
 * and Yaf_Response_Http::setAllHeaders() and Yaf_Response_Http::clearHeaders()
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P072Test.php
 */
class P072Test extends Base
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
        $response = new Http();

        $this->assertTrue($response->setHeader('MyName1', 'Header1'));
        $this->assertTrue($response->setHeader('MyName2', 'Header2'));
        $this->assertTrue($response->setHeader('MyName2', 'Header22'));
        $this->assertTrue($response->setHeader('MyName1', 'Header11', false));
        $this->assertSame([
            'MyName1' => 'Header1, Header11',
            'MyName2' => 'Header22'
        ], \YP\internalPropertyGet($response, '_header'));

        $this->assertTrue($response->setHeader('MyName1', 'Header1'));
        $this->assertTrue($response->setHeader('MyName3', 'Header31', false, 301));
        $this->assertTrue($response->setHeader('MyName3', 'Header32', true, 302));
        $this->assertTrue($response->setHeader('MyName1', 'Header2', false, 302));

        $this->assertSame([
            'MyName1' => 'Header1, Header2',
            'MyName2' => 'Header22',
            'MyName3' => 'Header32'
        ], $response->getHeader());
        $this->assertNull($response->getHeader('MyName'));
        $this->assertSame('Header1, Header2', $response->getHeader('MyName1'));
        $response->clearHeaders();
        $this->assertSame([], \YP\internalPropertyGet($response, '_header'));

        $headers = [
            'MyName1' => 'Header1x',
            'MyName2' => 'Header2x',
            'MyName3' => 12345
        ];
        $this->assertTrue($response->setAllHeaders($headers));
        $this->assertSame([
            'MyName1' => 'Header1x',
            'MyName2' => 'Header2x',
            'MyName3' => '12345'
        ], $response->getHeader());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
