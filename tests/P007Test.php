<?php

namespace tests;

use Yaf\Config\Simple;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P007Test.php
 */
class P006Test extends Base
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
        $config = [
            'section1' => [
                'name' => 'value',
                'dummy' =>  'foo',
            ],
            'section2' => "laruence",
        ];

        $config1 = new Simple($config, 'section2');
        $this->assertSame([
            'section1' => [
                'name' => 'value',
                'dummy' =>  'foo',
            ],
            'section2' => "laruence",
        ], $config1->_config);

        //---------------------------------------

        $config2 = new Simple($config, 'section1');
        $this->assertTrue($config2->readonly());
        $config2->new = 'value';
        $this->assertFalse(isset($config->new));

        //---------------------------------------

        $config3 = new Simple($config);
        unset($config);
        $this->assertTrue(isset($config3['section2']));
        $config3->new = "value";
        $this->assertFalse($config3->readonly());

        ob_start();
        foreach ($config3 as $key => $val) {
            print_r($key);
            print_r("=>");
            print_r($val);
            print_r(PHP_EOL);
        }
        $buf = ob_get_contents();
        ob_end_clean();
        $output = <<<OUTPUT
section1=>Yaf\Config\Simple Object
(
    [_readonly] => 
    [_config] => Array
        (
            [name] => value
            [dummy] => foo
        )

)

section2=>laruence
new=>value

OUTPUT;
        $this->assertSame($output, $buf);

        $this->assertSame([
            'section1'=> [
                'name' => 'value',
                'dummy' => 'foo'
            ],
            'section2' => 'laruence',
            'new' => 'value'
            ], $config3->toArray());
        //---------------------------------------

        $sick = @new Simple(null);

        $this->assertFalse($sick->__isset(1));
        $this->assertFalse($sick->__get(2));
        $sick->total = 1;
        $this->assertSame(1, count($sick));
        $this->assertSame(1, $sick->total);

        $sick->total_2 = 2;
        $this->assertSame(2, count($sick));
        $this->assertSame(2, $sick->total_2);
    }
    public function tearDown()
    {
    }
}
