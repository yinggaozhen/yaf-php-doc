<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Bug #63438 (Strange behavior with nested rendering)
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P0bug63438Test.php
 */
class P0bug63438Test extends Base
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
        function shut_down()
        {
            echo 'done';
        }

        register_shutdown_function('shut_down');

        /**
         * @param $file
         * @return string
         * @throws Exception
         * @throws \Yaf\Exception\TypeError
         */
        function view($file)
        {
            static $view;

            $view = new Simple(__DIR__ . '/common/tmp_build');

            return $view->render($file);
        }

        ob_start();
        file_put_contents(__DIR__ . '/common/tmp_build/outer.phtml', "1 <?php print view('inner.phtml');?> 3\n");
        file_put_contents(__DIR__ . '/common/tmp_build/inner.phtml', "2");
        print (view('outer.phtml'));
        $actual = ob_get_contents();
        ob_end_clean();
         $this->assertSame("1 2 3\n", $actual);

        ob_start();
        file_put_contents(__DIR__ . '/common/tmp_build/outer.phtml', "1 <?php \$this->display('inner.phtml');?> 3\n");
        print (view('outer.phtml'));
        $actual = ob_get_contents();
        ob_end_clean();
        $this->assertSame("1 2 3\n", $actual);

        ob_start();
        file_put_contents(__DIR__ . '/common/tmp_build/outer.phtml', "1 <?php echo \$this->eval('2');?> 3\n");
        print (view('outer.phtml'));
        $actual = ob_get_contents();
        ob_end_clean();
        $this->assertSame("1 2 3\n", $actual);

        $exception = false;
        try {
            file_put_contents(__DIR__ . '/common/tmp_build/outer.phtml', "1 <?php \$this->display('inner.phtml');?> 3\n");
            file_put_contents(__DIR__ . '/common/tmp_build/inner.phtml', "<?php undefined_function(); ?>");
            print (view('outer.phtml'));
        } catch (\Error $e) {
            $this->assertSame('Call to undefined function undefined_function()', $e->getMessage());
            $exception = true;
        }
        $this->assertTrue($exception);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
