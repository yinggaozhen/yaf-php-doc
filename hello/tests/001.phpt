--TEST--
Check if hello is loaded
--SKIPIF--
<?php
if (!extension_loaded('hello')) {
	echo 'skip';
}
?>
--FILE--
<?php 
echo 'The extension "hello" is available';
?>
--EXPECT--
The extension "hello" is available
