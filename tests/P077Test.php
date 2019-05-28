<?php

use tests\Base;
use Yaf\Route_Static;
use Yaf\Router;

/**
 * Check for Yaf_Route_Static::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P077Test.php
 */
class P077Test extends Base
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
        $route = new Route_Static();

        $router->addRoute('static', $route);

        $this->assertSame('/yafmodule/yafcontroller/yafaction?tkey1=tval1&tkey2=tval2', $router->getRoute('static')->assemble(
            array(
                ':a' => 'yafaction',
                'tkey' => 'tval',
                ':c' => 'yafcontroller',
                ':m' => 'yafmodule'
            ),
            array(
                'tkey1' => 'tval1',
                'tkey2' => 'tval2'
            )
        )
        );

        $this->assertSame('/yafmodule/yafcontroller/yafaction', $router->getRoute('static')->assemble(
            array(
                ':a' => 'yafaction',
                'tkey' => 'tval',
                ':c' => 'yafcontroller',
                ':m' => 'yafmodule'
            ),
            array(
                1 => 2,
                array(),
            )
        ));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
