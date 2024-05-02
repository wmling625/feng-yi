<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);
@$authcode = params_security($_POST["authcode"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}
if (empty($authcode)) {
    array_push($err_msg, "請輸入驗證碼");
} else {
    if ($authcode !== $_SESSION["authcode"]) {
        array_push($err_msg, "驗證碼錯誤");
    }
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {
    $callback = array("state" => "0", "message" => "驗證成功");
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);