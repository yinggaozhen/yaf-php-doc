<?php

namespace Yaf\Route;

// TODO 类名和目录名称不一致

use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Map implements Route_Interface
{
    /**
     * @var bool
     */
    protected $_ctl_router = false;

    protected $_delimiter;

    public function __construct(bool $controller_prefer = false, string $delimer = '#!')
    {
        $this->_ctl_router = $controller_prefer;

        if (!empty($delimer)) {
            $this->_delimiter = $delimer;
        }
    }

    public function route(Request_Abstract $request)
    {
        $uri = $request->getRequestUri();
        $base_uri = $request->getBaseUri();

        $ctl_prefer = $this->_ctl_router;
        $delimer = $this->_delimiter;

        if ($base_uri && is_string($base_uri) && !strncasecmp($uri, $base_uri, strlen($base_uri))) {
            $req_uri = $uri . $base_uri;
        } else {
            $req_uri = $uri;
        }

        if (is_string($delimer) && !empty($delimer)) {
            $query_str = strstr($req_uri, $delimer);

            if ($query_str[strlen($query_str) - 1] == '/') {
                // TODO
            }
        }
    }

    public function assemble()
    {
        // TODO: Implement assemble() method.
    }
}