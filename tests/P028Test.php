<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Bug segfault while call exit in a view template
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P028Test.php
 */
class P028Test extends Base
{
    public function setUp()
    {
        parent::setUp();

        require_once __DIR__ . '/common/build.inc';
        startup();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $view = new Simple(dirname(__FILE__));

        $view->assign('name', 'laruence');
        $tpl = YAF_TEST_APPLICATION_PATH . '/tpls/foo.phtml';

        file_put_contents($tpl, <<<HTML
okey
HTML
        );
        $this->assertSame('okey', $view->render($tpl));
    }

    public function tearDown()
    {
        shutdown();
    }
}
