<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Check for Yaf_View_Simple recursive render error message outputing
 *
 * @codeCoverageIgnore
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P039Test.php
 */
class P039Test extends Base
{
    public function setUp()
    {
        parent::setUp();
        // $this->markTestSkipped('syntax error test');

        require_once __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $view = new Simple(DIR_ROOT);
        $view->assign('name', 'laruence');
        $tpl = YAF_TEST_APPLICATION_PATH . '/tpls/foo.phtml';
        $tpl2 = YAF_TEST_APPLICATION_PATH . '/tpls/foo2.phtml';

        file_put_contents($tpl, <<<HTML
<?php
   echo \$this->render(\$tpl);
?>
HTML
        );

        file_put_contents($tpl2, <<<HTML
<?php
   if ((1) { //syntax error
   }
?>
HTML
        );

        $exception = false;
        try {
            echo $view->render($tpl, ['tpl' => $tpl2]);
        } catch (Error $e) {
            $exception = true;
            $this->assertSame("syntax error, unexpected '}'", $e->getMessage());
        }

        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
