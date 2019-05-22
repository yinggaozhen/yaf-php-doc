<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for Yaf_Application
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P022Test.php
 */
class P022Test extends Base
{
    public function setUp()
    {
        parent::setUp();
        if (is_dir(__DIR__ . '/tmp')) {
            rmdir(__DIR__ . '/tmp');
        }
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => realpath(__DIR__),
                'dispatcher' => [
                    'catchException' => 0,
                    'throwException' => 0,
                ],
            ],
        ];

        $app = new Application($config);
        $this->assertSame(__DIR__, $app->getAppDirectory());

        $dir = $app->getAppDirectory() . '/tmp';
        mkdir($dir);
        $app->setAppDirectory($dir);
        $this->assertSame(__DIR__ . '/tmp', $app->getAppDirectory());

        $exception = false;
        try {
            $app->run();
        } catch (Exception $e) {
            $exception = true;
            $this->assertTrue(
                strncmp(
                    'Failed opening controller script %s: %s',
                    Application::app()->getLastErrorMsg(),
                    strlen('Failed opening controller script ')
                ) === 0
            );
        }

        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::tearDown();
        if (is_dir(__DIR__ . '/tmp')) {
            rmdir(__DIR__ . '/tmp');
        }
    }
}
