<?php

namespace tests;

use Yaf\Session;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P016Test.php
 */
class P016Test extends Base
{
    public function setUp()
    {
        @ini_set('session.save_handler', 'files');
        @ini_set('session.save_path', '');

        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        $session = @Session::getInstance();

        $_SESSION["name"] = "Laruence";

        $age = 28;
        $session->age = $age;
        unset($age);

        unset($session);
        $session2 = @Session::getInstance();
        $session2["company"] = "Baidu";
        $this->assertTrue(isset($session2->age));
        $this->assertTrue($session2->has("name"));
        $this->assertSame(3, count($session2));

        ob_start();
        foreach ($session2 as $key => $value) {
            echo $key , "=>", $value, "\n";
        }
        $actual = ob_get_contents();
        ob_end_clean();

        $expected = <<<OUTPUT
name=>Laruence
age=>28
company=>Baidu

OUTPUT;

        $this->assertSame($expected, $actual);
        unset($session2);

        $session3 = @Session::getInstance();
        $session3->del("name");
        unset($session3["company"]);
        unset($session3->age);

        $this->assertSame(0, count($session3));
    }

    public function tearDown()
    {
        $_SESSION = [];
    }
}
