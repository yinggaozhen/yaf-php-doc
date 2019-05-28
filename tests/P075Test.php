<?php

use tests\Base;
use Yaf\Route\Regex;
use Yaf\Router;

/**
 * Check for Yaf_Route_Regex::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P075Test.php
 */
class P075Test extends Base
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

        $route  = new Regex(
            "#^/product/([^/]+)/([^/])+#",
            array(
                'controller' => "product",  //route to product controller,
            ),
            array(),
            array(),
            '/:m/:c/:a'
        );

        $router->addRoute("regex", $route);

        $this->assertSame('/module/controller/action?tkey1=tval1&tkey2=tval2',
            $router->getRoute('regex')->assemble([
                ':m' => 'module',
                ':c' => 'controller',
                ':a' => 'action'
            ],
            [
                'tkey1' => 'tval1',
                'tkey2' =>
                    'tval2'
            ]
        )
        );
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
