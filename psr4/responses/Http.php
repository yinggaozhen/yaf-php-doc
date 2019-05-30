<?php

namespace Yaf\Response;

use Yaf\Response_Abstract;

/**
 * 响应对象和请求对象相对应, 是发送给请求端的响应的载体
 *
 * @link http://www.laruence.com/manual/yaf.class.response.html#yaf.class.response.http
 */
class Http extends Response_Abstract
{
    /**
     * @var bool
     */
    protected $_sendheader = true;

    /**
     * 响应给请求端的HTTP状态码
     *
     * @var int
     */
    protected $_response_code = 0;

    /**
     * @link http://www.php.net/manual/en/yaf-response-abstract.setheader.php
     *
     * @param string $name
     * @param string $value
     * @param bool $replace
     * @param int $response_code
     * @return bool
     */
    public function setHeader($name, $value, $replace = true, $response_code = 0)
    {
        if ($response_code) {
            $this->_response_code = $response_code;
        }

        return (bool) $this->alertHeader($name, $value, $replace ? 1 : 0);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-response-abstract.setallheaders.php
     *
     * @param array|null $headers
     * @return bool|null
     */
    public function setAllHeaders($headers)
    {
        foreach ($headers as $name => $header) {
            $this->alertHeader($name, $header, true);
        }

        return true;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-response-abstract.getheader.php
     *
     * @param null $name
     * @return array|mixed|null
     */
    public function getHeader($name = null)
    {
        if (!is_array($this->_header)) {
            return null;
        }

        if (empty($name)) {
            return $this->_header;
        }

        return $this->_header[$name] ?? null;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-response-abstract.clearheaders.php
     *
     * @return $this
     */
    public function clearHeaders()
    {
        $this->_header = [];

        return $this;
    }

    /**
     * 设置重定向URL
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.setRedirect.html
     *
     * @param string $url 要重定向到的URL
     * @return bool
     */
    public function setRedirect($url)
    {
        if (strlen($url)) {
            return false;
        }

        header("Location:" . $url, true, 0);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-response-abstract.response.php
     *
     * @inheritdoc
     * @return bool
     */
    public function response()
    {
       $response_code = $this->_response_code;
       http_response_code($response_code);

        foreach ($this->_header as $header_name => $entry) {
            if ($header_name) {
                header(sprintf("%s: %s", $header_name, $entry), true, 0);
            } else {
                header(sprintf("%lu: %s", $header_name, $entry), true, 0);
            }
       }
       parent::response();

       return true;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param string $name
     * @param string $value
     * @param int $replace
     * @return int
     */
    private function alertHeader($name, $value, $replace)
    {
        if (empty($name)) {
            return 1;
        }

        $value = (string) $value;
        if (!isset($this->_header, $name)) {
            $this->_header[$name] = $value;
            return 1;
        }

        if ($replace) {
            $this->_header[$name] = $value;
        } else {
            if (isset($this->_header[$name])) {
                $this->_header[$name] .= ', ' . $value;
            } else {
                $this->_header[$name] = $value;
            }
        }

        return 1;
    }
}
