<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for working with other autoloaders
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P060Test.php
 */
class P060Test extends Base
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

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/060Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/060Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . "/views/index/index.phtml", "<?php echo get_class(\$obj);?>");

        ob_start();
        $app = new Application($config);
        $response = $app->bootstrap()->run();
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertSame('Dummy', $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
