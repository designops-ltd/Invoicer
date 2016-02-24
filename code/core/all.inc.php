<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL&(~E_NOTICE));
	
if(!isset($root))
	$root = dirname(__FILE__)."/../../";

ini_set("memory_limit", "64M");

if(!isset($cfgType))
	$cfgType = $_SERVER["HTTP_HOST"];

include_once $root."/code/core/config.inc.php";
include_once $root."/code/core/shared.inc.php";
?>