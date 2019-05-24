<?php

class IndexController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {
        $this->_view->name = 'custom view';
    }
}
