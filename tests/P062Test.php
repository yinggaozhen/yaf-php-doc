<?php

use tests\Base;
use Yaf\Application;
use Yaf\Dispatcher;
use Yaf\View\Simple;

/**
 * Check for Yaf_View_Simple and application's template directory
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P062Test.php
 */
class P062Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     * @throws TypeError
     */
    public function test()
    {
        $exception = false;
        $view = new Simple(__DIR__);
        try {
            $view = new Simple('');
        } catch (\Yaf\Exception\TypeError $e) {
            $exception = true;
            $this->assertSame('Expects an absolute path for templates directory', $e->getMessage());
        }
        $this->assertTrue($exception);

        $config = [
            'application' => [
                'directory' => __DIR__,
            ],
        ];
        $app = new Application($config);

        $view = Dispatcher::getInstance()->initView([]);
        $this->assertNull($view->getScriptPath());
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
