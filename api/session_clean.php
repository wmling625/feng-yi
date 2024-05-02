<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");

unset($_SESSION['member']);
$callback = array("state" => "1", "message" => "清除session成功");
echo json_encode($callback, JSON_UNESCAPED_UNICODE);
