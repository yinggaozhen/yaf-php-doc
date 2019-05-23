<?php

class ErrorController extends \Yaf\Controller_Abstract
{
    public function errorAction($exception)
    {
        $this->_view->msg = $exception->getMessage();
    }
}
