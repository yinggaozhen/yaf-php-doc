<?php

use tests\Base;
use Yaf\Application;
use Yaf\View\Simple;

class SimpleView extends Simple
{
    public function assign($name, $value = null)
    {
        $this->_tpl_vars[$name] = $value;
    }
}

/**
 * Check for Custom view engine
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P053Test.php
 */
class P053Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        require __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     * @throws ReflectionException
     * @throws TypeError
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => YAF_TEST_APPLICATION_PATH,
            ],
        ];

        $tpl_dir = YAF_TEST_APPLICATION_PATH . '/views';
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/053Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/053Test_2.inc'));
        file_put_contents($tpl_dir . '/index/index.phtml', file_get_contents(__DIR__ . '/common/053Test_3.inc'));

        $app = new Application($config);
        ob_start();
        $response = $app->bootstrap()->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame('custom view', $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
