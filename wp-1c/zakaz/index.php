<?php 
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (empty($wp)) {require_once( '../../wp-load.php' );}

require_once('db_order.php');
require_once('Array2XML.php');
header('Content-type: application/xml');
$converter = new Array2XML();
$xmlStr = $converter->convert($ZAKAZ);
echo $xmlStr
?>























