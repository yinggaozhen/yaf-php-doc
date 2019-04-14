<?php

namespace Yaf\Config;

final class Ini implements \Countable, \Iterator, \ArrayAccess
{
    /**
     * @var array
     */
    private $_config = [];

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
    public function set()
    {
        yaf_trigger_error(E_NOTICE, 'Yaf_Config_Ini is readonly');

        return false;
    }

    /**
     * @return bool
     */
    public function readonly()
    {
        return true;
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function current()
    {
        // TODO: Implement current() method.
    }

    public function next()
    {
        // TODO: Implement next() method.
    }

    public function key()
    {
        // TODO: Implement key() method.
    }

    public function valid()
    {
        // TODO: Implement valid() method.
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    private function instance(string $filename, string $section_name): ?Ini
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
                try {
                    $configs = parse_ini_file($ini_file);
                } catch (\Exception $e) {
                    yaf_trigger_error(E_ERROR, "Parsing ini file '%s' failed", $ini_file);
                    return null;
                }
            } else {
                yaf_trigger_error(E_ERROR, "Unable to find config file '%s'", $ini_file);
                return null;
            }

            if ($section_name && is_string($section_name)) {
                $section = $configs['section_name'];

                if (is_null($section)) {
                    yaf_trigger_error(E_ERROR, "There is no section '%s' in '%s'", $section_name, $ini_file);
                    return null;
                }
            }

            $this->_config = $configs;
            return $this;
        } else {
            yaf_trigger_error(YAF_ERR_TYPE_ERROR, "Invalid parameters provided, must be path of ini file");
            return null;
        }
    }
}
