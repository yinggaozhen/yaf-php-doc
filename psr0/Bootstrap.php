<?php

/**
 * Yaf_Bootstrap_Abstract提供了一个可以定制Yaf_Application的最早的时机, 它相当于一段引导, 入口程序.
 * 它本身没有定义任何方法.但任何继承自Yaf_Bootstrap的类中的以_init开头的方法, 都会在Yaf_Application::bootstrap时刻被调用.
 * 调用的顺序和这些方法在类中的定义顺序相同. Yaf保证这种调用顺序.
 *
 * @link http://www.laruence.com/manual/yaf.class.bootstrap.html
 */
abstract class Yaf_Bootstrap_Abstract
{
    public const YAF_DEFAULT_BOOTSTRAP = 'Bootstrap';

    public const YAF_DEFAULT_BOOTSTRAP_LOWER = 'bootstrap';

    public const YAF_BOOTSTRAP_INITFUNC_PREFIX = '_init';
}
