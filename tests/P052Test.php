<?php

use tests\Base;
use Yaf\Request\Http;

/**
 * Check for Yaf_Request APis
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P052Test.php
 */
class P052Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testRequestWithException()
    {
        $exception = false;

        try {
            new Http(new stdClass(), 'xxxx', false);
        } catch (\Error $e) {
            $exception = true;
            $this->assertSame('Yaf\Request\Http::__construct expects at most 2 parameters, 3 given', $e->getMessage());
        }

        $this->assertTrue($exception);
    }

    /**
     * @throws TypeError
     */
    public function testRequestSuccess()
    {
        $request = new Http('xxxxxxxxxxxxxxxxxxxxxxxxxxx');
        $this->assertNull($request->get('xxx'));
        $this->assertNull($request->getQuery('xxx'));
        $this->assertNull($request->getServer('xxx'));
        $this->assertNull($request->getPost('xxx'));
        $this->assertNull($request->getCookie('xxx'));
        $this->assertNull($request->getEnv('xxx'));

        // ------default value-------;

        $this->assertSame('123', $request->get('xxx', '123'));
        $std = new stdClass();
        $this->assertSame($std, $request->getQuery('xxx', $std));
        $this->assertSame([], $request->getServer('xxx', []));
        $this->assertNull($request->getPost('xxx', null));
        $this->assertFalse($request->getCookie('xxx', false));
        $this->assertSame('2.13232', $request->getEnv('xxx', '2.13232'));

        // ------params-------

        $this->assertNull($request->setParam('xxxx'));
        $this->assertNull($request->getParam('xxxx'));
        $this->assertSame([], $request->getParams());

        // ------others-------
        $this->assertFalse($request->isXmlHttpRequest());
        $this->assertTrue($request->isCli());
        $this->assertFalse($request->isPost());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
