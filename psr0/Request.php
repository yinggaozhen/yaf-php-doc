<?php

/**
 * 代表了一个实际请求, 一般的不用自己实例化它, Yaf_Application在run以后会自动根据当前请求实例它
 *
 * @link http://www.laruence.com/manual/yaf.class.request.html#yaf.class.request.http
 *
 * @method bool isGet()
 * @method bool isPost()
 * @method bool isPut()
 * @method bool isDelete()
 * @method bool isPatch()
 * @method bool isHead()
 * @method bool isOptions()
 * @method bool isCli()
 */
abstract class Yaf_Request_Abstract
{
    /**
     * 在路由完成后, 请求被分配到的模块名
     *
     * @var string
     */
    public $module;

    /**
     * 在路由完成后, 请求被分配到的控制器名
     *
     * @var string
     */
    public $controller;

    /**
     * 在路由完成后, 请求被分配到的动作名
     *
     * @var string
     */
    public $action;

    /**
     * 当前请求的Method, 对于命令行来说, Method为"CLI"
     *
     * @var string
     */
    public $method;

    /**
     * 当前请求的附加参数
     *
     * @var array
     */
    protected $_params;

    /**
     * 当前请求的希望接受的语言, 对于Http请求来说, 这个值来自分析请求头Accept-Language. 对于不能鉴别的情况, 这个值为NULL.
     *
     * @var string
     */
    protected $_language;

    /**
     * @var \Exception
     */
    protected $_exception;

    /**
     * 当前请求Request URI要忽略的前缀, 一般不需要手工设置, Yaf会自己分析
     *
     * @var string
     */
    protected $_base_uri;

    /**
     * 当前请求的Request URI
     *
     * @var string
     */
    protected $_uri = '';

    /**
     * 表示当前请求是否已经完成分发
     *
     * @var int
     */
    protected $_dispatched = 0;

    /**
     * 表示当前请求是否已经完成路由
     *
     * @var int
     */
    protected $_routed = 0;

    protected const YAF_REQUEST_SERVER_URI = 'request_uri=';

    public function isXmlHttpRequest()
    {
        return false;
    }

    /**
     * @return int
     */
    public function isDispatched()
    {
        return $this->_dispatched === true ? 1 : 0;
    }

    /**
     * @return int
     */
    public function isRouted()
    {
        return $this->_routed === true ? 1 : 0;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getServer(string $name = null, $default = null)
    {
        if (null === $name) {
            return $_SERVER;
        }

        return isset($_SERVER[$name]) ? $_SERVER[$name] : $default;
    }

    /**
     * @param string     $name
     * @param null|mixed $default
     * @return mixed
     */
    public function getEnv(string $name = null, $default = null)
    {
        if (null === $name) {
            return $_ENV;
        }

        return isset($_ENV[$name]) ? $_ENV[$name] : $default;
    }

    /**
     * 为当前的请求,设置路由参数.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.setParam.html
     *
     * @param array ...$params
     * @return null
     */
    public function setParam(...$params)
    {
        if (count($params) === 1) {
            $params = $params[0];

            if (!is_array($params)) {
                return;
            }

            if ($this->_setParamsMulti($params)) {
                return $this;
            }
        } else if (count($params) == 2) {
            list($name, $value) = $params;

            if (!is_string($name)) {
                return;
            }

            $this->_params[$name] = $value;
            if ($this->_setParamsSingle($name, $value)) {
                return $this;
            }
        }

        return false;
    }

    /**
     * 获取当前请求中的所有路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.
     * 比如, 对于默认的路由协议Yaf_Route_Static
     *  - 路由如下请求URL: http://www.domain.com/module/controller/action/name1/value1/name2/value2/
     * 路由结束后将会得到俩个路由参数, name1和name2, 值分别是value1, value2.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getParams.html
     *
     * @return array|null 当前所有的路由参数
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * 获取当前请求中的路由参数, 路由参数不是指$_GET或者$_POST, 而是在路由过程中, 路由协议根据Request Uri分析出的请求参数.
     * 比如, 对于默认的路由协议Yaf_Route_Static
     *  - 路由如下请求URL: http://www.domain.com/module/controller/action/name1/value1/name2/value2/
     * 路由结束后将会得到俩个路由参数, name1和name2, 值分别是value1, value2.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getParam.html
     *
     * @param string     $name 要获取的路由参数名
     * @param null|mixed $default 如果设定此参数, 如果没有找到$name路由参数, 则返回此参数值.
     * @return mixed 找到返回对应的路由参数值, 如果没有找到, 而又设置了$default_value, 则返回default_value, 否则返回NULL.
     */
    public function getParam($name, $default = null)
    {
        return isset($this->_params[$name]) ? $this->_params[$name] : $default;
    }

    /**
     * 本方法主要用于在异常捕获模式下, 在异常发生的情况时流程进入Error控制器的error动作时, 获取当前发生的异常对象
     *
     * @since 1.0.0.12
     * @link http://www.laruence.com/manual/yaf.class.request.getException.html
     *
     * @return \Exception|null
     */
    public function getException()
    {
        $exception = $this->_exception;

        if (is_object($exception) && $exception instanceof \Exception) {
            return $exception;
        }

        return null;
    }

    /**
     * 获取当前请求被路由到的模块名.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getModuleName.html
     *
     * @return mixed
     */
    public function getModuleName()
    {
        return $this->module;
    }

    /**
     * 获取当前请求被路由到的控制器名.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getControllerName.html
     *
     * @return mixed
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * 获取当前请求被路由到的动作(Action)名.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getActionName.html
     *
     * @return mixed
     */
    public function getActionName()
    {
        return $this->action;
    }

    /**
     * @param string $module
     * @return bool|$this
     */
    public function setModuleName($module)
    {
        if (!is_string($module)) {
            trigger_error('Expect a string module name');
            return false;
        }

        $this->module = $module;
        return $this;
    }

    /**
     * @param string $controller
     * @return bool|$this
     */
    public function setControllerName($controller)
    {
        if (!is_string($controller)) {
            trigger_error('Expect a string controller name');
            return false;
        }

        $this->controller = $controller;
        return $this;
    }

    /**
     * @param string $action
     * @return bool|$this
     */
    public function setActionName($action)
    {
        if (!is_string($action)) {
            trigger_error('Expect a string action name');
            return false;
        }

        $this->action = $action;
        return $this;
    }

    /**
     * @param string $uri
     * @return bool|$this
     */
    public function setBaseUri($uri)
    {
        if (empty($uri)) {
            return false;
        }

        $this->_setBaseUri($uri, null);

        return $this;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setRequestUri($uri)
    {
        $this->_uri = $uri;
        return $this;
    }

    public function setDispatched()
    {
        $this->_dispatched = (boolean) 1;

        return true;
    }

    /**
     * @return $this
     */
    public function setRouted()
    {
        $this->_routed = 1;

        return $this;
    }

    /**
     * 获取当前请求的类型, 可能的返回值为GET,POST,HEAD,PUT,CLI等.
     *
     * @since 1.0.0.5
     * @link http://www.laruence.com/manual/yaf.class.request.getMethod.html
     *
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        $lang = $this->_language;

        if (is_string($lang)) {
            $accept_langs = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            if (empty($accept_langs)) {
                return null;
            } else if (!is_string($accept_langs)) {
                return null;
            } else {
                $preferLen = 0;
                /** @var double $maxQvlaue */
			    $maxQvlaue = 0;
			    $prefer = '';
			    $langs = $accept_langs;

                foreach (explode(',', $langs) as $seg) {
                    $seg = ltrim($seg, ' ');
                    // TODO 待完成
                }
            }
        }

