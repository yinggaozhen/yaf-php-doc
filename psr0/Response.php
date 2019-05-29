<?php

use Yaf\Response\Cli;
use Yaf\Response\Http;
use Yaf\Response_Abstract;

/**
 * 响应对象和请求对象相对应, 是发送给请求端的响应的载体
 *
 * @link http://www.laruence.com/manual/yaf.class.response.html
 */
abstract class Yaf_Response_Abstract
{
    const DEFAULT_BODY = 'content';

    /**
     * 响应给请求的Header, 目前是保留属性
     *
     * @var array
     */
    protected $_header;

    /**
     * 响应给请求的Body内容
     *
     * @var array
     */
    protected $_body;

    /**
     * @var bool
     */
    protected $_sendheader = false;

    /**
     * @internal
     * @var Response_Abstract
     */
    private static $instance;

    /**
     * Response_Abstract constructor.
     *
     * @link https://www.php.net/manual/en/yaf-response-abstract.construct.php
     */
    public function __construct()
    {
        $this->_header = [];
        $this->_body   = [];
    }

    /**
     * 设置响应的Body, $name参数是保留参数, 目前没有特殊效果, 留空即可
     *
     * @link http://www.laruence.com/manual/yaf.class.response.setBody.html
     *
     * @param string $body 要响应的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)
     * @param string|null $name <p>
     * 要响应的字符串的key, 一般的你可以通过指定不同的key, 给一个response对象设置很多响应字符串, 可以在所有的请求结束后做layout
     * 如果你不做特殊处理, 交给Yaf去发送响应的话, 所有你设置的响应字符串, 按照被设置的先后顺序被输出给客户端.
     * <p>
     * @return $this|bool
     */
    public function setBody($body, $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_REPLACE')) {
            return $this;
        }

        return false;
    }

    /**
     * 往已有的响应body后附加新的内容, $name参数是保留参数, 目前没有特殊效果, 留空即可
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.appendBody.html
     *
     * @param string $body 要附加的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)
     * @param string|null $name
     * @return $this|bool
     */
    public function appendBody($body, $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_APPEND')) {
            return $this;
        }

        return false;
    }

    /**
     * 往已有的响应body前插入新的内容, $name参数是保留参数, 目前没有特殊效果, 留空即可
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.prependBody.html
     *
     * @param string $body 要插入的字符串, 一般是一段HTML, 或者是一段JSON(返回给Ajax请求)
     * @param string|null $name
     * @return $this|bool
     */
    public function prependBody($body, $name = null)
    {
        if ($this->alterBody($name, $body, 'YAF_RESPONSE_PREPEND')) {
            return $this;
        }

        return false;
    }

    /**
     * 清除已经设置的响应body
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.clearBody.html
     *
     * @param string|null $name
     * @return int
     */
    public function clearBody($name = null)
    {
        if ($name) {
            unset($this->_body[$name]);
        } else {
            $this->_body = [];
        }

        return 1;
    }

    /**
     * 获取已经设置的响应body
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.getBody.html
     *
     * @param string|null $name
     * @return string
     */
    public function getBody($name = null)
    {
        if (empty($name)) {
            $body = $this->_body[self::DEFAULT_BODY];
        } else {
            $body = $this->_body[$name];
        }

        return $body ?: '';
    }

    /**
     * 发送响应给请求端
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.response.response.html
     *
     * @return bool
     */
    public function response()
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
     * @link https://www.php.net/manual/en/yaf-response-abstract.destruct.php
     */
    public function __destruct()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-response-abstract.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-response-abstract.tostring.php
     */
    public function __toString()
    {
        $zbody = $this->_body;
        $return_value = implode('', $zbody);

        return $return_value;
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param string        $name
     * @param string        $body
     * @param string        $response_type 其实是int
     * @return int
     */
    private function alterBody($name, $body, $response_type)
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
