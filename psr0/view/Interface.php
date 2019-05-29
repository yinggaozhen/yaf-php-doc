<?php

/**
 * Yaf_View_Interface是为了提供可扩展的, 可自定的视图引擎而设立的视图引擎接口, 它定义了用在Yaf上的视图引擎需要实现的方法和功能.
 *
 * @link http://www.laruence.com/manual/yaf.class.view.html
 */
interface Yaf_View_Interface
{
    /**
     * @link https://www.php.net/manual/en/yaf-view-interface.assign.php
     *
     * @param string $name
     * @param string $value
     * @return mixed
     */
    function assign($name, $value);

    /**
     * @link https://www.php.net/manual/en/yaf-view-interface.display.php
     *
     * @param string $tpl
     * @param array $tpl_vars
     * @return mixed
     */
    function display($tpl, $tpl_vars = null);

    /**
     * @link https://www.php.net/manual/en/yaf-view-interface.render.php
     *
     * @param string $tpl
     * @param array $tpl_vars
     * @return mixed
     */
    function render($tpl, $tpl_vars = null);

    /**
     * @link https://www.php.net/manual/en/yaf-view-interface.setscriptpath.php
     *
     * @param string $template_dir
     * @return mixed
     */
    function setScriptPath($template_dir);

    /**
     * @link https://www.php.net/manual/en/yaf-view-interface.getscriptpath.php
     *
     * @return mixed
     */
    function getScriptPath();
}
