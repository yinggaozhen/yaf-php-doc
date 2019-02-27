<?php

namespace Yaf;

use const INTERNAL\PHP\DEFAULT_SLASH;
use Yaf\Config\Ini;
use Yaf\Config\Simple;
use const YAF\ERR\STARTUP_FAILED;
use const YAF\ERR\TYPE_ERROR;
use Yaf\Exception\LoadFailed\Action;
use Yaf\Request\Http;

final class ApplicationTODO
{
    /**
     * @var ApplicationTODO
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
     * @var null|*
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
     * @param $config
     * @param string|null $section
     * @throws \Exception
     */
    public function __construct($config, string $section = null)
    {
        $this->_environ = YAF_G('yaf.environ');

        $app = self::$_app;

        if (!is_null($app)) {
            throw new \Exception("Only one application can be initialized", STARTUP_FAILED);
        }

        try {
            $zconfig = null;

            if (!$section || !is_string($section) || empty($section)) {
                $zsection = YAF_G('environ_name');
                $zconfig = new Ini($config, $zsection);
            } else {
                $zconfig = new Simple($config, $section);
            }
        } catch (\Exception $e) {
            throw new \Exception("Initialization of application config failed", STARTUP_FAILED);
        }

        try {
            $zrequest = new Http(null, YAF_G('base_uri'));
        } catch (\Exception $e) {
            throw new \Exception("Initialization of request failed", STARTUP_FAILED);
        }

        // TODO dispatcher
    }

    public static function app()
    {
        // TODO 从这里开始
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $running = $this->_running;

        if ($running === true) {
            yaf_trigger_error(STARTUP_FAILED, "An application instance already run");
            return true;
        }

        $this->_running = true;
        $dispatcher = $this->getDispatcher();

        if (is_null($dispatcher->dispatch())) {
            return false;
        }
    }

    /**
     * @param callable $func
     * @return bool|mixed
     */
    public function execute(callback $func)
    {
        try {
            $returnVal = call_user_func($func);
            return $returnVal;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @return string
     */
    public function environ(): string
    {
        $env = $this->_environ;

        assert(is_string($env));

        return (string) $env;
    }

    public function bootstrap()
    {
        // TODO 从这里开始
    }

    /**
     * @return Config_Abstract
     */
    public function getConfig(): ?Config_Abstract
    {
        /** @var Config_Abstract $config */
        $config = $this->config;

        return $config;
    }

    /**
     * @return Dispatcher
     */
    public function getDispatcher(): Dispatcher
    {
        $dispatcher = $this->dispatcher;

        return $dispatcher;
    }

    /**
     * @return null|*
     */
    public function getModules()
    {
        $modules = $this->_modules;

        return $modules;
    }

    /**
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
     * @return string
     */
    public function getAppDirectory(): string
    {
        return YAF_G('directory');
    }

    /**
     * @return int
     */
    public function getLastErrorNo(): int
    {
        $errcode = $this->_err_no;
        assert(is_long($errcode));

        return (int) $errcode;
    }

    /**
     * @return string
     */
    public function getLastErrorMsg(): string
    {
        $errmsg = $this->_err_msg;
        assert(is_string($errmsg));

        return (string) $errmsg;
    }

    /**
     * @return $this
     */
    public function clearLastError(): ApplicationTODO
    {
        $this->_err_no = 0;
        $this->_err_msg = '';

        return $this;
    }

    public function __destruct()
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

    // ================================================== 内部方法 ==================================================

    /**
     * 内部方法,外部不可调用
     *
     * @internal
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

            if ($module == $name) {
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
     * @return bool
     * @throws \Exception
     */
    public static function parseOption($options)
    {
        $app = null;
        $conf = array_keys($options);

        if (!isset($conf['application'])) {
            if (is_null($app = $conf['yaf'])) {
                yaf_trigger_error(TYPE_ERROR, 'Expected an array of application configure');
                return false;
            }
        }

        if (is_array($app)) {
            yaf_trigger_error(TYPE_ERROR, 'Expected an array of application configure');
            return false;
        }

        $pzval = $app['directory'];
        if (is_null($pzval) || !is_string($pzval) || empty($pzval)) {
            yaf_trigger_error(STARTUP_FAILED, "Expected a directory entry in application configures");
            return false;
        }
        YAF_G('directory', rtrim($pzval, DEFAULT_SLASH));

        $pzval = $app['ext'];
        if (!is_null($pzval) && is_string($pzval)) {
            YAF_G('ext', $pzval);
        }

        $pzval = $app['bootstrap'];
        if (!is_null($pzval) && is_string($pzval)) {
            YAF_G('bootstrap', $pzval);
        }

        $pzval = $app['library'];
        if (!is_null($pzval)) {
            if (is_string($pzval)) {
                YAF_G('local_library', rtrim($pzval, DEFAULT_SLASH));
            } else if (is_array($pzval)) {
                $psval = $pzval['directory'];
                if (!is_null($psval) && is_string($psval)) {
                    YAF_G('local_library', rtrim('$psval', DEFAULT_SLASH));
                }

                $psval = $pzval['namespace'];
                if (!is_null($psval) && is_string($psval)) {

                }
            }
        }
    }
}
