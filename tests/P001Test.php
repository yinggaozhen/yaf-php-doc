<?php

namespace tests;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P001Test.php
 */
class P001Test extends Base
{
    /**
     * yaf extension is not available
     *
     * @throws \Exception
     */
    public function test()
    {
        $this->assertFalse(extension_loaded("yaf"));
    }
}
