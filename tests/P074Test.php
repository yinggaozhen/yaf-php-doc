<?php

use tests\Base;
use Yaf\Route\Simple;
use Yaf\Router;

/**
 * Check for Yaf_Route_Simple::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P074Test.php
 */
class P074Test extends Base
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
        $route  = new Simple('m', 'c', 'a');

        $router->addRoute('simple', $route);

        $this->assertSame('?m=yafmodule&c=yafcontroller&a=yafaction&tkey1=tval1&tkey2=tval2', $router->getRoute('simple')->assemble([
                ':a' => 'yafaction',
                'tkey' => 'tval',
                ':c' => 'yafcontroller',
                ':m' => 'yafmodule'
            ],
            [
                'tkey1' => 'tval1',
                'tkey2' => 'tval2'
            ]
        ));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
