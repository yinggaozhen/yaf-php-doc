<?php
class IndexController extends \Yaf\Controller_Abstract
{
    public function indexAction()
    {
        $obj = new Dummy();
        $this->_view->obj = $obj;
    }
}
