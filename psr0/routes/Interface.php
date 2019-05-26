<?php

use Yaf\Request_Abstract;
use Yaf\Route\Map;
use Yaf\Route\Regex;
use Yaf\Route\Rewrite;
use Yaf\Route\Simple;
use Yaf\Route\Supervar;

interface Yaf_Route_Interface
{
    public function route(Request_Abstract $request);

    public function assemble(array $info, array $query = null);

    // ================================================== 内部常量 ==================================================

    public const YAF_ROUTE_ASSEMBLE_MOUDLE_FORMAT       = ':m';
    public const YAF_ROUTE_ASSEMBLE_ACTION_FORMAT       = ':a';
    public const YAF_ROUTE_ASSEMBLE_CONTROLLER_FORMAT   = ':c';

    public const YAF_ROUTER_URL_DELIMIETER  	        = '/';
    public const YAF_ROUTE_REGEX_DILIMITER  	        = '#';
}

/**
 * @param $config
 * @return null
 * @throws \Exception
 */
function routerInstance($config)
{
    /** @var \Yaf\Route_Interface $instance */
    $instance = null;

    if (empty($config) || !is_array($config)) {
        return null;
    }

    $pzval = $config['type'] ?? null;
    if (!isset($pzval) || !is_string($pzval)) {
        return null;
    }

    if ($pzval == 'rewrite') {
        $match = $config['match'] ?? null;
        if (!isset($match) || !is_string($match)) {
            return null;
        }

        $def = $config['route'] ?? null;
        if (!isset($def) || !is_array($def)) {
            return null;
        }

        $verify = $config['route'] ?? null;
        if (!isset($def) || !is_array($def)) {
            $verify = null;
        }

        $instance = new Rewrite($match, $def, $verify);
    } else if ($pzval == 'regex') {
        $match = $config['match'] ?? null;
        if (!isset($match) || !is_string($match)) {
            return null;
        }

        $def = $config['route'] ?? null;
        if (!isset($def) || !is_array($def)) {
            return null;
        }

        $map = $config['map'] ?? null;
        if (!isset($map) || !is_array($map)) {
            $map = null;
        }

        $verify = $config['route'] ?? null;
        if (!isset($def) || !is_array($def)) {
            $verify = null;
        }

        $reverse = $config['route'] ?? null;
        if (!isset($reverse) || !is_string($reverse)) {
            $reverse = null;
        }

        $instance = new Regex($match, $def, $map, $verify, $reverse);
    } else if ($pzval == 'map') {
        $delimiter = '';
        $controllerPrefer = false;

        $pzval = $config['controllerPrefer'] ?? null;
		if (isset($pzval)) {
            $controllerPrefer = (bool) $pzval;
        }

        $pzval = $config['delimiter'] ?? null;
		if (isset($pzval) && is_string($pzval)) {
		    $delimiter = $pzval;
        }

		$instance = new Map($controllerPrefer, $delimiter);
    } else if ($pzval == 'simple') {
        $match = $config['module'] ?? null;
        if (!isset($match) || !is_string($match)) {
            return null;
        }

        $def = $config['controller'] ?? null;
        if (!isset($def) || !is_string($def)) {
            return null;
        }

        $map = $config['action'] ?? null;
        if (!isset($map) || !is_string($map)) {
            return null;
        }

        $instance = new Simple($match, $def, $map);
    } else if ($pzval == 'supervar') {
        $match = $config['varname'] ?? null;
        if (!isset($match) || !is_string($match)) {
            return null;
        }

        $instance = new Supervar($match);
    }

    return $instance;
}
