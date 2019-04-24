<?php

namespace Yaf;

use Yaf\Response\Cli;
use Yaf\Response\Http;

abstract class Response_Abstract
{
    const DEFAULT_BODY = 'content';

    /**
     * @var array
     */
    protected $_header;

    /**
     * @var array
     */
    protected $_body;

    protected $_sendheader = false;

    /**
     * @internal
     * @var Response_Abstract
     */
    private static $instance;

    public function __construct()
    {
        $this->_header = [];
        $this->_body   = [];
    }

    /**
     * @param string $body
     * @param string|null $name
     * @return $this|bool
     */
    public function setBody(string $body, string $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_REPLACE')) {
            return $this;
        }

        return false;
    }

    /**
     * @param string $body
     * @param string|null $name
     * @return $this|bool
     */
    public function appendBody(?string $body, ?string $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_APPEND')) {
            return $this;
        }

        return false;
    }

    /**
     * @param string $body
     * @param string|null $name
     * @return $this|bool
     */
    public function prependBody(string $body, string $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_PREPEND')) {
            return $this;
        }

        return false;
    }

    /**
     * @param string|null $name
     * @return int
     */
    public function clearBody(string $name = null)
    {
        if ($name) {
            unset($this->_body[$name]);
        } else {
            $this->_body = [];
        }

        return 1;
    }

    /**
     * @param string|null $name
     * @return string
     */
    public function getBody(string $name = null)
    {
        if (empty($name)) {
            $body = $this->_body;
        } else {
            $body = $this->_body[$name];
        }

        return $body ?: '';
    }

    /**
     * @return bool
     */
    public function response(): bool
    {
        foreach ($this->_body as $body) {
            if (!is_string($body)) {
                continue;
            }

            echo $body;
        }

        return true;
    }

    /**
     * @param string        $name
     * @param string        $body
     * @param string        $response_type 其实是int
     * @return int
     */
    private function alterBody(?string $name, ?string $body, string $response_type): int
    {
        if (strlen($body) === 0) {
            return 1;
        }

        if (empty($name)) {
            $name = self::DEFAULT_BODY;
        }

        if (!array_key_exists($name, $this->_body)) {
            $this->_body[$name] = $body;
        } else {
            switch ($response_type) {
                case 'YAF_RESPONSE_PREPEND':
                    $this->_body[$name] = $body . $this->_body[$name];
                    break;
                case 'YAF_RESPONSE_APPEND':
                    $this->_body[$name] .= $body;
                    break;
                case 'YAF_RESPONSE_REPLACE':
                    $this->_body[$name] = $body;
                    break;
                default:
                    break;
            }
        }

        return 1;
    }

    public function __destruct()
    {
    }

    private function __clone()
    {
    }

    public function __toString()
    {
        $zbody = $this->_body;
        $return_value = implode('', $zbody);

        return $return_value;
    }

    /**
     * 内部使用,YAF对外不存在此方法
     *
     * @internal
     */
    public static function instance($sapi)
    {
        if (!is_null(self::$instance)) {
            return self::$instance;
        }

        if (strncasecmp($sapi, 'cli', 4)) {
            self::$instance = new Cli();
        } else {
            self::$instance = new Http();
        }

        return self::$instance;
    }
}
