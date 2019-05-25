<?php

use tests\Base;
use Yaf\Application;

/**
 * Fixed misleading error message when providing a string in Yaf_Application construction
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P070Test.php
 */
class P070Test extends Base
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
        $expection = false;

        $config = <<<INI
[product]
;CONSTANTS is supported
application.directory = APP_PATH "/application/"
INI;

        try {
            $app = new Application($config);
            $app->run();
        }
        catch (\Exception $e) {
            $expection = true;

            // TODO $e->getPrevious()->getMessage()
            $this->assertSame('Expects a path to *.ini configuration file as parameter', $e->getMessage());
        }

        $this->assertTrue($expection);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
