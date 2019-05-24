<?php

use tests\Base;
use Yaf\Application;

/**
 * Check nesting view render
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P059Test.php
 */
class P059Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        require __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => YAF_TEST_APPLICATION_PATH,
            ],
        ];

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/059Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/059Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml', "<?php print_r(\$this); \$this->display('index/sub.phtml', array('content' => 'dummy'));?>");
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/sub.phtml', "<?php echo \$content; echo \$this->eval('foobar'); ?>");

        $app = new Application($config);
        ob_start();
        $response = $app->bootstrap()->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(stripos($actual, 'dummyfoobar') !== false);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
