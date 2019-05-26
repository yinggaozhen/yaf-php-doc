<?php

/**
 * @link https://www.php.net/manual/en/class.yaf-exception.php
 */
class Yaf_Exception extends \Exception
{
    /**
     * @var int
     */
    protected $code = 0;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $previous;
}
