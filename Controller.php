<?php

namespace Yaf;

use const INTERNAL\PHP\DEFAULT_SLASH;
use Yaf\View\Simple;

abstract class Controller_Abstract
{
    /**
     * @var array
     */
    public $actions;

    /**
     * @var mixed
     */
    protected $_module;

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var Request_Abstract
     */
    protected $_request;

    /**
     * @var Response_Abstract
     */
    protected $_response;

    protected $_invoke_args;

    /**
     * @var View_Interface
     */
    protected $_view;

    final public function __construct(Request_Abstract $request, Response_Abstract $response, View_Interface $view, array $invokeArgs = null)
    {
        if ($invokeArgs) {
            $this->_invoke_args = $invokeArgs;
        }

        $module = $request->getModuleName();

        $this->_request = $request;
        $this->_response = $response;
        $this->_module = $module;
        $this->_view = $view;

        if (!($this instanceof Action_Abstract) && is_callable([$this, 'init'])) {
            call_user_func([$this, 'init']);
        }
    }

    final private function __clone()
    {
    }

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
     * @param string $action
     * @param array|null $var_array
     * @return bool
     */
    protected function display(string $action, array $var_array = null): bool
    {
        return (bool) $this->_display($action, $var_array);
    }

    /**
     * @return Request_Abstract
     */
    public function getRequest(): Request_Abstract
    {
        return $this->_request;
    }

    /**
     * @return Response_Abstract
     */
    public function getResponse(): Response_Abstract
    {
        return $this->_response;
    }

    /**
     * @return $this
     */
    public function initView()
    {
        return $this;
    }

    /**
     * @param null|string $name
     * @return null|string
     */
    public function getInvokeArg(?string $name): ?string
    {
        if ($name) {
            $args = $this->_invoke_args;

            if (is_null($args)) {
                return null;
            }

            if (array_key_exists($args, $name)) {
                return $args[$name];
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->_module;
    }

    /**
     * @return View_Interface
     */
    public function getView(): View_Interface
    {
        return $this->_view;
    }

    /**
     * @return array
     */
    public function getInvokeArgs(): array
    {
        return $this->_invoke_args;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function setViewpath(string $path): bool
    {
        if (!is_string($path)) {
            return false;
        }

        $view = $this->_view;
        if ($view instanceof Simple) {
            $view->setScriptPath($path);
        } else {
            call_user_func([$view, 'setscriptpath'], $path);
        }

        return true;
    }

    /**
     * @return mixed|null|string
     */
    public function getViewpath()
    {
        $view = $this->_view;

        if ($view instanceof View_Interface) {
            $tpl_dir = $view->getScriptPath();

            if (!is_string($tpl_dir) && YAF_G('view_directory')) {
                return YAF_G('view_directory');
            }

            return $tpl_dir;
        } else {
            $ret = call_user_func([$view, 'getscriptpath']);

            return $ret ?? null;
        }
    }

    public function forward()
    {
        // TODO 下次从这里开始
    }

    public function redirect()
    {
        // TODO 下次从这里开始
    }

    /**
     * @param string $action_name
     * @param array $var_array
     * @return string
     */
    private function _render(string $action_name, array $var_array): string
    {
        $view = $this->_view;
        $name = $this->_name;

        $view_ext = YAF_G('view_ext');
        $self_name = str_replace('_', DEFAULT_SLASH, strtolower($name));
        $action_name = str_replace('_', DEFAULT_SLASH, $action_name);

        $path = sprintf("%s%c%s.%s", $self_name, DEFAULT_SLASH, $action_name, $view_ext);

        if ($var_array) {
            $ret = call_user_func([$view, 'render'], $path, $var_array);
        } else {
            $ret = call_user_func([$view, 'render'], $path);
        }

        if (is_null($ret) || !is_string($ret)) {
            return null;
        }

        return $ret;
    }

    /**
     * @param string $action_name
     * @param array $var_array
     * @return int
     */
    private function _display(string $action_name, array $var_array): int
    {
        $view = $this->_view;
        $name = $this->_name;

        $view_ext  = YAF_G('view_ext');
        $self_name = str_replace('_', DEFAULT_SLASH, strtolower($name));
        $action_name = str_replace('_', DEFAULT_SLASH, $action_name);

        $path = sprintf("%s%c%s.%s", $self_name, DEFAULT_SLASH, $action_name, $view_ext);

        if ($var_array) {
            $ret = call_user_func([$view, 'display'], $path, $var_array);
        } else {
            $ret = call_user_func([$view, 'display'], $path);
        }

        if (is_null($ret) || $ret === false) {
            return 0;
        }

        return 1;
    }
}