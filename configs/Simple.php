<?php

namespace Yaf\Config;

use const YAF\ERR\TYPE_ERROR;

final class Simple implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * TODO 其实是public
     *
     * @var bool
     */
    public $_readonly = false;

    /**
     * TODO 其实是protected
     *
     * @var array
     */
    public $_config = [];

    /**
     * Simple constructor.
     * @param $values
     * @param null $readonly
     * @throws \Exception
     */
    public function __construct($values, $readonly = null)
    {
        if ($readonly !== true) {
            $values = (array) $values;
        }

        return $this->instance($values, $readonly);
    }

    /**
     * @param string|null $name
     * @return $this|bool|mixed|null
     * @throws \Exception
     */
    public function get(string $name = null)
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

                if (!$ret) {
                    return null;
                }
            } else {
                return $pzval;
            }
        }

        return false;
    }

    public function set($name, $value): bool
    {
        $readonly = $this->_readonly;

        if ($readonly === false) {
            $this->_config[$name] = $value;
            return true;
        }

        return false;
    }

    public function readonly(): bool
    {
        return (bool) $this->_readonly;
    }

    /**
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

    public function next()
    {
        return next($this->_config);
    }

    public function key()
    {
        return key($this->_config);
    }

    public function valid()
    {
        return !is_null(key($this->_config));
    }

    public function rewind()
    {
        reset($this->_config);
    }

    public function offsetExists($offset)
    {
        return $this[$offset];
    }

    /**
     * @param mixed $offset
     * @return bool|mixed|null|Simple
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($name): bool
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

    public function count(): int
    {
        return count(array_keys($this->_config));
    }

    public function toArray()
    {
        return $this->_config;
    }

    public function __isset($name): bool
    {
        return array_key_exists($name, $this->_config);
    }

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

    /**
     * @param $values
     * @param bool $readonly
     * @return null|Simple
     * @throws \Exception
     */
    private function instance($values, ?bool $readonly): ?Simple
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
