<?php

use tests\Base;
use Yaf\Application;
use Yaf\Request\Simple;

/**
 * Check for ReturnResponse in cli
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P083Test.php
 */
class P083Test extends Base
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

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/083Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml', 'okey');

        $app = new Application($config);
        $response = $app->getDispatcher()->returnResponse(true)->dispatch(new Simple('CLI', 'Index', 'Index', 'index'));
        $this->assertSame('okey', $response->getBody());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
