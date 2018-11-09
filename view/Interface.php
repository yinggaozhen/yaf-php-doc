<?php

namespace Yaf;

interface View_Interface
{
    function assign($name, $value);
    function display($tpl, $tpl_vars);
    function render($tpl, $tpl_vars);
    function setScriptPath($template_dir);
    function getScriptPath();
}