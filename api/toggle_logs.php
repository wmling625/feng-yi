<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");

/* 日本二站 logs 紀錄 */

/*
1.logs資料庫先建立
2.再執行 pintech_logs_create() ，不能自己綁自己 pintech_logs
*/
$option = [];
$option["db_name"] = "pintech_findit"; // 要啟用的資料庫
$option["method"] = array("delete", "update", "insert"); // 要紀錄的
//$option["method"] = array("delete","update","insert"); // 也可單選表示只紀錄特定事件
pintech_logs_create($option);