<?php

namespace Yaf
{
    /**
     * Yaf_Exception是Yaf使用的异常类型, 它继承自Exception, 并实现了异常链.
     *
     * @link http://www.laruence.com/manual/yaf.class.exception.html
     */
    class Exception extends \Exception
    {
        /**
         * 异常代码
         *
         * @var int
         */
        protected $code = 0;

        /**
         * 异常信息
         *
         * @var string
         */
        protected $message;

        /**
         * 此异常之前的异常
         *
         * @var string
         */
        protected $previous;
    }
}

// ================================================== 内部方法 ==================================================

/**
 * @internal
 */
namespace Yaf\Exception\Internal
{
    const YAF_ERR_BASE 	= 512;
    const YAF_UERR_BASE	= 1024;
    const YAF_ERR_MASK	= 127;

    use Yaf\Exception\DispatchFailed;
    use Yaf\Exception\LoadFailed;
    use Yaf\Exception\RouterFailed;
    use Yaf\Exception\StartupError;
    use Yaf\Exception\TypeError;

    /**
     * @param int $code
     * @param string|null $exception
     * @return mixed|string
     */
    function yaf_buildin_exceptions($code, $exception = null)
    {
        static $exceptions = [];

        if (is_null($exception)) {
            return $exceptions[$code];
        }

        return $exceptions[$code] = $exception;
    }

    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\STARTUP_FAILED, StartupError::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\ROUTE_FAILED, RouterFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\DISPATCH_FAILED, DispatchFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\AUTOLOAD_FAILED, LoadFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\NOTFOUND\MODULE, LoadFailed\Module::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\NOTFOUND\CONTROLLER, LoadFailed\Controller::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\NOTFOUND\ACTION, LoadFailed\Action::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\NOTFOUND\VIEW, LoadFailed\View::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\YAF\ERR\TYPE_ERROR, TypeError::class);
}
