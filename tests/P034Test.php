<?php

use tests\Base;
use Yaf\View\Simple;

/**
 * Check for Yaf_View_Simple::eval
 *
 * @run ./vendor/bin/phpunit --bootstrap ./tests/bootstrap.php ./tests/P034Test.php
 */
class P034Test extends Base
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
        $view = new Simple('/tmp');
        $tpl = file_get_contents(__DIR__ . '/common/034Test.inc');

        $view->assign('entry', ['a', 'b', 'c']);
        $actual = $view->eval($tpl, ['name' => 'template']);

        $expect = <<<PHP
template
1. a
1. b
1. c
template
PHP;
        $this->assertSame($expect, $actual);
    }

    public function tearDown()
    {
        parent::setUp();
    }
}
