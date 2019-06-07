<?php

use Yaf\Config\Ini;
use Yaf\Config_Abstract;
use const YAF\ERR\TYPE_ERROR;

/**
 * \Yaf\Config\Ini
 * 能使开发人员能够以熟悉的ini格式存储配置数据，并使用嵌套的对象属性语法在应用程序中读取这些数据。
 * ini格式专门用于提供配置数据键层次结构和配置数据部分之间继承的能力。
 *
 * - 其中字符（“.”）作为分隔键，支持配置数据层次结构。
 * - 而字符（“：”）作为继承/扩展符号，方法是 <子节点名>:<父节点名>
 *
 * @link http://www.laruence.com/manual/yaf.class.config.html#yaf.class.config.ini
 */
final class Yaf_Config_Ini extends Config_Abstract implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * Ini constructor.
     *
     * @link http://www.php.net/manual/en/yaf-config-ini.construct.php
     *
     * @param string|array $filename 文件名称或者参数值
     * @param string $section 所要选取的ini部分
     * @throws \Exception
     */
    public function __construct($filename, $section = null)
    {
        if (is_null($filename)) {
            $this->_config = [];
        } else {
            $this->iniInstance($filename, $section);
        }
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.set.php
     *
     * @inheritdoc
     * @return bool
     */
    public function set($name, $value)
    {
        \trigger_error('Yaf_Config_Ini is readonly', E_USER_WARNING);

        return false;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.get.php
     *
     * @inheritdoc
     * @param string|null $name
     * @return $this|mixed|null|Ini
     * @throws \Exception
     */
    public function get($name = null)
    {
        $pzval = null;

        if (is_null($name)) {
            return $this;
        } else {
            $properties = $this->_config;

            if (!is_array($properties)) {
                return null;
            }

            if (stripos($name, '.') !== false) {
                $pzval = $this->_config;

                foreach (explode('.', $name) as $value) {
                    if (!isset($pzval[$value])) {
                        return null;
                    }

                    $pzval = $pzval[$value];
                }
            } else {
                $pzval = $properties[$name] ?? null;
                if (is_null($pzval)) {
                    return $pzval;
                }
            }

            if (is_array($pzval)) {
                $ret = $this->format($pzval);

                return $ret ?? null;
            } else {
                return $pzval;
            }
        }
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.readonly.php
     *
     * @return bool
     */
    public function readonly()
    {
        return true;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.count.php
     *
     * @return int
     */
    public function count()
    {
        return count(array_keys($this->_config));
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.current.php
     *
     * @return bool|mixed|null|Ini
     * @throws \Exception
     */
    public function current()
    {
        return current($this->_config);
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

    public function rewind(): void
    {
        reset($this->_config);
    }

    public function offsetExists($offset)
    {
        return isset($this[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed|null|Ini
     * @throws \Exception
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return bool|void
     * @throws \Exception
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        trigger_error('Yaf_Config_Ini is readonly', E_USER_WARNING);
        return false;
    }

    public function toArray()
    {
        $properties = $this->_config;

        return $properties;
    }

    /**
     * @link http://www.php.net/manual/en/yaf-config-ini.isset.php
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        return (bool) array_key_exists($name, $this->_config);
    }

    /**
     * @see \Yaf\Config\Ini::get()
     * @link http://www.php.net/manual/en/yaf-config-ini.get.php
     *
     * @param string $name
     * @return mixed|null|Ini
     * @throws \Exception
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @see \Yaf\Config\Ini::set()
     * @link http://www.php.net/manual/en/yaf-config-ini.set.php
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    // ================================================== 内部方法 ==================================================

    /**
     * @param null|string|array $filename
     * @param null|string $section_name
     * @param bool $create
     * @return null|Yaf_Config_Ini
     * @throws \Exception
     */
    private function iniInstance($filename, $section_name, $create = false)
    {
        if ($create) {
            return new Yaf_Config_Ini($filename, $section_name);
        } else if (is_array($filename)) {
            $this->_config = $filename;
            return $this;
        } else if ($filename && is_string($filename)) {
            $ini_file = $filename;

            if ($section_name) {
                YAF_G('section_name', $section_name);
            } else {
                YAF_G('section_name', 'NULL');
            }

            if (is_readable($filename)) {
                $configs = $this->iniParse($ini_file);

                if (empty($configs)) {
                    yaf_trigger_error(E_ERROR, "Parsing ini file '%s' failed", $ini_file);
                    return null;
                }
            } else {
                yaf_trigger_error(E_ERROR, "Unable to find config file '%s'", $ini_file);
                return null;
            }

            if ($section_name && is_string($section_name)) {
                $section = $configs[$section_name] ;

                if (is_null($section)) {
                    yaf_trigger_error(E_ERROR, "There is no section '%s' in '%s'", $section_name, $ini_file);
                    return null;
                }
                $configs = $section;
            }

            $this->_config = $configs;
            return $this;
        } else {
            yaf_trigger_error(TYPE_ERROR, "Invalid parameters provided, must be path of ini file");
            return null;
        }
    }

    /**
     * @param $pzval
     * @return null|Yaf_Config_Ini
     * @throws \Exception
     */
    private function format($pzval)
    {
        return $this->iniInstance($pzval, null, true);
    }

    /**
     * @param string $ini_file
     * @return array|null
     * @throws \Exception
     */
    private function iniParse($ini_file)
    {
        $result = [];
        try {
            $parse_array = parse_ini_file($ini_file, true, INI_SCANNER_TYPED);
        } catch (\Exception $e) {
            yaf_trigger_error(E_ERROR, "Argument is not a valid ini file '%s'", $ini_file);
            return null;
        }


        // 基础配置解析
        foreach ($parse_array as $key => $iniInfo) {
            if (stripos($key, ':') !== false) {
                list($newKey) = $this->parseKeyGroup($key);
                $result[$newKey] = null;

                continue;
            }

            $result[$key] = [];

            foreach ($iniInfo as $path => $value) {
                $this->generateRecvParsePath($result[$key], $path, $value);
            }
        }

        // 继承配置解析
        foreach ($parse_array as $key => $iniInfo) {
            if (stripos($key, ':') === false) {
                continue;
            }

            // TODO 多重继承
            list($newKey, $inheritedKey) = $this->parseKeyGroup($key);
            if (isset($result[$inheritedKey])) {
                $result[$newKey] = $result[$inheritedKey];
            }

            foreach ($iniInfo as $path => $value) {
                $this->generateRecvParsePath($result[$newKey], $path, $value);
            }
        }

        return $result;
    }

    /**
     * @param $result
     * @param string $path
     * @param $value
     * @return mixed
     */
    private function generateRecvParsePath(&$result, $path, $value)
    {
        $current = &$result;

        foreach (explode('.', $path) as $node) {
            if (!isset($current[$node])) {
                $current[$node] = [];
            }

            $current = &$current[$node];
        }

        $current = $value;
        unset($current);

        return $result;
    }

    private function parseKeyGroup($key)
    {
        return array_map(function($value) {
            return trim($value);
        }, explode(':', $key));
    }
}
