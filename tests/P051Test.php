<?php

use tests\Base;
use Yaf\Route\Map;
use Yaf\Route\Regex;
use Yaf\Route\Rewrite;
use Yaf\Route_Static;
use Yaf\Route\Supervar;

/**
 * Fixed bug that segfault while a abnormal object set to Yaf_Route*::route
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P051Test.php
 */
class P051Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test()
    {
        error_reporting(E_ALL & ~E_WARNING);
        $x = new Map(true);
        $x->route($x);

        $x = new Route_Static();
        $x->route($x);

        $x = new Rewrite('#^/test#', ['controller' => 'info'], []);
        $x->route($x);

        $x = new Supervar('r');
        $x->route($x);

        $x = new Regex('#^/test#', ['controller' => 'info'], []);
        $x->route($x);

        $this->assertSame('okey', 'okey');
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
