<?php

namespace Yaf\Config;

use const YAF\ERR\TYPE_ERROR;

final class Ini implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * @var array
     */
    public $_config = [];

    /**
     * Ini constructor.
     *
     * @param string|array $filename 文件名称或者参数值
     * @param string $section
     * @throws \Exception
     */
    public function __construct($filename, $section = null)
    {
        return $this->instance($filename, $section);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function set(): bool
    {
        yaf_trigger_error(E_NOTICE, 'Yaf_Config_Ini is readonly');

        return false;
    }

    // TODO strok
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

            if (strchr($name, '.')) {
                $entry = $name;

                if ($seg = strtok($entry, '.')) {
                    while ($seg) {
                        $pzval = $properties[$seg];

                        if (!$pzval) {
                            return null;
                        }

                        $properties = $pzval;
                        $seg = strtok(".");
                    }
                }

            } else {
                $pzval = $properties[$name];
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

    public function current()
    {
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
     * @param null|string $filename
     * @param null|string $section_name
     * @return null|Ini
     * @throws \Exception
     */
    private function instance(?string $filename, ?string $section_name): ?Ini
    {
        if (is_array($filename)) {
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
                $section = $configs[$section_name];

                if (is_null($section)) {
                    yaf_trigger_error(E_ERROR, "There is no section '%s' in '%s'", $section_name, $ini_file);
                    return null;
                }
            }

            $this->_config = $configs;
            return $this;
        } else {
            yaf_trigger_error(TYPE_ERROR, "Invalid parameters provided, must be path of ini file");
            return null;
        }
    }

    private function format($pzval)
    {
        return $this->instance($pzval, null);
    }

    private function iniParse(string $ini_file)
    {
        $result = [];

        $parse_array = parse_ini_file($ini_file, true);
        // 基础配置解析
        foreach ($parse_array as $key => $value) {
            if (stripos($key, ':') !== false) {
                continue;
            }

            $result[$key] = $this->generateRecvParsePath($value);
        }

        // 继承配置解析
        foreach ($parse_array as $key => $value) {
            if (stripos($key, ':') === false) {
                continue;
            }

            list($newKey, $inheritedKey) = explode(':', $key)[0];
            if (isset($result[$inheritedKey])) {
                $result[$newKey] = array_merge((array) $result[$inheritedKey], $this->generateRecvParsePath($value));
            }
        }

        return $result;
    }

    private function generateRecvParsePath(string $path, $value): array
    {
        $result = [];
        $current = &$result;

        foreach (explode('.', $path) as $node) {
            $current[$node] = [];
            $current = &$current[$node];
        }

        $current = $value;
        unset($current);

        return $result;
    }
}
