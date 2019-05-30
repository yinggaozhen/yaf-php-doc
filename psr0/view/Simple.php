<?php

use const YAF\ERR\NOTFOUND\VIEW;
use const YAF\ERR\TYPE_ERROR;
use Yaf\View_Interface;

/**
 * af_View_Simple是Yaf自带的视图引擎, 它追求性能, 所以并没有提供类似Smarty那样的多样功能, 和复杂的语法.
 * 对于Yaf_View_Simple的视图模板, 就是普通的PHP脚本, 对于通过Yaf_View_Interface::assgin的模板变量, 可在视图模板中直接通过变量名使用.
 *
 * @link http://www.laruence.com/manual/yaf.class.view.html#yaf.class.view.simple
 */
class Yaf_View_Simple implements View_Interface
{
    /**
     * 所有通过Yaf_View_Simple::assign分配的变量, 都保存在此属性中
     *
     * @var array
     */
    protected $_tpl_vars;

    /**
     * 当前视图引擎的模板文件基目录
     *
     * @var string
     */
    protected $_tpl_dir;

    /**
     * @var array|null
     */
    protected $_options;

    /**
     * Simple constructor.
     *
     * @link https://www.php.net/manual/en/yaf-view-simple.construct.php
     *
     * @param string $tpl_dir
     * @param array|null $options
     * @throws \Exception | \Yaf\Exception\TypeError
     */
    final public function __construct($tpl_dir, $options = null)
    {
        $this->_tpl_vars = [];

        if (is_string($tpl_dir)) {
            if (\YP\isAbsolutePath($tpl_dir)) {
                $this->_tpl_dir = $tpl_dir;
            } else {
                yaf_trigger_error(TYPE_ERROR, 'Expects an absolute path for templates directory');
            }
        }

        if (!empty($options) && is_array($options)) {
            $this->_options = $options;
        }
    }

    /**
     * 为视图引擎分配一个模板变量, 在视图模板中可以直接通过${$name}获取模板变量值
     *
     * @link http://www.laruence.com/manual/yaf.class.view.simple.assign.html
     *
     * @param string $name <p>
     * 字符串或者关联数组, 如果为字符串, 则$value不能为空, 此字符串代表要分配的变量名.
     * 如果为数组, 则$value须为空, 此参数为变量名和值的关联数组.
     * </p>
     *
     * @param mixed $value 分配的模板变量值
     * @return $this|bool
     */
    function assign($name, $value = null)
    {
        $argc = func_num_args();

        if ($argc === 1) {
            if ($this->assignMulti($name)) {
                return $this;
            }
        } else if ($argc === 2) {
            if ($this->assignSingle($name, $value)) {
                return $this;
            }
        } else {
            // WRONG_PARAM_COUNT
        }

        return false;
    }

    /**
     * 渲染一个视图模板, 并直接输出给请求端
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.view.simple.display.html
     *
     * @param string $tpl 视图模板的文件, 绝对路径, 一般这个路径由Yaf_Controller_Abstract提供
     * @param array $tpl_vars 关联数组, 模板变量
     * @throws \Exception
     */
    function display($tpl, $tpl_vars = null)
    {
        $null = null;
        $this->simpleRender($tpl, $tpl_vars, $null);
    }

    /**
     * 渲染一个视图模板, 得到结果
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.view.simple.render.html
     *
     * @param string $tpl 视图模板的文件, 绝对路径, 一般这个路径由Yaf_Controller_Abstract提供
     * @param array $tpl_vars 关联数组, 模板变量
     * @return string $return_value
     * @throws \Exception
     */
    function render($tpl, $tpl_vars = [])
    {
        $return_value = '';
        $this->simpleRender($tpl, $tpl_vars, $return_value);

        return $return_value;
    }

    /**
     * 设置模板的基目录, 默认的Yaf_Dispatcher会设置此目录为APPLICATION_PATH . "/views".
     *
     * @since 1.0.0.13
     * @link http://www.laruence.com/manual/yaf.class.view.simple.setScriptPath.html
     *
     * @param string $template_dir 视图模板的基目录, 绝对地址
     * @return $this|bool
     */
    function setScriptPath($template_dir)
    {
        if (is_string($template_dir) && realpath($template_dir) == $template_dir) {
            $this->_tpl_dir = $template_dir;

            return $this;
        }

        return false;
    }

    /**
     * 获取当前的模板目录
     *
     * @since 1.0.0.13
     * @link http://www.laruence.com/manual/yaf.class.view.simple.getScriptPath.html
     *
     * @return string
     */
    function getScriptPath()
    {
        $tpl_dir = $this->_tpl_dir;

        if ((empty($tpl_dir) || !is_string($tpl_dir)) && YAF_G('view_directory')) {
            return YAF_G('view_directory');
        }

        return $tpl_dir;
    }

