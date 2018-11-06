<?php

namespace Yaf
{
    class Exception
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

        /**
         * @return string
         */
        public function getPrevious()
        {
            return $this->previous;
        }
    }
}

namespace Yaf\Exception
{
    class StartupError
    {
    }

    class RouterFailed
    {
    }

    class DispatchFailed
    {
    }

    class LoadFailed
    {
    }

    class TypeError
    {
    }
}

namespace Yaf\Exception\LoadFailed
{
    class Module
    {
    }

    class Controller
    {
    }
    class Action
    {
    }
    class View
    {
    }
}