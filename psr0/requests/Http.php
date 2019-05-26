<?php

use Yaf\Request_Abstract;

/**
 * @link http://www.php.net/manual/en/class.yaf-request-http.php
 */
class Yaf_Request_Http extends Request_Abstract
{
    const SCHEME_HTTP = 'http';

    const SCHEME_HTTPS = 'https';

    /**
     * @link http://www.php.net/manual/en/yaf-request-http.construct.php
     *
     * @param null|string $request_uri
     * @param null|string $base_uri
     * @throws \TypeError
     */
    public function __construct($request_uri = null, $base_uri = null)
    {
        if (($argc = func_num_args()) > 2) {
            throw new \TypeError(__METHOD__ . " expects at most 2 parameters, {$argc} given");
        }

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
     * @link https://www.php.net/manual/en/yaf-request-http.getquery.php
     *
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getQuery($name = null, $default = null)
    {
        if (null === $name) {
            return $_GET;
        }

        return isset($_GET[$name]) ? $_GET[$name] : $default;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.getrequest.php
     *
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getRequest($name = null, $default = null)
    {
        if (null === $name) {
            return $_REQUEST;
        }

        return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.getpost.php
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getPost($name = null, $default = null)
    {
        if (null === $name) {
            return $_POST;
        }

        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.getcookie.php
     *
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getCookie($name = null, $default = null)
    {
        if (null === $name) {
            return $_COOKIE;
        }

        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.getfiles.php
     *
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getFiles($name = null, $default = null)
    {
        if (null === $name) {
            return $_FILES;
        }

        return isset($_FILES[$name]) ? $_FILES[$name] : $default;
    }

    public function getRaw()
    {
        // TODO 这里看不太懂
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.get.php
     *
     * @param string     $name
     * @param null|mixed $default
     * @return mixed|null
     */
    public function get($name, $default = null)
    {
        $value = $this->_params[$name] ?? null;

        if ($value) {
            return $value;
        } else {
            $methods = ['_POST', '_GET', '_COOKIE', '_SERVER'];

            foreach ($methods as $method) {
                $pzval = $$method[$name] ?? null;

                if (isset($pzval)) {
                    return $pzval;
                }
            }
        }

        return $default;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-request-http.isxmlhttprequest.php
     *
     * @return bool
     */
    public function isXmlHttpRequest()
    {
        $header = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? null;

        if ($header && strncasecmp("XMLHttpRequest", $header, strlen($header))) {
            return true;
        }

        return false;
    }

    private function __clone()
    {

    }
}
