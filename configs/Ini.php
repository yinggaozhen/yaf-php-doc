<?php

namespace Yaf\Config;

use Yaf\Config_Abstract;
use const YAF\ERR\TYPE_ERROR;

class Ini extends Config_Abstract implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * Ini constructor.
     *
     * @param string|array $filename 文件名称或者参数值
     * @param string $section
     * @throws \Exception
     */
    public function __construct($filename, $section = null)
    {
        if (is_null($filename)) {
            $this->_config = [];
            return;
        }

        return $this->iniInstance($filename, $section);
    }

    /**
     * @inheritdoc
     * @return bool
     * @throws \Exception
     */
    public function set($name, $value): bool
    {
        \trigger_error('Yaf_Config_Ini is readonly', E_USER_WARNING);

        return false;
    }

    /**
     * @inheritdoc
     * @param string|null $name
     * @return $this|mixed|null|Ini
     * @throws \Exception
     */
    public function get(string $name = null)
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
     * @return bool
     */
    public function readonly()
    {
        return true;
    }

    public function count(): int
    {
        return count(array_keys($this->_config));
    }

    /**
     * @return bool|mixed|null|Ini
     * @throws \Exception
     */
    public function current()
    {
        return current($this->_config);
        $prop = $this->_config;

        $pzval = null;
        if (is_null($pzval = current($prop))) {
            return false;
        }

        if (is_array($pzval)) {
            $ret = $this->format($pzval);
            return $ret ?? null;
        } else {
            return $pzval;
    }
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
        $this->set();
    }

    public function offsetUnset($offset): bool
    {
        trigger_error(E_WARNING, "Yaf_Config_Ini is readonly");
        return false;
    }

    public function toArray()
    {
        $properties = $this->_config;

        return $properties;
    }

    public function __isset(string $name): bool
    {
        return (bool) array_key_exists($name, $this->_config);
    }

    /**
     * @param $name
     * @return mixed|null|Ini
     * @throws \Exception
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        return $this->set();
    }

    /**
     * @param null|string|array $filename
     * @param null|string $section_name
     * @param bool $create
     * @return null|Ini
     * @throws \Exception
     */
    private function iniInstance($filename, ?string $section_name, bool $create = false): ?Ini
    {
        if ($create) {
            return new Ini($filename, $section_name);
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
                $section = $configs[$section_name] ?? null;

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
     * @return null|Ini
     * @throws \Exception
     */
    private function format($pzval)
    {
        return $this->iniInstance($pzval, null, true);
    }

    private function iniParse(string $ini_file)
    {
        $result = [];
        $parse_array = parse_ini_file($ini_file, true, INI_SCANNER_TYPED);

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

    private function generateRecvParsePath(&$result, string $path, $value): array
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

class Yaf_Config_Ini extends Ini {}
