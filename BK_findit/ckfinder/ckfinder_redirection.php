<?php
include_once(dirname(__FILE__)."/../phplibs/root.php");
include_once(dirname(__FILE__)."/../phplibs/root_global.php");
$temp = gettoken_value();
$value = $temp["value"];
$token = $temp["token"];
$str = "";


foreach ($_GET as $key => $value1) {
	$str .= $key . "=" . $value1 . "&";
}

if (strpos ($_SERVER["HTTP_REFERER"], $_SERVER['HTTP_HOST']) !== false) {
	header("Location: ckfinder_ui.php?$str"."value=$value&token=$token&ref=".rawurlencode($_SERVER["HTTP_REFERER"]));  	
	exit;
} 




?>