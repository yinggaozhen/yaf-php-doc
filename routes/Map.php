<?php

namespace Yaf\Route;

use const YAF\ERR\TYPE_ERROR;
use Yaf\Request_Abstract;
use Yaf\Route_Interface;

final class Map implements Route_Interface
{
    /**
     * @var bool
     */
    protected $_ctl_router = false;

    protected $_delimiter;

    /**
     * Map constructor.
     * @param bool $controller_prefer
     * @param string $delimer
     */
    public function __construct(bool $controller_prefer = false, string $delimer = '#!')
    {
        $this->_ctl_router = $controller_prefer;

        if (!empty($delimer)) {
            $this->_delimiter = $delimer;
        }
    }

    /**
     * @param Request_Abstract $request
     * @return bool
     */
    public function route(Request_Abstract $request): bool
    {
        return (bool) $this->_route($request);
    }

    /**
     * @param array $info
     * @param array|null $query
     * @return null|string
     * @throws \Exception
     */
    public function assemble(array $info, array $query = null): ?string
    {
        $str = $this->_assemble($info, $query);

        return !is_null($str) ? $str : null;
    }

    /**
     * @param Request_Abstract $request
     * @return int
     */
    private function _route(Request_Abstract $request): int
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
                // TODO 这里后面补上
            }
        }
    }

    /**
     * @param array $info
     * @param array $query
     * @return string
     * @throws \Exception
     */
    private function _assemble(array $info, array $query): ?string
    {
        $uri = '';
        $has_delim = false;

        $ctl_prefer = $this->_ctl_router;
        $delim = $this->_delimiter;

        if (is_string($delim) && !empty($delim)) {
            $has_delim = true;
        }

        do {
            if ($ctl_prefer === true) {
                $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_MODULE_FORMAT];

                if (!is_null($zv) && is_string($zv)) {
                    $pname = $zv;
                } else {
                    yaf_trigger_error(TYPE_ERROR, "Undefined the 'action' parameter for the 1st parameter");
                    break;
                }

            } else {
                $zv = $info[Route_Interface::YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT];
                if (!is_null($zv) && is_string($zv)) {
                    $pname = $zv;
                } else {
                    yaf_trigger_error(TYPE_ERROR, "Undefined the 'controller' parameter for the 1st parameter");
                    break;
                }
            }

            foreach (explode('_', $pname) as $seg) {
                $seg .= empty($seg) ? '' : '/' . $seg;
            }

            if ($query && is_array($query)) {
                $start = 1;

                if ($has_delim) {
                    $uri .= '/' . $delim;
                }

                foreach ($query as $key => $zv) {
                    $val = (string) $zv;

                    if ($has_delim) {
                        // 参数变成URI一部分，例如/uri/a/va/b/vb
                        $uri .= '/' . $key . '/' . $val;
                    } else {
                        // 参数变成QUERY，例如/uri?a=va&b=vb
                        if ($start) {
                            $uri .= '?' . $key . '=' . $val;
                            $start = 0;
                        } else {
                            $uri .= '&' . $key . '=' . $val;
                        }

                    }
                }
            }

            return $uri;
        } while (0);

        return null;
    }
}
