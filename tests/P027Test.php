<?php

use tests\Base;
use Yaf\Application;

/**
 * Check for Yaf autoload controller
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P027Test.php
 */
class P027Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        YAF_G('bak_use_spl_autoload', YAF_G('yaf.use_spl_autoload'));
        YAF_G('bak_library', YAF_G('yaf.library'));
        YAF_G('environ', YAF_G('yaf.environ'));

        YAF_G('yaf.use_spl_autoload', 0);
        YAF_G('yaf.library', '/php/global/dir');
        YAF_G('yaf.environ', 'product');
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $config = [
            'application' => [
                'directory' => realpath(dirname(__FILE__)),
                'dispatcher' => [
                    'catchException' => 0,
                    'throwException' => 0,
                ],
            ],
        ];

        $app = new Application($config);
        $this->assertSame('product', $app->environ());
        $this->assertSame(2, count($app->getConfig()->application));

        @$app->execute(123);
        $this->assertSame('Yaf_Application::execute must be a valid callback', error_get_last()['message']);
    }

    public function tearDown()
    {
        parent::tearDown();

        YAF_G('yaf.use_spl_autoload', YAF_G('bak_use_spl_autoload'));
        YAF_G('yaf.library', YAF_G('bak_library'));
        YAF_G('yaf.environ', YAF_G('bak_environ'));
    }
}
