<?php

use tests\Base;
use Yaf\Application;
use Yaf\Request\Simple;

/**
 * Check actions map with defined action class
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P067Test.php
 */
class P067Test extends Base
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

        file_put_contents(YAF_TEST_APPLICATION_PATH . '/controllers/Index.php', file_get_contents(__DIR__ . '/common/067Test_1.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/actions/sub.php', file_get_contents(__DIR__ . '/common/067Test_2.inc'));
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/index.phtml', 'indexAction' . PHP_EOL);
        file_put_contents(YAF_TEST_APPLICATION_PATH . '/views/index/sub.phtml', 'subAction' . PHP_EOL);

        $app = new Application($config);

        $request = new Simple();
        $app->getDispatcher()->dispatch($request);

        $new_request = new Simple();
        $new_request->setActionName('sub');
        $app->getDispatcher()->dispatch($new_request);

        $this->assertSame('ok', 'ok');
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
