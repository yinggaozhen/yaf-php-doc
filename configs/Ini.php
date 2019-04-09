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
        } else if (is_string($filename)) {
            // TODO 从这里开始
            /data/github/yaf/configs/yaf_config_ini.c
            if (is_file($filename)) {
            } else {
                yaf_trigger_error(E_NOTICE, 'Yaf_Config_Ini is readonly');
            }
        }
    }
}
