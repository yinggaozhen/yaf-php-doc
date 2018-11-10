<?php

namespace Yaf;

use Yaf\Config\Ini;
use Yaf\Config\Simple;
use const YAF\ERR\STARTUP_FAILED;
use Yaf\Request\Http;

final class Application
{
    protected static $_app = null;

    protected $config;

    protected $dispatcher;

    protected $_modules;

    /**
     * @var bool
     */
    protected $_running = false;

    /**
     * TODO
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

    /**
     * 内部方法,外部不可调用
     *
     * @internal
     * @param $name
     * @return int
     */
    public static function isModuleName($name)
    {
        $app = self::$_app;

        if (!is_object($app)) {
            return 0;
        }

        // TODO Application 从这里写起
    }
}