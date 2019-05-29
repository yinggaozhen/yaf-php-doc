<?php

namespace Yaf;

/**
 * 对象注册表(或称对象仓库)是一个用于在整个应用空间(application space)内存储对象和值的容器.
 * 通过把对象存储在其中, 我们可以在整个项目的任何地方使用同一个对象.
 * 这种机制相当于一种全局存储. 我们可以通过Yaf_Registry类的静态方法来使用对象注册表.
 * 另外,由于该类是一个数组对象,你可以使用数组形式来访问其中的类方法.
 *
 * @link http://www.laruence.com/manual/yaf.class.registry.html
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
     * 获取注册表中寄存的项
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.registry.get.html
     *
     * @param string $name 要获取的项的名字
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
     * 往全局注册表添加一个新的项
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.registry.set.html
     *
     * @param string $name 要注册的项的名字
     * @param mixed $value 要注册的项的值
     * @return bool
     */
    public static function set($name, $value)
    {
        if (!is_string($name)) {
            return false;
        }

        self::$_entries[$name] = $value;
        return true;
    }

    /**
     * 查询某一项目是否存在于注册表中
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.registry.has.html
     *
     * @param string $name 要查询的项的名字
     * @return bool
     */
    public static function has($name)
    {
        return array_key_exists($name, self::$_entries);
    }

    /**
     * 删除存在于注册表中的名为$name的项目
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.registry.del.html
     *
     * @param string $name 要删除的项的名字
     * @return bool
     */
    public static function del($name)
    {
        unset(self::$_entries[$name]);
        return true;
    }
}
