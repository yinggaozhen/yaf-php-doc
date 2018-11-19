<?php

namespace Yaf;

abstract class Controller_Abstract
{
    /**
     * @var array
     */
    public $actions;

    protected $_module;

    protected $_name;

    protected $_request;

    protected $_response;

    protected $_invoke_args;

    protected $_view;

    /**
     * @param string     $action
     * @param array|null $var_array
     * @return string|bool
     */
    protected function render(string $action, array $var_array = null)
    {
        $output = $this->_render($action, $var_array);

        return $output ? $output : false;
    }

    /**
     * @param string $action_name
     * @param array  $var_array
     */
    private function _render(string $action_name, array $var_array)
    {
        $view = $this->_view;
        $name = $this->_name;
    }
}