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

    public function setHeader(string $name, string $value, bool $replace = true, int $response_code = 0): bool
    {
        // TODO 这里源代码可能会存在BUG,如果想要将response_code重置为0,是办不到的
        if ($response_code) {
            $this->_response_code = $response_code;
        }

        return (bool) $this->alertHeader($name, $value, $replace ? 1 : 0);
    }

    /**
     * @param array|null $headers
     * @return bool|null
     */
    public function setAllHeaders(?array $headers): ?bool
    {
        if (empty($headers)) {
            return;
        }

        foreach ($headers as $name => $header) {
            $this->alertHeader($name, $header, true);
        }

        return true;
    }

    /**
     * TODO 源码注释有问题
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
     * @return $this
     */
    public function clearHeaders()
    {
        $this->_header = [];

        return $this;
    }

    /**
     * 返回结果http code只能为都是302,这里是个缺陷
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
