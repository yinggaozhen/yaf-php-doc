<?php

namespace tests;

use Yaf\Response\Http;
use Yaf\Response\Cli;
use Yaf\Response_Abstract;

/**
 * @run ./vendor/bin/phpunit --bootstrap bootstrap.php P005Test.php
 */
class P005Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test()
    {
        $response = new Cli();

        $body  = <<<HTML
ifjakdsljfklasdjfkljasdkljfkljadsf
HTML;

        $string = 'laruence';

        $response->appendBody($body);
        $response->prependBody($string);
        $response->appendBody('kfjdaksljfklajdsfkljasdkljfkjasdf');

        $body = $response->getBody();
        $this->assertSame('content', Response_Abstract::DEFAULT_BODY);
        $this->assertSame([
            'content' => 'laruenceifjakdsljfklasdjfkljasdkljfkljadsfkfjdaksljfklajdsfkljasdkljfkjasdf'
        ], $response->getBody(null));
        $this->assertSame(
            'laruenceifjakdsljfklasdjfkljasdkljfkljadsfkfjdaksljfklajdsfkljasdkljfkjasdf',
            $response->getBody(Http::DEFAULT_BODY)
        );
        $this->assertEquals('laruenceifjakdsljfklasdjfkljasdkljfkljadsfkfjdaksljfklajdsfkljasdkljfkjasdf', $response);

        ob_start();
        $response->response();
        $response = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('laruenceifjakdsljfklasdjfkljasdkljfkljadsfkfjdaksljfklajdsfkljasdkljfkjasdf', $response);
    }

    public function tearDown()
    {
    }
}
