<?php

namespace Yaf;

use const INTERNAL\PHP\DEFAULT_SLASH;
use const INTERNAL\PHP\FAILURE;
use const INTERNAL\PHP\SUCCESS;
use const YAF\ERR\STARTUP_FAILED;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Request\Http;

/**
 * Yaf_Application代表一个产品/项目, 是Yaf运行的主导者, 真正执行的主题. 它负责接收请求, 协调路由, 分发, 执行, 输出.
 *
 * @since 1.0.0.0
 * @link http://www.laruence.com/manual/yaf.classes.html#yaf.class.application
 */
class Application
{
    /**
     * Yaf_Application通过特殊的方式实现了单利模式, 此属性保存当前实例
     *
     * @var Application
     */
    protected static $_app = null;

    /**
     * 全局配置实例
     *
     * @var Config_Abstract
     */
    protected $config = null;

    /**
     * Yaf_Dispatcher实例
     *
     * @var Dispatcher
     */
    protected $dispatcher = null;

    /**
     * 存在的模块名, 从配置文件中ap.modules读取
     *
     * @var array
     */
    protected $_modules = null;

    /**
     * 布尔值, 指明当前的Yaf_Application是否已经运行
     *
     * @var bool
     */
    protected $_running = false;

    /**
     * 当前的环境名, 也就是Yaf_Application在读取配置的时候, 获取的配置节名字
     *
     * @var string
     */
    protected $_environ;

    /**
     * @var int
     */
    protected $_err_no = 0;

    /**
     * @var string
     */
    protected $_err_msg = '';

    /**
     * 初始化一个Yaf_Application, 如果$config是一个INI文件, 那么$section指明要读取的配置节.
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.application.construct.html
     *
     * @param mixed $config 关联数组的配置, 或者一个指向ini格式的配置文件的路径的字符串, 或者是一个Yaf_Config_Abstract实例
     * @param string|null $section
     * @throws \Exception | \ReflectionException | \TypeError
     */
    public function __construct($config, $section = null)
    {
        $app = self::$_app;
        if (!is_null($app)) {
            throw new \Exception("Only one application can be initialized", STARTUP_FAILED);
        }

        /** @var Config_Abstract $zconfig */
        $zconfig = null;

        $instanceFunc = new \ReflectionMethod(Config_Abstract::class, 'instance');
        $instanceFunc->setAccessible(true);
        if (!$section || !is_string($section) || empty($section)) {
            $zsection = YAF_G('yaf.environ');
            $zconfig = $instanceFunc->invoke(null, $config, $zsection);
        } else {
            $zconfig = $instanceFunc->invoke(null, $config, $section);
        }

        if (!is_object($zconfig) || self::parseOption($zconfig->_config) === FAILURE) {
            yaf_trigger_error(STARTUP_FAILED, 'Initialization of application config failed');
            return false;
        }

        try {
            $zrequest = new Http(null, YAF_G('base_uri'));
        } catch (\Exception $e) {
            throw new \Exception("Initialization of request failed", STARTUP_FAILED);
        }

        $zdispatcher = Dispatcher::getInstance();
        if (!is_object($zdispatcher)) {
            yaf_trigger_error(STARTUP_FAILED, "Instantiation of application dispatcher failed");
            return false;
        }

        $zdispatcher->setRequest($zrequest);
        $this->config = $zconfig;
        $this->dispatcher = $zdispatcher;

        if (YAF_G('local_library')) {
            $globalLibrary = YAF_G('yaf.library') ?: null;

            $loader = Loader::getInstance(YAF_G('local_library'), $globalLibrary);
        } else {
            $localLibrary = sprintf("%s%s%s", YAF_G('directory'), DEFAULT_SLASH, Loader::YAF_LIBRARY_DIRECTORY_NAME);
            $globalLibrary = YAF_G('yaf.library') ?: null;

            $loader = Loader::getInstance($localLibrary, $globalLibrary);
        }

        if (!is_object($loader)) {
            yaf_trigger_error(STARTUP_FAILED, "Initialization of application auto loader failed");
            return false;
        }

        $this->_running = 0;
        $this->_environ = YAF_G('yaf.environ');

        if (is_array(YAF_G('modules'))) {
            $this->_modules = YAF_G('modules');
        } else {
            $this->_modules = null;
        }

        self::$_app = $this;
    }

