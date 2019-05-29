<?php

use Yaf\Application;
use Yaf\Loader;

/**
 * Yaf_Loader类为Yaf提供了自动加载功能, 它根据类名中包含的路径信息实现类的定位和自动加载.
 * Yaf_Loader也提供了对传统的require_once的替代方案, 相比传统的require_once, 因为舍弃对require的支持, 所以性能能有一丁点小优势.
 *
 * @link http://www.laruence.com/manual/yaf.class.loader.html
 */
class Yaf_Loader
{
    /**
     * 本地(自身)类加载路径, 一般的, 属性的值来自配置文件中的ap.library
     *
     * @var string
     */
    protected $_library;

    /**
     * 全局类加载路径, 一般的, 属性的值来自php.ini中的ap.library
     *
     * @var string
     */
    protected $_global_library;

    /**
     * Yaf_Loader实现了单利模式, 一般的它由Yaf_Application负责初始化. 此属性保存当前实例
     *
     * @var \Yaf\Loader
     */
    protected static $_instance;

    /**
     * Loader constructor.
     *
     * @link https://www.php.net/manual/en/yaf-loader.construct.php
     */
    private function __construct()
    {
    }

    /**
     * 载入一个类, 这个方法被Yaf用作自动加载类的方法, 当然也可以手动调用.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.autoload.html
     *
     * @param string $class_name 要载入的类名, 类名必须包含路径信息, 也就是下划线分隔的路径信息和类名.
     * @return bool 成功返回TRUE
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function autoload($class_name)
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
                trigger_error(sprintf("You should not use '%s' as class name prefix", self::YAF_LOADER_RESERVERD), E_USER_WARNING);
            }

            if (self::isCategory($class_name, self::YAF_LOADER_MODEL, self::YAF_LOADER_LEN_MODEL)) {
                /* this is a model class */
                $directory = sprintf("%s%s%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_MODEL_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_MODEL;

                if (YAF_G('yaf.name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_MODEL + $separator_len);
                }

                break;
            }

            if (self::isCategory($class_name, self::YAF_LOADER_PLUGIN, self::YAF_LOADER_LEN_PLUGIN)) {
                /* this is a plugin class */
                $directory = sprintf("%s%s%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_PLUGIN_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_PLUGIN;

                if (YAF_G('yaf.name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_PLUGIN + $separator_len);
                }

                break;
            }

            if (self::isCategory($class_name, self::YAF_LOADER_CONTROLLER, self::YAF_LOADER_LEN_CONTROLLER)) {
                /* this is a controller class */
                $directory = sprintf("%s%s%s", $app_directory, DIRECTORY_SEPARATOR, self::YAF_CONTROLLER_DIRECTORY_NAME);
                $file_name_len = strlen($class_name) - $separator_len - self::YAF_LOADER_LEN_CONTROLLER;

                if (YAF_G('yaf.name_suffix')) {
                    $file_name = substr($class_name, 0, $file_name_len);
                } else {
                    $file_name = substr($class_name, self::YAF_LOADER_LEN_CONTROLLER + $separator_len);
                }

                break;
            }

            if (YAF_G('yaf.st_compatible')
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

        // TODO 设置有问题,取值为null,实际为0. from 003
        if (!YAF_G('yaf.use_spl_autoload')) {
            /** directory might be NULL since we passed a NULL */
            if (Loader::_internalAutoload($file_name, $file_name_len, $directory)) {
                $lc_classname = substr($origin_classname, 0, strlen($class_name));
                if (class_exists($lc_classname, false)) {
                    goto out;
                }

                trigger_error(sprintf("Could not find class %s in %s", $class_name, $directory), E_STRICT);
            } else {
                trigger_error(sprintf("Failed opening script %s", $directory), E_USER_WARNING);
            }
            goto out;
        } else {
            $lower_case_name = strtolower(substr($origin_classname, 0, strlen($class_name)));
            if (Loader::_internalAutoload($file_name, $file_name_len, $directory) && class_exists($lower_case_name, false)) {
                goto out;
            }

            $ret = 0;
            goto out;
        }

out:
        return (bool) $ret;
    }

    /**
     * 获取当前的Yaf_Loader实例
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.getInstance.html
     *
     * @param string $library 本地(自身)类库目录, 如果留空, 则返回已经实例化过的Yaf_Loader实例
     * @param string $global_library 全局类库目录, 如果留空则会认为和$local_library_directory相同.
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

    /**
     * 导入一个PHP文件, 因为Yaf_Loader::import只是专注于一次包含, 所以要比传统的require_once性能好一些
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.import.html
     *
     * @param string $file 要载入的文件路径, 可以为绝对路径和相对路径. 如果为相对路径, 则会以应用的本地类目录(ap.library)为基目录.
     * @return bool 成功返回TRUE, 失败返回FALSE.
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function import($file)
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
                $library = $property->getValue($loader);

                $file = sprintf("%s%s%s", $library, DIRECTORY_SEPARATOR, $file);
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
     * 注册本地类前缀, 是的对于以这些前缀开头的本地类, 都从本地类库路径中加载.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.registerLocalNamespace.html
     *
     * @param string|array $namespaces 字符串或者是数组格式的类名前缀, 不包含前缀后面的下划线.
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
            trigger_error('Invalid parameters provided, must be a string, or an array', E_USER_WARNING);
        }

        return false;
    }

    /**
     * 获取当前已经注册的本地类前缀
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.getLocalNamespace.html
     *
     * @return null|string
     */
    public function getLocalNamespace()
    {
        return YAF_G('local_namespaces') ?? null;
    }

    /**
     * 清除已注册的本地类前缀
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.clearLocalNamespace.html
     *
     * @return bool 成功返回TRUE, 失败返回FALSE
     */
    public function clearLocalNamespace()
    {
        // 源代码这里是用宏直接设置成null,这里做了一点小调整
        YAF_G('local_namespaces', 'NULL');

        return true;
    }

    /**
     * 判断一个类, 是否是本地类.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.loader.isLocalName.html
     *
     * @param null|string $class_name <p>
     * 字符串的类名, 本方法会根据下划线分隔截取出类名的第一部分, 然后在Yaf_Loader的_local_ns中判断是否存在, 从而确定结果.
     * </p>
     * @return bool|int
     */
    public function isLocalName($class_name)
    {
        if (empty($class_name) || !is_string($class_name)) {
            return false;
        }

        return (bool) $this->isLocalNamespace($class_name);
    }

    /**
     * @link https://www.php.net/manual/en/yaf-loader.setlibrarypath.php
     *
     * @param string $library
     * @param bool $global
     * @return $this
     */
    public function setLibraryPath($library, $global = false)
    {
        if (!$global) {
            $this->_library = $library;
        } else {
            $this->_global_library = $library;
        }

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-loader.getlibrarypath.php
     *
     * @param bool $global
     * @return string
     */
    public function getLibraryPath($global = false)
    {
        if (!$global) {
            $library = $this->_library;
        } else {
            $library = $this->_global_library;
        }

        return $library;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-loader.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-loader.sleep.php
     */
    private function __sleep()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-loader.wakeup.php
     */
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
    private static function isCategory($class_name, $category, $category_len)
    {
        $class_name_len = strlen($class_name);
        $separator_len  = YAF_G('name_separator_len');

        if (YAF_G('yaf.name_suffix')) {
            if ($class_name_len > $category_len && strncmp(substr($class_name, $class_name_len - $category_len), $category, $category_len) == 0) {
                if (!$separator_len || !strncmp(substr($class_name, $class_name_len - $category_len - $separator_len), YAF_G('yaf.name_separator'), $separator_len)) {
                    return 1;
                }
            }
        } else {
            if (strncmp($class_name, $category, $category_len) == 0) {
                if (!$separator_len ||
                    strncmp(substr($class_name, $category_len), YAF_G('yaf.name_separator'), $separator_len) == 0) {
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
    private static function _instance($library_path, $global_path)
    {
        $instance = self::$_instance;

        if (is_object($instance)) {
            if ($library_path) {
                $property = new \ReflectionProperty($instance, '_library');
                $property->setAccessible(true);
                $property->setValue($instance, $library_path);
            }

            if ($global_path) {
                $property = new \ReflectionProperty($instance, '_global_library');
                $property->setAccessible(true);
                $property->setValue($instance, $global_path);
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
     * @param Yaf_Loader $loader
     * @return int
     * @throws \Exception
     */
    private static function loaderRegister(Yaf_Loader $loader)
    {
        $method = 'autoload';
        $autoload = [$loader, $method];

        try {
            // TODO
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
     * @param string $file_name
     * @param string $directory
     * @return int
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function _internalAutoload($file_name, &$directory)
    {
        $buf = '';

        if (empty($directory)) {
            $loader = self::_instance(null, null);

            if (empty($loader)) {
                yaf_trigger_error(E_WARNING, '%s need to be initialize first', Loader::class);
                return 0;
            } else {
                if (self::isLocalNamespace($file_name)) {
                    $property = new \ReflectionProperty($loader, '_library');
                    $property->setAccessible(true);
                    $library_dir = $property->getValue($loader);
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

        if (YAF_G('yaf.lowcase_path')) {
            $buf = strtolower($buf);
        }

        $buf .= '.';
        $buf .= YAF_G('ext');

        $directory = $buf;
        $status = Yaf_Loader::loaderImport($buf, 0);

        return $status;
    }

    /**
     * @param string $class_name
     * @return int
     */
    private static function isLocalNamespace($class_name)
    {
        if (!YAF_G('local_namespaces')) {
            return 0;
        }

        $ns = (string) YAF_G('local_namespaces');

        $class_name = ltrim($class_name, '\\');

        if (($pos = strpos($class_name, '_')) !== false) {
            $prefix = substr($class_name, 0, $pos);
            substr($class_name, $pos + 1);
        } else if (($pos = strpos($class_name, '\\')) !== false) {
            $prefix = substr($class_name, 0, $pos);
            substr($class_name, $pos + 1);
        } else {
            $prefix = $class_name;
        }

        if ($prefix == '') {
            return 0;
        }

        $prefixes = explode(PATH_SEPARATOR, $ns);
        if (in_array($prefix, $prefixes)) {
            return 1;
        }

        return 0;
    }

    /**
     * @param string $path
     * @param int $use_path
     * @return int
     */
    private static function loaderImport($path, $use_path)
    {
        if (!is_readable($path)) {
            return 0;
        }

        require_once $path;

        return 1;
    }

    /**
     * @param string $prefix
     * @return int
     */
    private static function registerNamespaceSingle($prefix)
    {
        if (YAF_G('local_namespaces')) {
            YAF_G('local_namespaces', YAF_G('local_namespaces') . PATH_SEPARATOR . $prefix);
        } else {
            YAF_G('local_namespaces', $prefix);
        }

        return 1;
    }

    /**
     * @param string[] $prefixes
     * @return int
     */
    private static function namespaceMulti(array $prefixes)
    {
        foreach ($prefixes as $prefix) {
            if (is_string($prefix)) {
                self::registerNamespaceSingle($prefix);
            }
        }

        return 1;
    }

}
