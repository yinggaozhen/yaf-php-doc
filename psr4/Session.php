<?php

namespace Yaf;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * @link https://www.php.net/manual/en/class.yaf-session.php
 */
final class Session implements Iterator, ArrayAccess, Countable
{
    /**
     * @var $this
     */
    protected static $_instance = null;

    /**
     * @var array
     */
    protected $_session = null;

    /**
     * @var bool
     */
    protected $_started = false;

    /**
     * Session constructor.
     *
     * @link https://www.php.net/manual/en/yaf-session.construct.php
     */
    private function __construct()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.getinstance.php
     *
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
     * @link https://www.php.net/manual/en/yaf-session.start.php
     *
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
     * @link https://www.php.net/manual/en/yaf-session.get.php
     *
     * @param string|int|null $name
     * @return array|mixed|null
     */
    public function get($name = null)
    {
        $session = $this->_session;

        if (empty($name)) {
            return $session;
        }

        return $this->_session[$name] ?? null;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.set.php
     *
     * @param string|int $name
     * @param mixed $value
     * @return $this
     */
    public function set($name, $value)
    {
        $this->_session[$name] = $value;

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.del.php
     *
     * @param string|int $name
     * @return $this
     */
    public function del($name)
    {
        unset($this->_session[$name]);

        return $this;
    }

    public function clear()
    {
        $this->_session = [];
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.has.php
     *
     * @param string|int $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.current.php
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.next.php
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.key.php
     *
     * @return int|string
     */
    public function key()
    {
        return key($this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.valid.php
     *
     * @return bool
     */
    public function valid()
    {
        $key = key($this->_session);

       return isset($key);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.rewind.php
     */
    public function rewind()
    {
        reset($this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.offsetexists.php
     *
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.offsetget.php
     *
     * @param string|int $offset
     * @return array|mixed|null
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.offsetset.php
     *
     * @param string|int $offset
     * @param mixed $value
     * @return Session
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.offsetunset.php
     *
     * @param string|int $offset
     * @return Session
     */
    public function offsetUnset($offset)
    {
        return $this->del($offset);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.count.php
     *
     * @return int
     */
    public function count()
    {
        if (empty($this->_session)) {
            return 0;
        }

        return count($this->_session);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.get.php
     *
     * @param string|int $name
     * @return array|mixed|null
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.isset.php
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.unset.php
     *
     * @param string|int $name
     * @return Session
     */
    public function __unset($name)
    {
        return $this->del($name);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.sleep.php
     */
    private function __sleep()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-session.wakeup.php
     */
    private function __wakeup()
    {
    }
}
