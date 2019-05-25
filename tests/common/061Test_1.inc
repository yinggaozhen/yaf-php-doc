<?php

class IndexController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {
        $this->forward('dummy');
    }

    public function dummyAction()
    {
    }
}
