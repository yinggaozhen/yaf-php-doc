<?php


!defined('DIR_ROOT') && define('DIR_ROOT', __DIR__);
!defined('YAF_TEST_APPLICATION_PATH') && define('YAF_TEST_APPLICATION_PATH', DIR_ROOT . '/tmp_build/application');

function shutdown($root = YAF_TEST_APPLICATION_PATH) {
//    $dp = opendir($root);
//
//    while (($dir = readdir($dp))) {
//        if (in_array($dir, ['.', '..'])) {
//            continue;
//        }
//        $path = $root . DIRECTORY_SEPARATOR . $dir;
//
//        if (is_dir($path)) {
//            shutdown($path);
//        } else if (is_file($path)) {
//            unlink($path);
//        }
//    }
}

function startup($root = YAF_TEST_APPLICATION_PATH) {
    $dirs = [];
    $dirs[] = 'library';
    $dirs[] = 'controllers';
	$dirs[] = 'actions';
    $dirs[] = 'plugins';
    $dirs[] = 'models';
    $dirs[] = 'views';
    $dirs[] = 'views/index';
    $dirs[] = 'tpls';

    foreach($dirs as $dir){
        $dir = $root . DIRECTORY_SEPARATOR . $dir;
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            chmod($dir, 0755);
        }
    }
}