    /**
     * 获取当前的Yaf_Application实例
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.application.app.html
     *
     * @return Application
     */
    public static function app()
    {
        return self::$_app;
    }

    /**
     * 运行一个Yaf_Application, 开始接受并处理请求. 这个方法只能调用一次, 多次调用并不会有特殊效果.
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.application.run.html
     *
     * @throws \Exception
     */
    public function run()
    {
        $running = $this->_running;

        if ($running === true) {
            yaf_trigger_error(STARTUP_FAILED, 'An application iniInstance already run');
            return true;
        }

        $this->_running = true;
        $dispatcher = $this->getDispatcher();
        if (is_null($dispatcher->_dispatch($returnVal))) {
            return false;
        }

        return $returnVal;
    }

    /**
     * 在Yaf_Application的环境下, 运行一个用户自定义函数过程. 主要用在使用Yaf做简单的命令行脚本的时候, 应用Yaf的外围环境, 比如:自动加载, 配置, 视图引擎等.
     *
     * @since 1.0.0.17
     * @link http://www.laruence.com/manual/yaf.class.application.execute.html
     *
     * @param callable $func 要运行的函数或者方法, 方法可以通过array($obj, "method_name")来定义.
     * @param array $args 零个或者多个要传递给函数的参数.
     * @return bool|mixed 被调用函数或者方法的返回值
     */
    public function execute($func, ...$args)
    {
        if (!is_callable($func)) {
            return trigger_error('Yaf_Application::execute must be a valid callback', E_USER_WARNING);
        }

        $returnVal = call_user_func_array($func, $args);
        return $returnVal;
    }

    /**
     * 获取当前Yaf_Application的环境名
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.application.environ.html
     *
     * @return string 当前的环境名, 也就是ini_get("yaf.environ")
     */
    public function environ()
    {
        $env = $this->_environ;

        assert(is_string($env));

        return (string) $env;
    }

    /**
     * 指示Yaf_Application去寻找Bootstrap(默认在ap.directory/Bootstrap.php), 并执行所有在Bootstrap类中定义的, 以_init开头的方法.
     * 一般用作在处理请求之前, 做一些个性化定制.
     * Bootstrap并不会调用run, 所以还需要在bootstrap以后调用Application::run来运行Yaf_Application实例
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.application.bootstrap.html
     *
     * @throws \Exception | \ReflectionException
     * @return $this|bool
     */
    public function bootstrap()
    {
        $ce = null;
        $retval = 1;

        $ce = \YP\getClassEntry(Bootstrap_Abstract::YAF_DEFAULT_BOOTSTRAP_LOWER);

        if (!class_exists($ce)) {
            if (YAF_G('bootstrap')) {
                $bootstrapPath = YAF_G('bootstrap');
            } else {
                $bootstrapPath = sprintf('%s%s%s.%s', YAF_G('directory'), DEFAULT_SLASH, Bootstrap_Abstract::YAF_DEFAULT_BOOTSTRAP, YAF_G('ext'));
            }

            if (!Loader::import($bootstrapPath)) {
                trigger_error("Couldn't find bootstrap file {$bootstrapPath}", E_USER_WARNING);
                $retval = 0;
            } else if (!($ce = \YP\getClassEntry(Bootstrap_Abstract::YAF_DEFAULT_BOOTSTRAP_LOWER))) {
                trigger_error(sprintf("Couldn't find class %s in %s", Bootstrap_Abstract::YAF_DEFAULT_BOOTSTRAP_LOWER, $bootstrapPath), E_USER_WARNING);
                $retval = 0;
            } else if (get_parent_class($ce) !== 'Yaf\Bootstrap_Abstract' && get_parent_class($ce) !== 'Yaf_Bootstrap_Abstract') {
                trigger_error(sprintf("Expect a %s iniInstance, %s give", Bootstrap_Abstract::class, $ce), E_USER_WARNING);
            }
        }

        if (!$retval) {
            return false;
        } else {
            $bootstrap = new $ce();
            $dispatcher = $this->getDispatcher();

            $reflection = new \ReflectionClass($bootstrap);
            $methods = $reflection->getMethods();

            $prefixLen = strlen(Bootstrap_Abstract::YAF_BOOTSTRAP_INITFUNC_PREFIX) - 1;
            foreach ($methods as $method) {
                if (strncasecmp($method->getName(), Bootstrap_Abstract::YAF_BOOTSTRAP_INITFUNC_PREFIX, $prefixLen)) {
                    continue;
                }

                $method->setAccessible(true);
                $method->invoke($bootstrap, $dispatcher);
            }
        }

        return $this;
    }

