<?php

if (!isset($app_started)) 
{
	$app_started = true;

	define( 'ABSPATH', dirname(__FILE__).'/');

	error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);

	require_once(ABSPATH.'config.php' );
	require_once(ABSPATH.'base.php');
}
?>


