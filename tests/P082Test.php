<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Check for variables out of scope
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P082Test.php
 */
class P082Test extends Base
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
        $view = new Simple(DIR_ROOT);
        $view->assign('name', 'laruence');
        $tpl = YAF_TEST_APPLICATION_PATH . '/tpls/foo.phtml';

        file_put_contents($tpl, <<<HTML
<?php
   echo \$name, \$tpl;
HTML
        );

        ob_start();
        echo @$view->render($tpl);
        $actual = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(stripos(error_get_last()['message'], 'Undefined variable: tpl') !== false);
        $this->assertSame('laruence', $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
