<?php

namespace tests;

use Yaf\Registry;

/**
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
        Registry::set('name', $str);

        $this->assertSame('Ageli Platform', Registry::get('name'));
        $this->assertTrue(Registry::has('name'));

        //---------------------------------------

        $name = 'name';
        Registry::del($name);

        $this->assertNull(Registry::get($name));
        $this->assertFalse(Registry::has($name));
    }

    public function tearDown()
    {
    }
}
