<?php

namespace Yaf;

final class Registry
{
    /**
     * @var array
     */
    protected static $_entries;

    protected $_instance;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function get(string $name)
    {
        $entries = self::$_entries;

        if (!empty($entries) && is_array($entries)) {
            return $entries[$name] ?: null;
        }

        return null;
    }

    /**
     * @param string $name
     * @param $value
     * @return bool
     */
    public static function set(string $name, $value): bool
    {
        self::$_entries[$name] = $value;
        return true;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function has(string $name): bool
    {
        return array_key_exists($name, self::$_entries);
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function del(string $name): bool
    {
        unset(self::$_entries[$name]);
        return true;
    }
}