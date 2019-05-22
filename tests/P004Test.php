<?php

use tests\Base;
use Yaf\Yaf_Registry;

/**
 * Check for Yaf_Registry
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P004Test.php
 */
class P004Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    public function test()
    {
        $str = 'Ageli Platform';
        Yaf_Registry::set('name', $str);

        $this->assertSame('Ageli Platform', Yaf_Registry::get('name'));
        $this->assertTrue(Yaf_Registry::has('name'));

        //---------------------------------------

        $name = 'name';
        Yaf_Registry::del($name);

        $this->assertNull(Yaf_Registry::get($name));
        $this->assertFalse(Yaf_Registry::has($name));
    }

    public function tearDown()
    {
    }
}
