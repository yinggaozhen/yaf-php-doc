<?php

use tests\Base;
use Yaf\Application;
use Yaf\Exception\LoadFailed\Controller;
use Yaf\Request\Simple;

/**
 * Check for Yaf_Request_Simple
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P002Test.php
 */
class P002Test extends Base
{
    public function setUp()
    {
        parent::setUp();
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

        //---------------------------------------

        $catch = false;
        try {
            $app = new Application([
                'application' => ['directory' => dirname(__FILE__)],
            ]);
            $app->getDispatcher()->dispatch($request);
        } catch (Controller | \Exception $e) {
            $catch = true;
            $this->assertTrue(
                strncmp(
                    'Failed opening controller script %scontrollers%cDummy.php: No such file or directory',
                    $e->getMessage(),
                    strlen('Failed opening controller script ')
                ) === 0
            );
        }
        $this->assertTrue($catch);

        $this->assertNull($request->get('xxx'));
        $this->assertNull($request->getQuery('xxx'));
        $this->assertNull($request->getServer('xxx'));
        $this->assertNull($request->getPost('xxx'));
        //$this->assertNull($request->getCookie('xxx'));
        $this->assertNull($request->getEnv('xxx'));

        // test 1 ------default value-------
        $this->assertSame('123', $request->get('xxx', '123'));
        $default = new \stdClass();
        $this->assertSame($default, $request->getQuery('xxx', $default));
        $this->assertSame([], $request->getServer('xxx', []));
        $this->assertNull($request->getPost('xxx', null));
        $this->assertFalse($request->getCookie('xxx', false));
        $this->assertSame('2.13232', $request->getEnv('xxx', '2.13232'));

        // test 2 ------params-------
        $this->assertNull($request->setParam("xxxx"));
        $this->assertNull($request->getParam("xxxx"));
        $this->assertSame([
            'name' => 'Laruence'
        ], $request->getParams());
    }

    public function tearDown()
    {
        ini_set('yaf.use_namespace', null);
    }
}
