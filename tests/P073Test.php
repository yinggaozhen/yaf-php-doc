<?php

use tests\Base;
use Yaf\Route\Rewrite;
use Yaf\Router;

/**
 * Check for Yaf_Route_Rewrite::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P073Test.php
 */
class P073Test extends Base
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
        $router = new Router();
        $route = new Rewrite('/product/:name/:id/*', [
            'controller' => 'product',
        ], []);
        $router->addRoute('rewrite', $route);

        $this->assertSame('/product/foo/bar/tmpkey1/tmpval1/?tkey1=tval1&tkey2=tval2', $router->getRoute('rewrite')->assemble(
            [
                ':name' => 'foo',
                ':id' => 'bar',
                ':tmpkey1' => 'tmpval1'
            ],
            [
                'tkey1' => 'tval1',
                'tkey2' => 'tval2'
            ]
        ));

        $this->assertSame('/product/foo/1/tmpkey1/tmpval1/?tkey1=tval1&tkey2=22222', $router->getRoute('rewrite')->assemble(
            [
                ':name' => 'foo',
                ':id' =>  1,
                ':tmpkey1' => 'tmpval1'
            ],
            [
                'tkey1' => 'tval1',
                'tkey2' => 22222
            ]
        ));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
