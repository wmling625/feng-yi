<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$qrcode_id = params_security($_POST["qrcode_id"]);
@$code = params_security($_POST["code"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($code)) {
    array_push($err_msg, "請填寫QRCode名稱");
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {

    /* 檢查該 code 是否存在資料庫內 */
    $query = "SELECT * FROM `qrcode` WHERE `code` = '" . $code . "' AND qrcode_id != '" . $qrcode_id . "' ";

    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        $rows = $result->fetch_assoc();
        if ($total == 0) {
            $callback = array("state" => "1", "message" => "名稱可使用");
        } else {
            array_push($err_msg, "名稱不可使用");
        }
        mysqli_free_result($result);
    }

}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);