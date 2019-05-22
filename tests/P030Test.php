<?php

use tests\Base;
use Yaf\Config\Ini;

/**
 * Check for Yaf_Config_Ini::__construct with section
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P030Test.php
 */
class P030Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $file = __DIR__ . '/common/configs/simple.ini';

        $exception = false;

        try {
            $config = new Ini($file, 'ex');
        } catch (Exception $e) {
            $exception = true;
            $this->assertTrue(
                strncmp(
                    "There is no section 'ex' in",
                    $e->getMessage(),
                    strlen("There is no section 'ex' in")
                ) === 0
            );
        }

        $this->assertTrue($exception);
    }
}
