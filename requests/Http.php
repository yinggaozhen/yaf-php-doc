<?php

namespace Yaf\Request;

use Yaf\Request_Abstract;

class Http extends Request_Abstract
{
    const SCHEME_HTTP = 'http';

    const SCHEME_HTTPS = 'https';

    public function __construct(?string $request_uri, ?string $base_uri)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';

        if ($method) {
        } else if (strtolower(php_sapi_name()) != 'cli') {
            $method = 'Unknow';
        } else {
            $method = 'Cli';
        }

        $this->method = $method;

        if ($request_uri) {
            $settled_uri = $request_uri;
        } else {
            $settled_uri = '';

            do {
                $uri = $_SERVER['PATH_INFO'] ?? '';
                if (!empty($uri)) {
                    $settled_uri = $uri;
                    break;
                }

                $uri = $_SERVER['REQUEST_URI'] ?? '';
                if (!empty($uri)) {
                    if (strncasecmp($uri, 'http', strlen('http') - 1) == 0) {
                        $settled_uri = parse_url($uri, PHP_URL_PATH);
                    } else {
                        $settled_uri = explode('?', $uri)[0];
                    }
                    break;
                }

                $uri = $_SERVER['ORIG_PATH_INFO'] ?? '';
                if (!empty($uri)) {
                    $settled_uri = $uri;
                }
            } while(0);
        }

        if ($settled_uri) {
            $this->_uri = str_replace('//', '/', $settled_uri);
            $this->_setBaseUri($base_uri, $settled_uri);
        }

        $this->_params = [];

        return $this;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getQuery(string $name, $default = null): ?string
    {
        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getRequest(string $name, $default = null): ?string
    {
        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getPost(string $name, $default = null): ?string
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getCookie(string $name, $default = null): ?string
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return null|string
     */
    public function getFiles(string $name, $default = null): ?string
    {
        return isset($_FILES[$name]) ? $_FILES[$name] : $default;
    }

    public function getRaw()
    {
        // TODO 这里看不太懂
    }

    /**
     * @param string     $name
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

    private function __clone()
    {

    }
}

class Yaf_Request_Http extends Http {}
