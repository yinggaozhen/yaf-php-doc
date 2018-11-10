<?php

namespace Yaf;

final class Loader
{
    protected $_library;
    protected $_global_library;
    protected static $_instance;

    private function __construct()
    {
    }

    public function autoload(string $class_name)
    {
        $file_name_len = 0;
        $ret = true;

        $separator_len = YAF_G('name_separator_len');
        $app_directory = YAF_G('directory')? YAF_G('directory') : null;
        $origin_classname = $class_name;

        do {
            if (!$class_name) {
                break;
            } else {
                $class_name = str_replace('\\', '_', ltrim($class_name, '\\'));
            }

            if (strncmp($class_name, self::YAF_LOADER_RESERVERD, self::YAF_LOADER_LEN_RESERVERD) == 0) {
                trigger_error(sprintf("You should not use '%s' as class name prefix", self::YAF_LOADER_RESERVERD), E_WARNING);
            }

            // TODO
            if (self::isCategory($class_name, self::YAF_LOADER_MODEL, self::YAF_LOADER_LEN_MODEL)) {
                $directory = sprintf("%s%c%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_MODEL_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_MODEL;

                if (YAF_G('name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_MODEL + $separator_len);
                }

                break;
            }

            // TODO 从这里开始哟 2018-11-10 20:14


        } while (0);
    }

    /**
     * @param null $library
     * @param null $global_library
     * @return Loader|false
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function getInstance($library = null, $global_library = null)
    {
        $loader = self::_instance($library, $global_library);

        if ($loader) {
            return $loader;
        }

        return false;
    }

    public static function import()
    {

    }

    public function registerLocalNamespace()
    {

    }

    public function getLocalNamespace()
    {

    }

    public function clearLocalNamespace()
    {

    }

    public function isLocalName()
    {

    }

    public function setLibraryPath()
    {

    }

    public function getLibraryPath()
    {

    }

    private function __clone()
    {

    }

    private function __sleep()
    {

    }

    private function __wakeup()
    {

    }

    // ================================================== 内部常量 ==================================================

    /**
     * @internal
     */
    public const YAF_LIBRARY_DIRECTORY_NAME    = "library";
    public const YAF_CONTROLLER_DIRECTORY_NAME = "controllers";
    public const YAF_PLUGIN_DIRECTORY_NAME     = "plugins";
    public const YAF_MODULE_DIRECTORY_NAME     = "modules";
    public const YAF_VIEW_DIRECTORY_NAME       = "views";
    public const YAF_MODEL_DIRECTORY_NAME      = "models";

    private const YAF_LOADER_RESERVERD	       = "Yaf_";
    private const YAF_LOADER_LEN_RESERVERD     = 3;
    private const YAF_LOADER_CONTROLLER		   = "Controller";
    private const YAF_LOADER_LEN_CONTROLLER	   = 10;
    private const YAF_LOADER_MODEL			   = "Model";
    private const YAF_LOADER_LEN_MODEL		   = 5;
    private const YAF_LOADER_PLUGIN			   = "Plugin";
    private const YAF_LOADER_LEN_PLUGIN		   = 6;

    // ================================================== 内部方法 ==================================================

    /**
     * @param string $class_name
     * @param string $category
     * @param int $category_len
     * @return int
     */
    private static function isCategory(string $class_name, string $category, int $category_len): int
    {
        $class_name_len = strlen($class_name);

        $separator_len  = YAF_G('name_separator_len');

        if (YAF_G('name_suffix')) {
            if ($class_name_len > $category_len && strncmp(substr($class_name, $class_name_len - $category_len), $category, $category_len) == 0) {
                if (!$separator_len || !strncmp(substr($class_name, $class_name_len - $category_len - $separator_len), YAF_G('name_separator'), $separator_len)) {
                    return 1;
                }
            }
        } else {
            if (strncmp($class_name, $category, $category_len) == 0) {
                if (!$separator_len ||
                    strncmp(substr($class_name, $category_len), YAF_G('name_separator'), $separator_len) == 0) {
                    return 1;
                }
            }
        }

        return 0;
    }

    /**
     * @param string $library_path
     * @param string $global_path
     * @throws \ReflectionException
     * @return mixed
     * @throws \Exception
     */
    private static function _instance(string $library_path, string $global_path)
    {
        $instance = self::$_instance;

        if (is_object($instance)) {
            if ($library_path) {
                $property = new \ReflectionProperty($instance, '_library');
                $property->setAccessible(true);
                $property->setValue($library_path);
            }

            if ($global_path) {
                $property = new \ReflectionProperty($instance, '_global_library');
                $property->setAccessible(true);
                $property->setValue($global_path);
            }

            return $instance;
        }

        $instance = new self();
        if (!$global_path && !$library_path) {
            trigger_error('Missed library directory arguments', E_WARNING);
            return null;
        }

        if ($library_path && $global_path) {
            $instance->_library = $library_path;
            $instance->_global_library = $global_path;
        } else if (!$global_path) {
            $instance->_library = $library_path;
            $instance->_global_library = $library_path;
        } else {
            $instance->_library = $global_path;
            $instance->_global_library = $global_path;
        }

        self::$_instance = $instance;

        if (!self::loaderRegister($instance)) {
            trigger_error('Failed to register autoload function', E_WARNING);
        }

        return $instance;
    }

    /**
     * @param Loader $loader
     * @return int
     * @throws \Exception
     */
    private static function loaderRegister(Loader $loader): int
    {
        $autoload = [];
        $method = 'autoload';

        $autoload[] = $loader;
        $autoload[] = $method;

        try {
            spl_autoload_register($autoload);
        } catch (\Exception $e) {
            trigger_error(sprintf('Unable to register autoload function autoload'), E_WARNING);
            return 0;
        }

        return 1;
    }
}