    /**
     *获取Yaf_Application读取的配置项.
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.application.getConfig.html
     *
     * @return Config_Abstract
     */
    public function getConfig()
    {
        /** @var Config_Abstract $config */
        $config = $this->config;

        return $config;
    }

    /**
     * 获取当前的分发器
     *
     * @since 1.0.0.6
     * @link http://www.laruence.com/manual/yaf.class.application.getDispatcher.html
     *
     * @return Dispatcher Yaf_Dispatcher实例
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * 获取在配置文件中申明的模块.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.application.getModules.html
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * 改变APPLICATION_PATH, 在这之后, 将从新的APPLICATION_PATH下加载控制器/视图, 但注意, 不会改变自动加载的路径.
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.dispatcher.setAppDirectory.html
     *
     * @param string $directory 绝度路径的APPLICATION_PATH
     * @return $this|bool
     */
    public function setAppDirectory($directory)
    {
        if (empty($directory) || realpath($directory) !== $directory) {
            return false;
        }

        YAF_G('directory', $directory);

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.getappdirectory.php
     *
     * @return string
     */
    public function getAppDirectory()
    {
        return YAF_G('directory');
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.getlasterrorno.php
     *
     * @return int
     */
    public function getLastErrorNo()
    {
        $errcode = $this->_err_no;
        assert(is_long($errcode));

        return (int) $errcode;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.getlasterrormsg.php
     *
     * @return string
     */
    public function getLastErrorMsg()
    {
        $errmsg = $this->_err_msg;
        assert(is_string($errmsg));

        return (string) $errmsg;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.clearlasterror.php
     *
     * @return $this
     */
    public function clearLastError()
    {
        $this->_err_no = 0;
        $this->_err_msg = '';

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.destruct.php
     */
    public function __destruct()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.clone.php
     */
    private function __clone()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.sleep.php
     */
    private function __sleep()
    {
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.wakeup.php
     */
    private function __wakeup()
    {
    }

    // ================================================== 内部方法 ==================================================

    /**
     * 内部方法,外部不可调用
     *
     * @param string $name
     * @return int
     */
    public static function isModuleName($name)
    {
        $app = self::$_app;

        if (!is_object($app)) {
            return 0;
        }

        $modules = $app->getModules();
        if (!is_array($modules)) {
            return 0;
        }

        foreach ($modules as $module) {
            if (!is_string($module)) {
                continue;
            }

            if (strnatcasecmp($module, $name) === 0) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * 内部方法,外部不可调用
     *
     * @internal
     * @param string $name
     * @return int
     */
    public static function isModuleNameStr($name)
    {
        $ret = self::isModuleName($name);

        return $ret;
    }

    /**
     * @param $options
     * @return int
     * @throws \Exception
     */
    public static function parseOption($options)
    {
        $app = null;
        if (is_null($app = $options['application'])) {
            if (is_null($app = $options['yaf'])) {
                yaf_trigger_error(TYPE_ERROR, 'Expected an array of application configure');
                return FAILURE;
            }
        }

        if (!is_array($app)) {
            yaf_trigger_error(TYPE_ERROR, 'Expected an array of application configure');
            return FAILURE;
        }

        $pzval = $app['directory'];
        if (is_null($pzval) || !is_string($pzval) || empty($pzval)) {
            yaf_trigger_error(STARTUP_FAILED, "Expected a directory entry in application configures");
            return FAILURE;
        }
        YAF_G('directory', rtrim($pzval, DEFAULT_SLASH));

        $pzval = $app['ext'] ?? null;
        if (!is_null($pzval) && is_string($pzval)) {
            YAF_G('ext', $pzval);
        }

        $pzval = $app['bootstrap'] ?? null;
        if (!is_null($pzval) && is_string($pzval)) {
            YAF_G('bootstrap', $pzval);
        }

        $pzval = $app['library'] ?? null;
        if (!is_null($pzval)) {
            if (is_string($pzval)) {
                YAF_G('local_library', rtrim($pzval, DEFAULT_SLASH));
            } else if (is_array($pzval)) {
                $psval = $pzval['directory'] ?? null;
                if (!is_null($psval) && is_string($psval)) {
                    YAF_G('local_library', rtrim($psval, DEFAULT_SLASH));
                }

                $psval = $pzval['namespace'] ?? null;
                if (!is_null($psval) && is_string($psval) && !empty($psval)) {
                    $src = $psval;
                    $target = str_replace([' ', ','], ['', PATH_SEPARATOR], $src);
                    $method = new \ReflectionMethod(Loader::class, 'registerNamespaceSingle');
                    $method->setAccessible(true);
                    $method->invoke(null, $target);
                }
            }
        }

        $pzval = $app['view'] ?? null;
        if (!is_null($pzval) && is_array($pzval)) {
            $psval = $pzval['ext'];

            if (!is_null($psval) && is_string($psval)) {
                YAF_G('view_ext', $psval);
            }
        }

        $pzval = $app['baseUri'] ?? null;
        if (!is_null($pzval) && is_string($pzval)) {
            YAF_G('base_uri', $pzval);
        }

        $pzval = $app['dispatcher'] ?? null;
        if (!is_null($pzval) && is_array($pzval)) {
            $psval = $pzval['defaultModule'] ?? null;
            if (!is_null($psval) && is_string($psval)) {
                YAF_G('default_module', strtolower($psval));
            }

            $psval = $pzval['defaultController'] ?? null;
            if (!is_null($psval) && is_string($psval)) {
                YAF_G('default_controller', strtolower($psval));
            }

            $psval = $pzval['defaultAction'] ?? null;
            if (!is_null($psval) && is_string($psval)) {
                YAF_G('default_action', strtolower($psval));
            }

            if (array_key_exists('throwException', $pzval) && !is_null($pzval['throwException'])) {
                YAF_G('throw_exception', $pzval['throwException'] === true);
            }

            if (array_key_exists('catchException', $pzval) && !is_null($pzval['catchException'])) {
                YAF_G('catch_exception', $pzval['catchException'] === true);
            }

            $psval = $pzval['defaultRoute'] ?? null;
            if (!is_null($psval) && is_array($psval)) {
                YAF_G('default_route', $psval);
            }
        }

        do {
            YAF_G('modules', []);
            $pzval = $app['modules'] ?? null;

            if (!is_null($pzval) && is_string($pzval) && !empty($pzval)) {
                $modules = array_map('strtoupper', explode(',', $pzval));
                YAF_G('modules', $modules);
            } else {
                $module = YAF_G('default_module');
                YAF_G('modules', [$module]);
            }
        } while (0);

        $pzval = $app['system'] ?? null;
        if (!is_null($pzval) && is_array($pzval)) {
            foreach ($pzval as $key => $value) {
                $str = substr(sprintf("%s.%s", 'yaf', $key), 0, 127);
                ini_alter($str, $value);
            }
        }

        return SUCCESS;
    }
}
