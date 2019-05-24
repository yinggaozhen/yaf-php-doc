<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for Sample application with return response
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P050Test.php
 */
class P050Test extends Base
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
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => YAF_TEST_APPLICATION_PATH,
            ],
        ];

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/Bootstrap.php', file_get_contents(__DIR__ . '/common/050Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/050Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml', 'view');

        $app = new Application($config);
        $response = $app->bootstrap()->run();
        $this->assertSame('view', (string) $response);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
