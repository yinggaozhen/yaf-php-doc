<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for Sample application with exception
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P049Test.php
 */
class P049Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        YAF_G('yaf.use_spl_autoload', 0);
        YAF_G('yaf.lowcase_path', 0);

        require __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => YAF_TEST_APPLICATION_PATH,
                'dispatcher' => [
                    'catchException' => true,
                ],
                'library' => [
                ],
            ],
        ];

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Error.php', file_get_contents(__DIR__ . '/common/049Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/049Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/plugins/Test.php', file_get_contents(__DIR__ . '/common/049Test_3.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/049Test_4.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml',file_get_contents(__DIR__ . '/common/049Test_5.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/error/error.phtml',file_get_contents(__DIR__ . '/common/049Test_6.inc'));

        ob_start();
        $app = new Application($config);
        $app->bootstrap()->run();
        ob_end_flush();

        // exit;
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
