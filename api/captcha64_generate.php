<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");

$data = captcha64('int');
$callback = array("state" => "0", "message" => "讀取成功", "data" => $data);
echo json_encode($callback, JSON_UNESCAPED_UNICODE);
?>