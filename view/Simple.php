<?php

namespace Yaf\View;

use const YAF\ERR\NOTFOUND\VIEW;
use const YAF\ERR\TYPE_ERROR;
use Yaf\View_Interface;

class Simple implements View_Interface
{
    protected $_tpl_vars;

    protected $_tpl_dir;

    protected $_options;

    /**
     * Simple constructor.
     * @param string $tpl_dir
     * @param array|NULL $options
     * @throws \Exception
     */
    final public function __construct(string $tpl_dir, array $options = NULL)
    {
        $this->_tpl_vars = [];

        if (!empty($tpl_dir)) {
            if (realpath($tpl_dir) == $tpl_dir) {
                $this->_tpl_dir = $tpl_dir;
            } else {
                throw new \Exception("Expects an absolute path for templates directory", TYPE_ERROR);
            }
        }

        if (!empty($options) && is_array($options)) {
            $this->_options = $options;
        }
    }

    /**
     * @param $name
     * @param null $value
     * @return $this|bool
     */
    function assign($name, $value = null)
    {
        $argc = func_get_args();

        if ($argc == 1) {
            if ($this->assignMulti($value)) {
                return $this;
            }
        } else if ($argc == 2) {
            if ($this->assignSingle($name, $value)) {
                return $this;
            }
        } else {
            // WRONG_PARAM_COUNT
        }

        return false;
    }

    function display($tpl, $tpl_vars = null)
    {
    }

    function render($tpl, $tpl_vars)
    {
        // TODO: Implement render() method.
    }

    function setScriptPath($template_dir)
    {
        // TODO: Implement setScriptPath() method.
    }

    function getScriptPath()
    {
        // TODO: Implement getScriptPath() method.
    }

    /**
     * @param string|null $name
     * @return array|mixed|null
     */
    public function get(?string $name = null)
    {
        $tpl_vars = $this->_tpl_vars;

        if (!empty($tpl_vars) && is_array($tpl_vars)) {
            if (empty($name)) {
                return $this->_tpl_vars;
            }

            return $this->_tpl_vars[$name] ?? null;
        }

        return null;
    }

    public function eval()
    {

    }

    public function assignRef()
    {

    }

    public function clear()
    {

    }

    /**
     * @param $value
     * @return int
     */
    private function assignMulti($value) :int
    {
        if (is_array($value)) {
            $this->_tpl_vars = $value;
            return 1;
        }

        return 0;
    }

    /**
     * @param string $name
     * @param $value
     * @return int
     */
    private function assignSingle(string $name, $value) :int
    {
        try {
            $this->_tpl_vars[$name] = $value;
            return 1;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function simpleRender(string $tpl, $vars, $ret)
    {
        $tpl_vars = $this->_tpl_vars[$tpl];

        $symbol_table = $this->buildSymtable($tpl_vars, $vars);

        // 判断是否为绝对路径
        if (realpath($tpl) == $tpl) {
            // TODO
        }
    }

    /**
     * @param array $symbol_table
     * @param string $tpl
     * @param $ret
     * @throws \Exception
     */
    private function renderTpl(array $symbol_table, string $tpl, $ret)
    {
        if (realpath($tpl) != $tpl) {
            throw new \Exception(sprintf("Failed opening template %s", $tpl), VIEW);
        }

        // TODO 下次从这里开始
    }

    private function buildSymtable($tpl_vars, $vars)
    {
        $symbol_table = [];
        $scope        = $this;

        if ($tpl_vars && is_array($tpl_vars)) {
            foreach ($tpl_vars as $var_name => $entry) {
                /* GLOBALS protection */
                if ($var_name === 'GLOBALS') {
                    continue;
                }

                if ($var_name === 'this' && $scope && !empty($scope->name)) {
                    continue;
                }

                if ($this->validVarName($var_name, strlen($var_name))) {
                    $symbol_table[$var_name] = $entry;
                }
            }
        }

        if (!empty($vars) && is_array($vars)) {
            foreach ($vars as $var_name => $entry) {
                /* GLOBALS protection */
                if ($var_name === 'GLOBALS') {
                    continue;
                }

                if ($var_name === 'this' && $scope && !empty($scope->name)) {
                    continue;
                }

                if ($this->validVarName($var_name, strlen($var_name))) {
                    $symbol_table[$var_name] = $entry;
                }
            }
        }

        return $symbol_table;
    }

    private static function validVarName(string $var_name, int $var_name_len): int
    {
        if (empty($var_name)) {
            return 0;
        }

        /* 只允许首字符为: [a-zA-Z_\x7f-\xff] */
        $ch = ord($var_name[0]);
        if ($var_name[0] != '_' &&
            ($ch < 65  /* A    */ || /* Z    */ $ch > 90)  &&
            ($ch < 97  /* a    */ || /* z    */ $ch > 122) &&
            ($ch < 127 /* 0x7f */ || /* 0xff */ $ch > 255)
        ) {
            return 0;
        }

	    /* And these as the rest: [a-zA-Z0-9_\x7f-\xff] */
	    if ($var_name_len > 1) {
            for ($i = 1; $i < $var_name_len; $i++) {
                $ch = ord($var_name[$i]);

                if ($var_name[$i] != '_' &&
                    ($ch < 48  /* 0    */ || /* 9    */ $ch > 57)  &&
                    ($ch < 65  /* A    */ || /* Z    */ $ch > 90)  &&
                    ($ch < 97  /* a    */ || /* z    */ $ch > 122) &&
                    ($ch < 127 /* 0x7f */ || /* 0xff */ $ch > 255)
                ) {
                    return 0;
                }
            }
        }

	    return 1;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->_tpl_vars);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        return $this->assign($name, $value);
    }
}