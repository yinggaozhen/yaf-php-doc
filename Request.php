<?php

namespace Yaf;

/**
 * @method bool isGet()
 * @method bool isPost()
 * @method bool isPut()
 * @method bool isDelete()
 * @method bool isPatch()
 * @method bool isHead()
 * @method bool isOptions()
 * @method bool isCli()
 */
abstract class Request_Abstract
{
    public $module;

    public $controller;

    public $action;

    public $method;

    /**
     * @var array
     */
    protected $_params;

    protected $_language;

    protected $_exception;

    /**
     * @var string
     */
    protected $_base_uri;

    /**
     * @var string
     */
    protected $_uri = '';

    /**
     * @var int
     */
    protected $_dispatched = 0;

    /**
     * @var int
     */
    protected $_routed = 0;

    public function isXmlHttpRequest()
    {
        return false;
    }

    /**
     * @return int
     */
    public function isDispatched()
    {
        return $this->_dispatched === true ? 1 : 0;
    }

    /**
     * @return int
     */
    public function isRouted()
    {
        return $this->_routed === true ? 1 : 0;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getServer($name, $default = null)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getEnv($name, $default = null)
    {
        return isset($_ENV[$name]) ? $_ENV[$name] : $default;
    }

    /**
     * @param array ...$params
     * @return null
     */
    public function setParam(...$params)
    {
        if (count($params) == 1 && is_array($params)) {
            $this->_params = $params;
        } else if (count($params) == 2) {
            list($name, $value) = $params;

            $this->_params[$name] = $value;
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $default;
    }

    public function getException()
    {
        return $this->_exception;
    }

    /**
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->module;
    }

    /**
     * @return mixed
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->action;
    }

    /**
     * @param string $module
     * @return bool|$this
     */
    public function setModuleName($module)
    {
        if (!is_string($module)) {
            trigger_error('Expect a string module name');
            return false;
        }

        $this->module = $module;
        return $this;
    }

    /**
     * @param string $controller
     * @return bool|$this
     */
    public function setControllerName($controller)
    {
        if (!is_string($controller)) {
            trigger_error('Expect a string controller name');
            return false;
        }

        $this->controller = $controller;
        return $this;
    }

    /**
     * @param string $action
     * @return bool|$this
     */
    public function setActionName($action)
    {
        if (!is_string($action)) {
            trigger_error('Expect a string action name');
            return false;
        }

        $this->action = $action;
        return $this;
    }

    /**
     * @param string $name
     * @return bool|$this
     */
    public function setBaseUri($name)
    {
        if (empty($name)) {
            return false;
        }

        $this->_base_uri = $name;
        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setRequestUri($uri)
    {
        $this->_uri = $uri;
        return $this;
    }

    public function setDispatched()
    {
        $this->_dispatched = 1;

        return true;
    }

    public function setRouted()
    {
        $this->_routed = 1;

        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getLanguage()
    {
        $lang = $this->_language;

        if (is_string($lang)) {
            $accept_langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            if (empty($accept_langs)) {
                return null;
            } else if (!is_string($accept_langs)) {
                return null;
            } else {
                // TODO php_strtok_r
            }
        }

        return $lang;
    }

    public function getBaseUri()
    {
        return $this->_base_uri;
    }

    public function getRequestUri()
    {
        return $this->_uri;
    }

    public function __call($method, $arguments)
    {
        $method = strtolower($method);
        $allowMethod = ['Get', 'Post', 'Delete', 'Patch', 'Put', 'Head', 'Options', 'Cli'];

        if (!in_array(ucfirst($method), $allowMethod)) {
            // TODO throw method error
            return null;
        }

        return $this->method == $method;
    }
}