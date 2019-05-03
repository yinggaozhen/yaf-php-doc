<?php

namespace Yaf;

use ArrayAccess;
use Countable;
use Iterator;

final class Session implements Iterator, ArrayAccess, Countable
{
    protected static $_instance = null;

    /**
     * @var array
     */
    protected $_session = null;

    /**
     * @var bool
     */
    protected $_started = false;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return null|Session
     * @throws \Exception
     */
    public static function getInstance()
    {
        $instance = self::$_instance;

        if (is_null($instance)) {
            $instance = new Session();
            $instance->start();

            if (!isset($_SESSION) || !is_array($_SESSION)) {
                trigger_error("Attempt to start session failed", E_USER_WARNING);
                return null;
            }

            self::$_instance = $instance;
        }

        return $instance;
    }

    /**
     * @return $this
     */
    public function start()
    {
        if ($this->_started || session_status() === PHP_SESSION_ACTIVE) {
            $this->_started = 1;
            // 这里赋值其实是在instance
            $this->_session = &$_SESSION;

            return $this;
        }

        session_start();

        $this->_started = 1;
        // 这里赋值其实是在instance
        $this->_session = &$_SESSION;

        return $this;
    }

    /**
     * @param string|null $name
     * @return array|mixed|null
     */
    public function get(string $name = null)
    {
        $session = $this->_session;

        if (empty($name)) {
            return $session;
        }

        return $this->_session[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function set(string $name, $value)
    {
        $this->_session[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function del($name)
    {
        unset($this->_session[$name]);

        return $this;
    }

    public function clear(): void
    {
        $this->_session = [];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->_session);
    }

    public function current()
    {
        return current($this->_session);
    }

    public function next()
    {
        return next($this->_session);
    }

    /**
     * @return int|string
     */
    public function key()
    {
        return key($this->_session);
    }

    public function valid(): bool
    {
        $key = key($this->_session);

       return isset($key);
    }

    public function rewind(): void
    {
        reset($this->_session);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->del($offset);
    }

    public function count(): int
    {
        if (empty($this->_session)) {
            return 0;
        }

        return count($this->_session);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }

    public function __unset($name)
    {
        return $this->del($name);
    }

    private function __sleep()
    {
    }

    private function __wakeup()
    {
    }
}
