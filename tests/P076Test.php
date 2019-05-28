<?php

use tests\Base;
use Yaf\Route\Supervar;
use Yaf\Router;

/**
 * Check for Yaf_Route_Supervar::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P076Test.php
 */
class P076Test extends Base
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
        $route  = new Supervar('r');

        $router->addRoute('supervar', $route);

        $this->assertSame('?r=/yafmodule/yafcontroller/yafaction&tkey1=tval1&tkey2=tval2', $router->getRoute('supervar')->assemble(
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
        ));

        $expect = false;
        try {
            $router->getRoute('supervar')->assemble(
                array(
                    ':a' => 'yafaction',
                    'tkey' => 'tval',
                    ':m' => 'yafmodule'
                ),
                array(
                    'tkey1' => 'tval1',
                    'tkey2' => 'tval2',
                    1 => array(),
                )
            );
        } catch (Exception $e) {
            $expect = true;
            $this->assertSame("You need to specify the controller by ':c'", $e->getMessage());
        }
        $this->assertTrue($expect);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
