<?php

namespace Yaf;

abstract class Controller_Abstract
{
    public $actions;

    protected $_module;

    protected $_name;

    protected $_request;

    protected $_response;

    protected $_invoke_args;

    protected $_view;

    /**
     * @param string     $action
     * @param array|NULL $var_array
     * @return string|bool
     */
    protected function render($action, array $var_array = NULL)
    {
        $output = $this->_render($this, $action, $var_array);

        return $output ? $output : false;
    }

    /**
     * @param self   $instance
     * @param string $action_name
     * @param array  $var_array
     */
    private function _render($instance, $action_name, $var_array)
    {
        $view = $instance->_view;
        $name = $instance->_name;
    }
}