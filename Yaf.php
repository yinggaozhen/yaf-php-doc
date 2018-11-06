<?php

ini_set('yaf.library',          '');
ini_set('yaf.action_prefer',    '0');
ini_set('yaf.lowcase_path',     '0');
ini_set('yaf.use_spl_autoload', '0');
ini_set('yaf.forward_limit',    '5');
ini_set('yaf.name_suffix',      '1');
ini_set('yaf.name_separator',   '');
ini_set('yaf.st_compatible',    '0');
ini_set('yaf.environ',          'product');
ini_set('yaf.use_namespace',    '0');

// MINIT_FUNCTION
define('YAF\\ENVIRON',                   '');
define('YAF\\VERSION',                   '3.0.8-dev');
define('YAF\\ERR\\STARTUP_FAILED', 		 512);
define('YAF\\ERR\\ROUTE_FAILED', 		 513);
define('YAF\\ERR\\DISPATCH_FAILED', 	 514);
define('YAF\\ERR\\NOTFOUND\\MODULE', 	 515);
define('YAF\\ERR\\NOTFOUND\\CONTROLLER', 516);
define('YAF\\ERR\\NOTFOUND\\ACTION', 	 517);
define('YAF\\ERR\\NOTFOUND\\VIEW', 		 518);
define('YAF\\ERR\\CALL_FAILED',			 519);
define('YAF\\ERR\\AUTOLOAD_FAILED', 	 520);
define('YAF\\ERR\\TYPE_ERROR',			 521);

// RINIT_FUNCTION
$GLOBALS['yaf']['throw_exception'] = 1;
$GLOBALS['yaf']['ext']             = 'php';
$GLOBALS['yaf']['view_ext']        = 'phtml';
$GLOBALS['yaf']['default_module']  = 'Index';
$GLOBALS['yaf']['default_action']  = 'Index';



