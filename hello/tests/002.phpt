--TEST--
hello_test1() Basic test
--SKIPIF--
<?php
if (!extension_loaded('hello')) {
	echo 'skip';
}
?>
--FILE--
<?php 
$ret = hello_test1();

var_dump($ret);
?>
--EXPECT--
The extension hello is loaded and working!
NULL
