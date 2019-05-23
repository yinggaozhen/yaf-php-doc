<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Fixed bug that segv in Yaf_View_Simple::render if the tpl is not a string
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P040Test.php
 */
class P040Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function test()
    {
        $view = new Simple(__DIR__);
        $view->render(null);
        $view->render(0);
        $view->render(true);

        $this->assertTrue(true);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
