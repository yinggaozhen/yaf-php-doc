<?php

class ErrorController extends \Yaf\Controller_Abstract
{
    public function errorAction($exception)
    {
        var_dump($exception->getMessage());
        return false;
    }
}
