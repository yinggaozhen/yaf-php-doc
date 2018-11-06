<?php

namespace Yaf\Request;

// TODO 空间名称和文件名称不统一

use Yaf\Request_Abstract;

final class Simple extends Request_Abstract
{
    public function __construct(string $method = null, string $module = null, string $controller = null, string $action = null, array $params = null)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method) {
        } else if (strtolower(php_sapi_name()) != 'cli') {
            $method = 'Unknow';
        } else {
            $method = 'Cli';
        }

        $this->method = $method;

        global $yaf;

        if ($module || $controller || $action) {
            if ($module) {
                $this->module = $module;
            } else {
                $this->module = $yaf['default_module'];
            }

            if ($controller) {
                $this->controller = $controller;
            } else {
                $this->controller = $yaf['default_controller'];
            }

            if ($action) {
                $this->action = $action;
            } else {
                $this->action = $yaf['default_action'];
            }

            $this->_routed = 1;
        } else {
            $query = $_GET[self::YAF_REQUEST_SERVER_URI];
            $this->_uri = $query ?: '';
        }

        $this->_params = $params ?: [];
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getQuery(string $name, $default = null): ?string
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getRequest(string $name, $default = null): ?string
    {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getPost(string $name, $default = null): ?string
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getCookie(string $name, $default = null): ?string
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getFiles(string $name, $default = null): ?string
    {
        return isset($_FILES[$name]) ? $_FILES[$name] : $default;
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        $value = $this->_params[$name];

        if ($value) {
            return $value;
        } else {
            $methods = ['_POST', '_GET', '_COOKIE', '_SERVER'];

            foreach ($methods as $method) {
                $pzval = $$method[$name];

                if (isset($pzval)) {
                    return $pzval;
                }
            }
        }

        return $default;
    }

    public function isXmlHttpRequest(): bool
    {
        $header = $_SERVER['HTTP_X_REQUESTED_WITH'];

        if ($header && strncasecmp("XMLHttpRequest", $header, strlen($header))) {
            return true;
        }

        return false;
    }
}