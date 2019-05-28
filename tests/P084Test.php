<?php

use SebastianBergmann\CodeCoverage\TestCase;
use tests\Base;
use Yaf\Request\Simple;

/**
 * Check request methods
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P084Test.php
 */
class P084Test extends Base
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
        function checkMethod($method) {
            $request = new Simple($method, 'index', 'dummy', null, []);
            $method = 'is' . ucfirst(strtolower($method));

            TestCase::assertTrue($request->$method());
            TestCase::assertSame(1, $request->isGet() + $request->isPost() + $request->isHead()
                + $request->isDelete() + $request->isPut() + $request->isOptions()
                + $request->isPatch());
        }

        checkMethod('GET');
        checkMethod('POST');
        checkMethod('HEAD');
        checkMethod('DELETE');
        checkMethod('PUT');
        checkMethod('OPTIONS');
        checkMethod('PATCH');
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
