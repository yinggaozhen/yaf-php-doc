<?php

class IndexController extends \Yaf\Controller_Abstract
{
    public $actions = [
        'sub' => 'actions/sub.php',
    ];

    public function indexAction()
    {
        var_dump($this->forward('sub'));
    }
}
