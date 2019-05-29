<?php

namespace Yaf;

use Yaf\Config\Ini;
use Yaf\Config\Simple;
use const YAF\ERR\TYPE_ERROR;

/**
 * Yaf_Config_Abstract被设计在应用程序中简化访问和使用配置数据。
 * 它为在应用程序代码中访问这样的配置数据提供了一个基于用户接口的嵌入式对象属性。
 * 配置数据可能来自于各种支持等级结构数据存储的媒体。
 * Yaf_Config_Abstract实现了Countable, ArrayAccess 和 Iterator 接口。
 * 这样，可以基于Yaf_Config_Abstract对象使用count()函数和PHP语句如foreach, 也可以通过数组方式访问Yaf_Config_Abstract的元素.
 *
 * - Yaf_Config_INI : 存储在Ini文件的配置数据提供了适配器。
 * - Yaf_Config_Simple : 存储在PHP的数组中的配置数据提供了适配器。
 *
 * @link http://www.laruence.com/manual/yaf.class.config.html
 */
abstract class Config_Abstract
{
    /**
     * 配置实际的保存容器
     *
     * @access protected
     * @array
     */
    public $_config;

    /**
     * 表示配置是否容许修改, 对于Yaf_Config_Ini来说, 永远都是TRUE
     *
     * @access protected
     * @var bool
     */
    public $_readonly = true;

    /**
     * @link https://www.php.net/manual/en/yaf-config-abstract.get.php
     *
     * @return mixed
     */
    abstract function get();

    /**
     * @link https://www.php.net/manual/en/yaf-config-abstract.set.php
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    abstract function set($name, $value);

    /**
     * @link https://www.php.net/manual/en/yaf-config-abstract.readonly.php
     *
     * @return mixed
     */
    abstract function readonly();

    /**
     * @link https://www.php.net/manual/en/yaf-config-abstract.toarray.php
     *
     * @return array
     */
    abstract function toArray();

    // ================================================== 内部方法 ==================================================

    /**
     * @param string|array $arg1
     * @param string $arg2
     * @return null|Ini|Simple
     * @throws \Exception
     */
    private static function instance($arg1, $arg2)
    {
        if (is_string($arg1)) {
            if (pathinfo($arg1, PATHINFO_EXTENSION) === 'ini') {
                /** @var \Yaf\Config\Ini $instance */
                $instance = new Ini($arg1, $arg2);

                return $instance ?: null;
            }
            yaf_trigger_error(TYPE_ERROR, "Expects a path to *.ini configuration file as parameter");

            return null;
        } else if (is_array($arg1)) {
            $readonly = true;
            /** @var \Yaf\Config\Simple $instance */
            $instance = new Simple($arg1, $readonly);

            return $instance;
        }

        yaf_trigger_error(TYPE_ERROR, "Expects a string or an array as parameter");
        return null;

    }
}
