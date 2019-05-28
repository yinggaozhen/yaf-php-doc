<?php

use tests\Base;
use Yaf\Route\Map;
use Yaf\Router;

/**
 * Check for Yaf_Route_Map::assemble
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P078Test.php
 */
class P078Test extends Base
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
        $route = new Map();

        $router->addRoute('map', $route);

        $this->assertSame('/foo/bar?tkey1=tval1&tkey2=tval2', $router->getRoute('map')->assemble(
            array(
                ':c' => 'foo_bar'
            ),
            array(
                'tkey1' => 'tval1',
                'tkey2' => 'tval2'
            )
        ));

        $route = new Map(true, '_');
        $router->addRoute('map', $route);

        $this->assertSame('/foo/bar/_/tkey1/tval1/tkey2/tval2', $router->getRoute('map')->assemble(
            array(
                ':a' => 'foo_bar'
            ),
            array(
                'tkey1' => 'tval1',
                'tkey2' => 'tval2'
            )
        ));
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
