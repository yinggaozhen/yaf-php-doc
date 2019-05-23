<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for Sample application
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P048Test.php
 */
class P048Test extends Base
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

        ob_start();
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Error.php', file_get_contents(__DIR__ . '/common/048Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/048Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/plugins/Test.php', file_get_contents(__DIR__ . '/common/048Test_3.inc'));
        $value = null;
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/048Test_4.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml',file_get_contents(__DIR__ . '/common/048Test_5.inc'));

        $app = new Application($config);
        $app->bootstrap()->run();
        $content = ob_get_contents();
        ob_end_clean();

        $expect = <<<EXPECT
string(13) "routerStartup"
string(14) "routerShutdown"
string(19) "dispatchLoopStartup"
string(11) "preDispatch"
string(4) "init"
string(6) "action"
bool(true)
string(12) "postDispatch"
string(43) "dispatchLoopShutdown, global var is:changed"
string(4) "view"

EXPECT;

        $this->assertSame($expect, $content);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