        return $lang;
    }

    /**
     * @return string
     */
    public function getBaseUri()
    {
        return $this->_base_uri;
    }

    /**
     * @return string
     */
    public function getRequestUri()
    {
        return $this->_uri;
    }

    /**
     * @param string $base_uri
     * @param string $request_uri
     * @return int
     */
    protected function _setBaseUri(?string $base_uri, ?string $request_uri)
    {
        if (is_null($base_uri)) {
            $script_filename = $_SERVER['SCRIPT_FILENAME'];

            do {
                if ($script_filename) {
                    global $ext;
                    $script_name = $_SERVER['SCRIPT_NAME'];
                    $file_name = basename($script_name, $ext);

                    if ($script_name) {
                        $script = basename($script_name);
                        if ($file_name == $script) {
                            $basename = $script_name;
                            break;
                        }
                    }

                    $phpself_name = $_SERVER['PHP_SELF'];
                    if ($phpself_name) {
                        $phpself = basename($phpself_name);

                        if ($file_name == $phpself) {
                            $basename = $phpself;
                            break;
                        }
                    }

                    $orig_name = $_SERVER['ORIG_SCRIPT_NAME'];
                    if ($orig_name) {
                        $orig = basename($orig_name);

                        if ($file_name == $orig) {
                            $basename = $orig;
                            break;
                        }
                    }
                }
            } while (0);

            if (!empty($basename) && $request_uri == $basename) {
                if ($basename) {
                    $this->_base_uri = rtrim($basename, '/');
                    return 1;
                }
            } else if (!empty($basename)) {
                $dir = rtrim(dirname($basename), '/');

                if ($dir && strncmp($request_uri, $dir, strlen($dir)) == 0) {
                    $this->_base_uri = $dir;
                }
            }
        } else {
            $this->_base_uri = $base_uri;
        }

        return 1;
    }

    public function __call($method, $arguments)
    {
        $method = ucfirst(str_replace('is', '', strtolower($method)));
        $allowMethod = ['Get', 'Post', 'Delete', 'Patch', 'Put', 'Head', 'Options', 'Cli'];

        if (!in_array($method, $allowMethod)) {
            // TODO throw method error
            return null;
        }

        return strcasecmp($this->method, $method) === 0;
    }

    // ================================================== 内部方法 ==================================================

    public function _setDispatched($flag)
    {
        $this->_dispatched = (bool) $flag;
    }

    /**
     * @param Yaf_Request_Abstract $request
     * @param array $values
     * @return int
     */
    public static function _setParamsMulti(Yaf_Request_Abstract $request, $values)
    {
        if ($values && is_array($values)) {
            $request->_params = $values;

            return 1;
        }

        return 0;
    }

    /**
     * @param string $key
     * @param $value
     * @return int
     */
    private function _setParamsSingle(string $key, $value)
    {
        $this->_params[$key] = $value;
        return 1;
    }

    /**
     * @param string $type
     * @param string $name
     * @return array|mixed|null
     */
    public static function _queryEx(string $type, string $name)
    {
        $carrier = [];

        switch ($type) {
            case 'POST':
            case 'GET':
            case 'FILES':
            case 'COOKIE':
                $carrier = $_COOKIE;
                break;
            case 'ENV':
                $carrier = $_ENV;
                break;
            case 'SERVER':
                $carrier = $_ENV;
                break;
            case 'REQUEST':
                $carrier = $_REQUEST;
                break;
            default:
                break;
        }

        if (empty($carrier)) {
            return null;
        }

        if (empty($name)) {
            return $carrier;
        }

        return $carrier[$name] ?? null;
    }
}
