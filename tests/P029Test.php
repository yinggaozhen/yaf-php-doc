<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Check for Yaf_View_Simple::get and clear
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P029Test.php
 */
class P029Test extends Base
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
        $view->assign('a', 'b');
        $view->assign('b', 'a');
        $this->assertSame('b', $view->get('a'));
        $this->assertSame([
            'a' => 'b',
            'b' => 'a'
        ], $view->get());

        $view->clear('b');
        $this->assertSame([
            'a' => 'b'
        ], $view->get());
        $view->clear();
        $this->assertSame([], $view->get());
    }
}
