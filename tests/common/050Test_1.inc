<?php

class Bootstrap extends \Yaf\Bootstrap_Abstract
{
    public function _initReturn(\Yaf\Dispatcher $dispatcher)
    {
        $dispatcher->returnResponse(true);
    }
}
