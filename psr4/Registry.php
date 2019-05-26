<?php

namespace Yaf;

/**
 * @link https://www.php.net/manual/en/class.yaf-registry.php
 */
final class Registry
{
    /**
     * @var array
     */
    protected static $_entries = [];

    /**
     * Registry constructor
     *
     * @link https://www.php.net/manual/en/yaf-registry.construct.php
     */
    private function __construct()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-registry.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-registry.get.php
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        $entries = self::$_entries;

        if (!empty($entries) && is_array($entries)) {
            return $entries[$name] ?: null;
        }

        return null;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-registry.set.php
     *
     * @param string $name
     * @param $value
     * @return bool
     */
    public static function set($name, $value)
    {
        self::$_entries[$name] = $value;
        return true;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-registry.has.php
     *
     * @param string $name
     * @return bool
     */
    public static function has($name)
    {
        return array_key_exists($name, self::$_entries);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-registry.del.php
     *
     * @param string $name
     * @return bool
     */
    public static function del($name)
    {
        unset(self::$_entries[$name]);
        return true;
    }
}
