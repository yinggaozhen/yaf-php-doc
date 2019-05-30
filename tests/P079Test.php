<?php

use tests\Base;

/**
 * Autoloading the classes under library
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P079Test.php
 */
class P079Test extends Base
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

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/079Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/library/Test.php', file_get_contents(__DIR__ . '/common/079Test_2.inc'));

        $app = new \Yaf\Application($config);
        $app->bootstrap();
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
