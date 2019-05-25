<?php

use tests\Base;
use Yaf\Application;

/**
 * Bug empty template file interrupts forward chain
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P061Test.php
 */
class P061Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        require __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     * @throws TypeError
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => YAF_TEST_APPLICATION_PATH,
            ],
        ];

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/061Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Dummy.php', file_get_contents(__DIR__ . '/common/061Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml', '');
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/dummy.phtml', 'Dummy');

        ob_start();
        $app = new Application($config);
        $response = $app->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Dummy', $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
