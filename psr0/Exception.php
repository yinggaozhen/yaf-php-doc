<?php

/**
 * Yaf_Exception是Yaf使用的异常类型, 它继承自Exception, 并实现了异常链.
 *
 * @link http://www.laruence.com/manual/yaf.class.exception.html
 */
class Yaf_Exception extends \Exception
{
    /**
     * 异常代码
     *
     * @var int
     */
    protected $code = 0;

    /**
     * 异常信息
     *
     * @var string
     */
    protected $message;

    /**
     * 此异常之前的异常
     *
     * @var string
     */
    protected $previous;
}
