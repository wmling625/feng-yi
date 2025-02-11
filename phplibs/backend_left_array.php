<?php
$menu_array = array();
$icon_array = array(
    "初始頁" => "fa-solid fa-star",
    "管理員" => "fa-solid fa-user-secret",
    "廣告管理" => "fa-solid fa-rectangle-ad",
    // "QRCode管理" => "fa-solid fa-qrcode",
    "單位條碼管理" => "fa-solid fa-qrcode",
    "會員管理" => "fa-solid fa-user-group",
    "通知歷程" => "fa-brands fa-line",
    "設定" => "fa-solid fa-tools",
    "驗證碼設定" => "fa-solid fa-tools"

);

$menu_array["初始頁"][0] = array("title" => "初始頁", "link" => "init.php", "target" => "_self", "checked" => true, "user_checked" => true, "user_show" => true);

// $menu_array["QRCode管理"][0] = array("title" => "標籤群組", "link" => "qr_type_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
// $menu_array["QRCode管理"][1] = array("title" => "QRCode", "link" => "qrcode_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["單位條碼管理"][] = array("title" => "單位條碼", "link" => "qr_type_big_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
$menu_array["單位條碼管理"][] = array("title" => "綁定紀錄", "link" => "qrcode_big_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["廣告管理"][0] = array("title" => "廣告管理", "link" => "advertisement_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["會員管理"][0] = array("title" => "會員", "link" => "member_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["通知歷程"][0] = array("title" => "通知歷程", "link" => "history_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["管理員"][0] = array("title" => "管理員及權限設定", "link" => "admin_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
$menu_array["管理員"][1] = array("title" => "個人資料", "link" => "me_mang.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
$menu_array["管理員"][2] = array("title" => "資料表操作紀錄", "link" => "logs_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
$menu_array["管理員"][3] = array("title" => "管理員操作紀錄", "link" => "logs_page_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);

$menu_array["設定"][0] = array("title" => "設定", "link" => "setting_mang.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
$menu_array["驗證碼設定"][0] = array("title" => "驗證碼設定", "link" => "verify_list.php", "target" => "_self", "checked" => false, "user_checked" => false, "user_show" => false);
