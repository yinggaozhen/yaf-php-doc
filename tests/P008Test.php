<?php

use tests\Base;
use Yaf\Route\Simple;
use Yaf\Route\Supervar;
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
        $router = new Router();

        $route  = new Simple('m', 'c', 'a');
        $sroute = new Supervar('r');

        $router->addRoute('simple', $route)->addRoute('super', $sroute);

        ob_start();
        print_r($router);
        $content = ob_get_contents();
        ob_end_clean();

        $expect = <<<EXPECT
Yaf\Router Object
(
    [_routes:protected] => Array
        (
            [_default] => Yaf\Route_Static Object
                (
                )

            [simple] => Yaf\Route\Simple Object
                (
                    [controller:protected] => c
                    [module:protected] => m
                    [action:protected] => a
                )

            [super] => Yaf\Route\Supervar Object
                (
                    [_var_name:protected] => r
                )

        )

    [_current:protected] => 
)

EXPECT;
        $this->assertSame($content, $expect);

        // =========== test 2

        $this->assertNull($router->getCurrentRoute());

        // =========== test 3

        ob_start();
        print_r($router->getRoutes());
        $content = ob_get_contents();
        ob_end_clean();

        $expect = <<<EXPECT
Array
(
    [_default] => Yaf\Route_Static Object
        (
        )

    [simple] => Yaf\Route\Simple Object
        (
            [controller:protected] => c
            [module:protected] => m
            [action:protected] => a
        )

    [super] => Yaf\Route\Supervar Object
        (
            [_var_name:protected] => r
        )

)

EXPECT;

        $this->assertSame($content, $expect);

        // =========== test 4

        /** @var Simple $route */

        ob_start();
        print_r($router->getRoute('simple'));
        $content = ob_get_contents();
        ob_end_clean();

        $expect = <<<EXPECT
Yaf\Route\Simple Object
(
    [controller:protected] => c
    [module:protected] => m
    [action:protected] => a
)

EXPECT;

        $this->assertSame($content, $expect);

        // =========== test 5
        $this->assertNull($router->getRoute('noexists'));
    }

    public function tearDown()
    {
    }
}
