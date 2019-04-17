<?php

namespace Yaf
{
    class Exception extends \RuntimeException
    {
        /**
         * @var int
         */
        protected $code = 0;

        /**
         * @var string
         */
        protected $message;

        /**
         * @var string
         */
        protected $previous;

        public function __construct()
        {
        }
    }
}

namespace Yaf\Exception
{

    use \Yaf\Exception;

    class StartupError extends Exception
    {
    }

    class RouterFailed extends Exception
    {
    }

    class DispatchFailed extends Exception
    {
    }

    class LoadFailed extends Exception
    {
    }

    class TypeError extends Exception
    {
    }
}

namespace Yaf\Exception\LoadFailed
{

    use \Yaf\Exception;

    class Module extends Exception
    {
    }

    class Controller extends Exception
    {
    }
    class Action extends Exception
    {
    }
    class View extends Exception
    {
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

    function yaf_buildin_exceptions(int $code, string $exception = null): string
    {
        static $exceptions = [];

        if (is_null($exception)) {
            return $exceptions[$code];
        }

        return $exceptions[$code] = $exception;
    }

    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\STARTUP_FAILED, StartupError::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\ROUTE_FAILED, RouterFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\DISPATCH_FAILED, DispatchFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\AUTOLOAD_FAILED, LoadFailed::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\NOTFOUND\MODULE, LoadFailed\Module::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\NOTFOUND\CONTROLLER, LoadFailed\Controller::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\NOTFOUND\ACTION, LoadFailed\Action::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\NOTFOUND\VIEW, LoadFailed\View::class);
    \Yaf\Exception\Internal\yaf_buildin_exceptions(\Yaf\ERR\TYPE_ERROR, TypeError::class);
}
