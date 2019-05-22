<?php

use tests\Base;
use Yaf\Config\Simple;
use Yaf\Router;

/**
 * Check for Yaf_Router
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P008Test.php
 */
class P008Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        // TODO 依赖于 route_static::_pathinfoRoute
//        $router = new Router();
//
//        $route  = new Simple('m', 'c', 'a');
//        $sroute = new Supervar('r');
//
//        $router->addRoute("simple", $route)->addRoute("super", $sroute);
//        print_r($router);
//        var_dump($router->getCurrentRoute());
//        print_r($router->getRoutes());
//        print_r($router->getRoute("simple"));
//        var_dump($router->getRoute("noexists"));
    }

    public function tearDown()
    {
    }
}
