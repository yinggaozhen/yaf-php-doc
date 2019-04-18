<?php

namespace tests;

use Yaf\Application;
use Yaf\Exception\LoadFailed\Controller;
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
        $this->assertSame('dummy', $request->controller);
        $this->assertSame('index', $request->action);
        $this->assertSame('CLI', $request->method);
        $this->assertTrue((bool) $request->setParam('name', 'Laruence'));
        $this->assertTrue($request->isCli());
        $this->assertFalse($request->isXmlHttpRequest());
        $this->assertFalse($request->isPost());
        $this->assertSame('Laruence', $request->getParam('name'));
        $this->assertNull($request->getParam('notexists'));

        $catch = false;
        try {
            $app = new Application([
                'application' => ['directory' => dirname(__FILE__)],
            ]);
            $app->getDispatcher()->dispatch($request);
        } catch (Controller | \Exception $e) {
            $catch = true;
            $this->assertSame('Failed opening controller script %scontrollers%cDummy.php: No such file or directory', $e->getMessage());
        }
        $this->assertTrue($catch);
    }

    public function tearDown()
    {
        ini_set('yaf.use_namespace', null);
    }
}
