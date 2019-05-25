<?php

namespace Yaf\Response;

use Yaf\Response_Abstract;

class Http extends Response_Abstract
{
    /**
     * @var bool
     */
    protected $_sendheader = true;

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
    public function setHeader(string $name, string $value, bool $replace = true, int $response_code = 0): bool
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
    public function setAllHeaders(?array $headers): ?bool
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

        return $this->_header[$name];
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
     * @link http://www.php.net/manual/en/yaf-response-abstract.setredirect.php
     *
     * @param string $url
     * @return bool
     */
    public function setRedirect(string $url): bool
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
    public function response(): bool
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
    private function alertHeader(string $name, string $value, int $replace): int
    {
        if (empty($name)) {
            return 1;
        }

        if (!isset($this->_header, $name)) {
            $this->_header[$name] = $value;
            return 1;
        }

        if ($replace) {
            $this->_header[$name] = $value;
        } else {
            $this->_header[$name] .= $value;
        }

        return 1;
    }
}
