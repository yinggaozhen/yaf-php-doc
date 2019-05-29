<?php

namespace Yaf;

/**
 * Yaf_Action_Abstract是MVC中C的动作, 一般而言动作都是定义在Yaf_Controller_Abstract的派生类中的
 * 但是有的时候, 为了使得代码清晰, 分离一些大的控制器, 则可以采用单独定义Yaf_Action_Abstract来实现.
 * Yaf_Action_Abstract体系具有可扩展性, 可以通过继承已有的类, 来实现这个抽象类, 从而添加应用自己的应用逻辑.
 *
 * @link http://www.laruence.com/manual/yaf.class.action.html
 */
abstract class Action_Abstract extends Controller_Abstract
{
    /**
     * @var \Yaf\Controller_Abstract
     */
    protected $_controller = null;

    /**
     * @link https://www.php.net/manual/en/yaf-action-abstract.execute.php
     *
     * @return mixed
     */
    abstract function execute();

    /**
     * @link https://www.php.net/manual/en/yaf-action-abstract.getcontroller.php
     *
     * @return \Yaf\Controller_Abstract
     */
    public function getController()
    {
        return $this->_controller;
    }
}
