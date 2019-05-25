<?php

use tests\Base;
use Yaf\Application;

/**
 * Fixed bug that alter_response is not binary safe
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P069Test.php
 */
class P069Test extends Base
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

        file_put_contents(YAF_TEST_APPLICATION_PATH . "/controllers/Index.php", <<<PHP
<?php
   class IndexController extends \Yaf\Controller_Abstract {
         public function indexAction() {
         }
   }
PHP
        );


        file_put_contents(YAF_TEST_APPLICATION_PATH . "/views/index/index.phtml", "head <?php echo chr(0);?> tail" . PHP_EOL);

        ob_start();
        $app = new Application($config);
        $app->run();
        $acutal = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(stripos($acutal, 'head') !== false && stripos($acutal, 'tail') !== false);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
