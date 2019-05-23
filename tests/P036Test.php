<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;
use Yaf\Request\Http;

/**
 * Check for Yaf_Route_Static with arbitrary urls
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P036Test.php
 */
class P036Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $urls = [
            '/', '/foo', '/foo/', '/foo///bar', 'foo/bar', '/foo/bar/',
            '/foo/bar/dummy', '/foo///bar/dummy/', 'foo/bar/dummy/',
            '/my', '/my/', '/my/foo', '/my/foo/', 'my/foo/bar', 'my/foo/bar/',
            '/m/index/index', '/my/foo/bar/dummy/1', 'my/foo/bar/dummy/1/a/2/////',
            '/my/index/index', 'my/index', '/foo/index', 'index/foo',
        ];

        $config = [
            'application' => [
                'directory' => '/tmp/',
                'modules'   => 'Index,My',
            ],
        ];

        $app = new Application($config);
        $route = Dispatcher::getInstance()->getRouter();

        $expect = <<<EXPECT
/ : m=> c=> a=>
/foo : m=> c=>foo a=>
/foo/ : m=> c=>foo a=>
/foo///bar : m=> c=>foo a=>bar
foo/bar : m=> c=>foo a=>bar
/foo/bar/ : m=> c=>foo a=>bar
/foo/bar/dummy : m=> c=>foo a=>bar args=>dummy->,
/foo///bar/dummy/ : m=> c=>foo a=>bar args=>dummy->,
foo/bar/dummy/ : m=> c=>foo a=>bar args=>dummy->,
/my : m=> c=>my a=>
/my/ : m=> c=>my a=>
/my/foo : m=> c=>my a=>foo
/my/foo/ : m=> c=>my a=>foo
my/foo/bar : m=>my c=>foo a=>bar
my/foo/bar/ : m=>my c=>foo a=>bar
/m/index/index : m=> c=>m a=>index args=>index->,
/my/foo/bar/dummy/1 : m=>my c=>foo a=>bar args=>dummy->1,
my/foo/bar/dummy/1/a/2///// : m=>my c=>foo a=>bar args=>dummy->1,a->2,
/my/index/index : m=>my c=>index a=>index
my/index : m=> c=>my a=>index
/foo/index : m=> c=>foo a=>index
index/foo : m=> c=>index a=>foo

EXPECT;

        ob_start();
        foreach ($urls as $url) {
            $req = new Http($url);
            $route->route($req);
            echo $url, ' : ',  'm=>', $req->getModuleName(), ' c=>', $req->getControllerName(), ' a=>',  $req->getActionName();
            if (($args = $req->getParams())) {
                echo ' args=>';
                foreach ($args as $k => $v) {
                    echo $k , '->', $v , ',';
                }
            }
            echo PHP_EOL;
        }
        $actual = ob_get_contents();
        ob_end_clean();

        // $this->assertSame($expect, $actual);

        $expect = <<<EXPECT
/ : m=> c=> a=>
/foo : m=> c=> a=>foo
/foo/ : m=> c=> a=>foo
/my : m=> c=>my a=>
/my/ : m=> c=>my a=>
/my/foo : m=> c=>my a=>foo
/my//foo/ : m=> c=>my a=>foo

EXPECT;

        // ===================== test 2

        YAF_G('yaf.action_prefer', 1);
        $urls = [
            '/', '/foo', '/foo/',
            '/my', '/my/', '/my/foo', '/my//foo/',
        ];

        ob_start();
        foreach ($urls as $url) {
            $req = new Http($url);
            $route->route($req);
            echo $url, ' : ',  'm=>', $req->getModuleName(), ' c=>', $req->getControllerName(), ' a=>',  $req->getActionName();
            if (($args = $req->getParams())) {
                echo ' args=>';
                foreach ($args as $k => $v) {
                    echo $k , '->', $v , ',';
                }
            }
            echo PHP_EOL;
        }
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame($expect, $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
