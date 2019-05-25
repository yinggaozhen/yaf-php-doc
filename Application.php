<?php

namespace Yaf;

use const INTERNAL\PHP\DEFAULT_SLASH;
use const INTERNAL\PHP\FAILURE;
use const INTERNAL\PHP\SUCCESS;
use const YAF\ERR\STARTUP_FAILED;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Request\Http;

/**
 * @link https://www.php.net/manual/en/class.yaf-application.php
 */
class Application
{
    /**
     * @var Application
     */
    protected static $_app = null;

    /**
     * @var Config_Abstract
     */
    protected $config = null;

    /**
     * @var Dispatcher
     */
    protected $dispatcher = null;

    /**
     * @var array
     */
    protected $_modules = null;

    /**
     * @var bool
     */
    protected $_running = false;

    /**
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
     * Application constructor.
     *
     * @link https://www.php.net/manual/en/yaf-application.construct.php
     *
     * @param mixed $config
     * @param string|null $section
     * @throws \Exception | \ReflectionException | \TypeError
     */
    public function __construct($config, string $section = null)
    {
        $app = self::$_app;
        if (!is_null($app)) {
            throw new \Exception("Only one application can be initialized", STARTUP_FAILED);
        }

        try {
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
        } catch (\Exception $e) {
            throw new \Exception("Initialization of application config failed", STARTUP_FAILED);
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
     * @link https://www.php.net/manual/en/yaf-application.app.php
     *
     * @return Application
     */
    public static function app()
    {
        return self::$_app;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.run.php
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
     * @link https://www.php.net/manual/en/yaf-application.execute.php
     *
     * @param callable $func
     * @param array $args
     * @return bool|mixed
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
     * @link https://www.php.net/manual/en/yaf-application.environ.php
     *
     * @return string
     */
    public function environ()
    {
        $env = $this->_environ;

        assert(is_string($env));

        return (string) $env;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.bootstrap.php
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
     * @link https://www.php.net/manual/en/yaf-application.getconfig.php
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
     * @link https://www.php.net/manual/en/yaf-application.getdispatcher.php
     *
     * @return Dispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.getmodules.php
     *
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-application.setappdirectory.php
     *
     * @param string $directory
     * @return $this|bool
     */
    public function setAppDirectory(string $directory)
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
    public function getLastErrorNo(): int
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
    public function getLastErrorMsg(): string
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
    public function clearLastError(): Application
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
    public static function isModuleName($name): int
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
    public static function isModuleNameStr($name): int
    {
        $ret = self::isModuleName($name);

        return $ret;
    }

    /**
     * @param $options
     * @return int
     * @throws \Exception
     */
    public static function parseOption($options): int
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
