<?php

use tests\Base;
use Yaf\Route\Rewrite;
use Yaf\Router;

/**
 * ISSUE #134 (Segfault while calling assemble)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P0issue134Test.php
 */
class P0issue134Test extends Base
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
        $route  = new Rewrite('/detail/:id', [
                'controller' => 'index',
                'action' => 'detail',
                'module' => 'kfc'
            ]
        );
        $router->addRoute('kfc/index/detail', $route);
        $this->assertSame('/detail/1', $router->getRoute('kfc/index/detail')->assemble([
                ':id' => '1',
            ]
        ));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
