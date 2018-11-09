<?php

namespace Yaf;

interface View_Interface
{
    function assign($name, $value);
    function display($tpl, $tpl_vars);
    function render($tpl, $tpl_vars);
    function setScriptPath(string $template_dir);
    function getScriptPath();
}