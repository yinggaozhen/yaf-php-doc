<?php

namespace Yaf\Config;

use Yaf\Config_Abstract;
use const YAF\ERR\TYPE_ERROR;

/**
 * Yaf_Config_Simple为存储在数组中的配置数据提供了适配器。
 *
 * @link http://www.laruence.com/manual/yaf.class.config.html#yaf.class.config.simple
 */
final class Simple extends Config_Abstract implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * @var bool
     */
    public $_readonly = false;

    /**
     * @var array
     */
    public $_config = [];

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.construct.php
     *
     * @param array $values
     * @param bool  $readonly
     * @throws \Exception
     */
    public function __construct($values, $readonly = null)
    {
        if ($readonly !== true) {
            $values = (array) $values;
        }

        return $this->simpleInstance($values, $readonly);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.get.php
     *
     * @param string|null $name
     * @return $this|bool|mixed|null
     * @throws \Exception
     */
    public function get($name = null)
    {
        if (!$name) {
            return $this;
        } else {
            $properties = $this->_config;
            $hash = (array) $properties;

            $pzval = $hash[$name] ?? null;
            if (is_null($pzval)) {
                return false;
            }

            if (is_array($pzval)) {
                $ret = $this->format($pzval);
                return $ret ?? null;
            } else {
                return $pzval;
            }
        }

        return false;
    }

    /**
     * @@link http://www.php.net/manual/en/yaf-config-simple.set.php
     *
     * @inheritdoc
     * @param $name
     * @param $value
     * @return bool
     */
    public function set($name, $value)
    {
        $readonly = $this->_readonly;

        if ($readonly === false) {
            $this->_config[$name] = $value;
            return true;
        }

        return false;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.readonly.php
     *
     * @return bool
     */
    public function readonly()
    {
        return (bool) $this->_readonly;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.current.php
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function current()
    {
        $pzval = current($this->_config);
        if (is_null($pzval)) {
            return false;
        }

        if (is_array($pzval)) {
            $ret = $this->format($pzval);
            return $ret ?? null;
        }

        return $pzval;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.next.php
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->_config);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.key.php
     *
     * @return int|mixed|null|string
     */
    public function key()
    {
        return key($this->_config);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.valid.php
     *
     * @return bool
     */
    public function valid()
    {
        return !is_null(key($this->_config));
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.rewind.php
     */
    public function rewind()
    {
        reset($this->_config);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.offsetexists.php
     *
     * @param mixed $offset
     * @return bool|mixed
     */
    public function offsetExists($offset)
    {
        return $this[$offset];
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.offsetget.php
     *
     * @param mixed $offset
     * @return bool|mixed|null|Simple
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     * @return bool
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.offsetunset.php
     *
     * @param mixed $name
     * @return bool
     */
    public function offsetUnset($name)
    {
        if (!$this->_readonly) {
            if (!is_array($this->_config)) {
                return false;
            }

            unset($this->_config[$name]);
            return true;
        }

        return false;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.count.php
     *
     * @return int
     */
    public function count()
    {
        return count(array_keys($this->_config));
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.toarray.php
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_config;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.isset.php
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_config);
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-simple.set.php
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * @param $key
     * @return bool|mixed|null|Simple
     * @throws \Exception
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param $values
     * @param bool $readonly
     * @return null|Simple
     * @throws \Exception
     */
    private function simpleInstance($values, ?bool $readonly)
    {
        switch (gettype($values)) {
            case 'array':
                $this->_config = $values;
                if ($readonly) {
                    $this->_readonly = (bool) $readonly;
                }
                return $this;
            default:
                yaf_trigger_error(TYPE_ERROR, "Invalid parameters provided, must be an array");
                return null;
        }
    }

    /**
     * @param $pzval
     * @return null|Simple
     * @throws \Exception
     */
    private function format($pzval)
    {
	    $readonly = $this->_readonly;
	    $ret = new Simple($pzval, $readonly);

	    return $ret;
    }
}