    /**
     * 获取视图引擎的一个模板变量值
     *
     * @since 1.0.0.0
     * @link http://www.laruence.com/manual/yaf.class.view.simple.get.html
     *
     * @param string|null $name 模板变量名
     * @return array|mixed|null
     */
    public function get($name = null)
    {
        if (!is_null($this->_tpl_vars) && is_array($this->_tpl_vars)) {
            if ($name) {
                if (array_key_exists($name, $this->_tpl_vars)) {
                    return $this->_tpl_vars[$name];
                }
            } else {
                return $this->_tpl_vars;
            }
        }

        return null;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-view-simple.eval.php
     *
     * @param string $tpl_content
     * @param array|null $vars
     * @return bool
     * @throws \Exception
     */
    public function eval($tpl_content, array $vars = null)
    {
        $result_value = '';

        if (!$this->simpleEval($tpl_content, $vars, $result_value)) {
            return false;
        }

        return $result_value;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-view-simple.assignref.php
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function assignRef($name, &$value)
    {
        $this->_tpl_vars[$name] = &$value;

        return $this;
    }

    /**
     * @link https://www.php.net/manual/en/yaf-view-simple.clear.php
     *
     * @param string|null $name
     */
    public function clear($name = null)
    {
        if (empty($name)) {
            $this->_tpl_vars = [];
        } else {
            unset($this->_tpl_vars[$name]);
        }
    }

    public function __isset($name)
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

    // ================================================== 内部方法 ==================================================

    /**
     * @param $value
     * @return int
     */
    private function assignMulti($value)
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
    private function assignSingle($name, $value)
    {
        try {
            $this->_tpl_vars[$name] = $value;
            return 1;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @param string $tpl
     * @param $vars
     * @param $result
     * @return int
     * @throws \Exception
     */
    private function simpleRender($tpl, $vars, &$result)
    {
        if (!is_string($tpl)) {
            return 0;
        }

        $symbol_table = $this->buildSymtable($this->_tpl_vars, $vars);

        // 判断是否为绝对路径
        if (\YP\isAbsolutePath($tpl)) {
            if ($this->renderTpl($symbol_table, $tpl, $result) == 0) {
                return 0;
            }
        } else {
            $tpl_dir = $this->_tpl_dir;

            if (!is_string($tpl_dir)) {
                if (YAF_G('view_directory')) {
                    $script = sprintf("%s%s%s", YAF_G('view_directory'), DIRECTORY_SEPARATOR, $tpl);
                } else {
                    $message = sprintf("Could not determine the view script path, you should call %s::setScriptPath to specific it", self::class);
                    throw new \Exception($message, VIEW);
                }
            } else {
                $script = $tpl_dir . DIRECTORY_SEPARATOR . $tpl;
            }

            if ($this->renderTpl($symbol_table, $script, $result) == 0) {
                return 0;
            }
        }

        return 1;
    }

    /**
     * @param array $symbol_table
     * @param string $_internal_tpl
     * @param null|string $_internal_result
     * @return int
     * @throws \Exception
     */
    private function renderTpl(array $symbol_table, $_internal_tpl, &$_internal_result)
    {
        extract($symbol_table, EXTR_REFS);

        if (!realpath($_internal_tpl)) {
            yaf_trigger_error(VIEW, 'Failed opening template %s', $_internal_tpl);
        }

        if (!is_file($_internal_tpl) || !is_readable($_internal_tpl)) {
            yaf_trigger_error(E_USER_WARNING, 'Unable to fetch ob content');
            return 0;
        }

        ob_start();
        include $_internal_tpl;

        if (is_null($_internal_result)) { // display方式. 直接输出页面
            ob_end_flush();
        } else { // render. 获取页面内容,不直接输出
            $_internal_result = ob_get_contents();
            ob_end_clean();
        }

        return 1;
    }

    /**
     * @param $tpl_vars
     * @param $vars
     * @return array
     */
    private function buildSymtable($tpl_vars, $vars)
    {
        $symbol_table = [];
        $scope        = $this;

        if ($tpl_vars && is_array($tpl_vars)) {
            foreach ($tpl_vars as $var_name => &$entry) {
                /* GLOBALS protection */
                if ($var_name === 'GLOBALS') {
                    continue;
                }

                if ($var_name === 'this' && $scope && !empty($scope->name)) {
                    continue;
                }

                if ($this->validVarName($var_name, strlen($var_name))) {
                    $symbol_table[$var_name] = &$entry;
                }
            }
        }

        if (!empty($vars) && is_array($vars)) {
            foreach ($vars as $var_name => &$entry) {
                /* GLOBALS protection */
                if ($var_name === 'GLOBALS') {
                    continue;
                }

                if ($var_name === 'this' && $scope && !empty($scope->name)) {
                    continue;
                }

                if ($this->validVarName($var_name, strlen($var_name))) {
                    $symbol_table[$var_name] = &$entry;
                }
            }
        }

        return $symbol_table;
    }

    /**
     * @param string $tpl
     * @param array|null $vars
     * @param $result
     * @return int
     */
    private function simpleEval($tpl, $vars, &$result)
    {
        if (!is_string($tpl)) {
            return 0;
        }

        $tpl_vars = $this->_tpl_vars;
        $symbol_table = $this->buildSymtable($tpl_vars, $vars);

        if (strlen($tpl)) {
            $__phtml = sprintf("?>%s", $tpl);

            extract($symbol_table);
            ob_start();
            eval($__phtml);
            $result = ob_get_contents();
            ob_end_clean();
        }

        return 1;
    }

    /**
     * @param string $var_name
     * @param int $var_name_len
     * @return int
     */
    private static function validVarName($var_name, $var_name_len)
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
}
