<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$qrcode_id = aes_decrypt(params_security($_POST["qrcode_id"]));

$callback = array();
$err_msg = array();
if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {
    $result_arr = array();

    $query = "UPDATE `qrcode` SET `title`='',`introd`='',`contents`='',`last_date`=NOW(),`orders`='-1' WHERE `qrcode_id`='" . $qrcode_id . "';";
    $mysqli->query($query);

    $callback = array("state" => "1", "message" => "已註銷此筆QRCode");
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);
