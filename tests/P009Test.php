<?php

namespace tests;

use Yaf\View\Simple;

/**
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P009Test.php
 */
class P009Test extends Base
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @throws \Exception
     */
    public function test()
    {
        $view = new Simple(__DIR__);
        $value = 'laruence';
        $view->assign('name', $value);
        unset($value);

        $this->assertSame('laruence', $view->get('name'));
        $this->assertSame(6, strlen($view->render(__DIR__ . '/common/009Test.phtml')));
        $this->assertSame('laruence', $view->name);
        $this->assertNull($view->noexists);
    }

    public function tearDown()
    {
    }
}
