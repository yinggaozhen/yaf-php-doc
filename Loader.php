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

    /**
     * @param string $class_name
     * @return bool
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function autoload(string $class_name): bool
    {
        $file_name     = '';
        $file_name_len = 0;
        $ret = true;

        $separator_len = YAF_G('name_separator_len');
        $app_directory = YAF_G('directory') ?? null;
        $origin_classname = $class_name;

        $directory = null;
        do {
            if (!$class_name) {
                break;
            } else {
                $class_name = str_replace('\\', '_', ltrim($class_name, '\\'));
            }

            if (strncmp($class_name, self::YAF_LOADER_RESERVERD, self::YAF_LOADER_LEN_RESERVERD) == 0) {
                yaf_trigger_error(E_WARNING, "You should not use '%s' as class name prefix", self::YAF_LOADER_RESERVERD);
            }

            if (self::isCategory($class_name, self::YAF_LOADER_MODEL, self::YAF_LOADER_LEN_MODEL)) {
                /* this is a model class */
                $directory = sprintf("%s%c%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_MODEL_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_MODEL;

                if (YAF_G('name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_MODEL + $separator_len);
                }

                break;
            }

            if (self::isCategory($class_name, self::YAF_LOADER_PLUGIN, self::YAF_LOADER_LEN_PLUGIN)) {
                /* this is a plugin class */
                $directory = sprintf("%s%c%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_PLUGIN_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_PLUGIN;

                if (YAF_G('name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_PLUGIN + $separator_len);
                }

                break;
            }

            if (self::isCategory($class_name, self::YAF_LOADER_CONTROLLER, self::YAF_LOADER_LEN_CONTROLLER)) {
                /* this is a controller class */
                $directory = sprintf("%s%c%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_CONTROLLER_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_CONTROLLER;

                if (YAF_G('name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_CONTROLLER + $separator_len);
                }

                break;
            }

            if (YAF_G('st_compatible')
                && strncmp($class_name, self::YAF_LOADER_DAO, self::YAF_LOADER_LEN_DAO) == 0
                && strncmp($class_name, self::YAF_LOADER_SERVICE, self::YAF_LOADER_LEN_SERVICE) == 0
            ) {
                $directory = sprintf("%s/%s", $app_directory, self::YAF_MODEL_DIRECTORY_NAME);
            }

            $file_name_len = strlen($class_name);
            $file_name     = substr($class_name, 0, strlen($class_name));

        } while (0);

        if (!$app_directory && $directory) {
            trigger_error(sprintf("Couldn't load a MVC class unless an %s is initialized", Application::class));
            $ret = 0;
            goto out;
        }

        if (!YAF_G('use_spl_autoload')) {
            /** directory might be NULL since we passed a NULL */
            // TODO $directory是否为指针
            if ($this->internalAutoload($file_name, $file_name_len, $directory)) {
                $lc_classname = substr($origin_classname, 0, strlen($class_name));
                if (class_exists($lc_classname, false)) {
                    goto out;
                }

                yaf_trigger_error(E_STRICT, "Could not find class %s in %s", $class_name, $directory);
            } else {
                yaf_trigger_error(E_WARNING, "Failed opening script %s", $directory);
            }
            goto out;
        } else {
            $lower_case_name = strtolower(substr($origin_classname, 0, strlen($class_name)));
            if ($this->internalAutoload($file_name, $file_name_len, $directory) && class_exists($lower_case_name, false)) {
                goto out;
            }

            $ret = 0;
            goto out;
        }

out:
        return (bool) $ret;
    }

    /**
     * @param string $library
     * @param string $global_library
     * @return Loader|false
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function getInstance(?string $library = null, ?string $global_library = null)
    {
        $loader = self::_instance($library, $global_library);

        if ($loader) {
            return $loader;
        }

        return false;
    }

    /**
     * @param string $file
     * @return bool
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function import(string $file): bool
    {
        if (empty($file)) {
            return false;
        }

        if (realpath($file) !== $file) {
            $loader = self::getInstance(null, null);

            if (empty($loader)) {
                yaf_trigger_error(E_WARNING, "%s need to be initialize first", Loader::class);
                return false;
            } else {
                $property = new \ReflectionProperty($loader, '_library');
                $property->setAccessible(true);
                $library = $property->getValue();

                $file = sprintf("%s%c%s", $library, DIRECTORY_SEPARATOR, $file);
            }
        }

        $retval = array_key_exists($file, get_included_files());
        if ($retval) {
            return true;
        }

        $retval = self::loaderImport($file, 0);

        return (bool) $retval;
    }

    /**
     * @param string|array $namespaces
     * @return $this|bool
     * @throws \Exception
     */
    public function registerLocalNamespace($namespaces)
    {
        if (is_string($namespaces)) {
            if (self::registerNamespaceSingle($namespaces)) {
                return $this;
            }
        } else if (is_array($namespaces)) {
            if ($this->namespaceMulti($namespaces)) {
                return $this;
            }
        } else {
            yaf_trigger_error(E_WARNING, "Invalid parameters provided, must be a string, or an array");
        }

        return false;
    }

    /**
     * @return null|string
     */
    public function getLocalNamespace(): ?string
    {
        return YAF_G('local_namespaces') ?? null;
    }

    /**
     * @return true
     */
    public function clearLocalNamespace(): bool
    {
        // 源代码这里是用宏直接设置成null,这里做了一点小调整
        YAF_G('local_namespaces', 'NULL');

        return true;
    }

    /**
     * @param null|string $class_name
     * @return bool|int
     */
    public function isLocalName(?string $class_name): bool
    {
        if (empty($class_name) || !is_string($class_name)) {
            return false;
        }

        return (bool) $this->isLocalNamespace($class_name, strlen($class_name));
    }

    /**
     * @param string $library
     * @param bool $global
     * @return $this
     */
    public function setLibraryPath(string $library, bool $global = false): Loader
    {
        if (!$global) {
            $this->_library = $library;
        } else {
            $this->_global_library = $library;
        }

        return $this;
    }

    /**
     * @param bool $global
     * @return mixed
     */
    public function getLibraryPath(bool $global = false): string
    {
        if (!$global) {
            $library = $this->_library;
        } else {
            $library = $this->_global_library;
        }

        return $library;
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
    public const YAF_LIBRARY_DIRECTORY_NAME     = "library";
    public const YAF_CONTROLLER_DIRECTORY_NAME  = "controllers";
    public const YAF_PLUGIN_DIRECTORY_NAME      = "plugins";
    public const YAF_MODULE_DIRECTORY_NAME      = "modules";
    public const YAF_VIEW_DIRECTORY_NAME        = "views";
    public const YAF_MODEL_DIRECTORY_NAME       = "models";

    private const YAF_LOADER_RESERVERD          = "Yaf_";
    private const YAF_LOADER_LEN_RESERVERD      = 3;
    private const YAF_LOADER_CONTROLLER         = "Controller";
    private const YAF_LOADER_LEN_CONTROLLER     = 10;
    private const YAF_LOADER_MODEL              = "Model";
    private const YAF_LOADER_LEN_MODEL          = 5;
    private const YAF_LOADER_PLUGIN             = "Plugin";
    private const YAF_LOADER_LEN_PLUGIN         = 6;

    private const YAF_LOADER_DAO                = "Dao_";
    private const YAF_LOADER_LEN_DAO            = 4;
    private const YAF_LOADER_SERVICE            = "Service_";
    private const YAF_LOADER_LEN_SERVICE        = 8;

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
    private static function _instance(?string $library_path, ?string $global_path)
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
            yaf_trigger_error(E_WARNING, 'Missed library directory arguments');
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
            yaf_trigger_error(E_WARNING, 'Failed to register autoload function');
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
            // spl_autoload_register($autoload);
        } catch (\Exception $e) {
            yaf_trigger_error(E_WARNING, 'Unable to register autoload function autoload');
            return 0;
        }

        return 1;
    }

    /**
     * 实际为private
     *
     * @internal
     * @param string $file_name
     * @param int $name_len
     * @param string $directory
     * @return int
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function internalAutoload(?string $file_name, ?int $name_len, ?string &$directory): int
    {
        $buf = '';

        if (empty($directory)) {
            $loader = self::_instance(null, null);

            if (empty($loader)) {
                yaf_trigger_error(E_WARNING, '%s need to be initialize first', Loader::class);
                return 0;
            } else {
                if (self::isLocalNamespace($file_name, $name_len)) {
                    $property = new \ReflectionProperty($loader, '_library');
                    $property->setAccessible(true);
                    $library_dir = $property->getValue();
                } else {
                    $property = new \ReflectionProperty($loader, '_global_library');
                    $property->setAccessible(true);
                    $library_dir = $property->getValue($loader);
                }

                $library_path = $library_dir;
            }

            $buf .= $library_path;
        } else {
            $buf .= $directory;
        }

        $buf .= DIRECTORY_SEPARATOR;
        $buf .= str_replace('_', DIRECTORY_SEPARATOR, $file_name);

        if (YAF_G('lowcase_path')) {
            $buf = strtolower($buf);
        }

        $buf .= '.';
        $buf .= YAF_G('ext');

        $status = Loader::loaderImport($buf, 0);

        return $status;
    }

    /**
     * @param string $class_name
     * @param int $len
     * @return int
     */
    private static function isLocalNamespace(string $class_name, int $len): int
    {
        if (!YAF_G('local_namespaces')) {
            return 0;
        }

        $ns = YAF_G('local_namespaces');

        if (strstr($class_name, '_')) {
            $prefix = strstr($class_name, '_', true);
            $backup = strstr($class_name, '_');
        } else if (strstr($class_name, DIRECTORY_SEPARATOR)) {
            $prefix = strstr($class_name, DIRECTORY_SEPARATOR, true);
            $backup = strstr($class_name, DIRECTORY_SEPARATOR);
        } else {
            $prefix = $class_name;
            $backup = $len;
        }

        // TODO 看不懂

        return 0;
    }

    /**
     * @param string $path
     * @param int $use_path
     * @return int
     */
    private static function loaderImport(string $path, int $use_path): int
    {
        if (!@realpath($path)) {
            return 0;
        }

        include $path;
    }

    /**
     * @param string $prefix
     * @return int
     */
    private static function registerNamespaceSingle($prefix): int
    {
        if (YAF_G('local_namespaces')) {
            YAF_G('local_namespaces', YAF_G('local_namespaces') . ';' . $prefix);
        } else {
            YAF_G('local_namespaces', $prefix);
        }

        return 1;
    }

    /**
     * @param string[] $prefixes
     * @return int
     */
    private static function namespaceMulti(array $prefixes): int
    {
        foreach ($prefixes as $prefix) {
            if (is_string($prefix)) {
                self::registerNamespaceSingle($prefix);
            }
        }

        return 1;
    }

}
