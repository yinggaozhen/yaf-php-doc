<?php

namespace tests;

use Yaf\Request\Simple;

class P002Test extends Base
{
    public function setUp()
    {
        ini_set('yaf.use_namespace', 0);
    }

    /**
     * Check for Yaf_Request_Simple
     */
    public function test()
    {
        $request = new Simple('CLI', 'index', 'dummy', null, []);
        $this->assertSame('index', $request->module);
    }

    public function tearDown()
    {
        ini_set('yaf.use_namespace', null);
    }
}
