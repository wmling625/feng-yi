<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$id = params_security($_POST["id"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
} elseif (empty($id)) {
    array_push($err_msg, "請輸入有效的參數");
}

if (count($err_msg) > 0) {
    $callback = array("state" => -1, "message" => implode("、", $err_msg));
} else {
    $result_arr = array();
    $query = "SELECT * FROM qrcode WHERE member_id = '" . $id . "'; ";
    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        if ($total > 0) {
            $callback = array("state" => 1, "message" => "共有" . $total . "筆QRcode綁定資料");
        } else {
            array_push($err_msg, "尚無QRcode綁定資料");
        }
    }
}

if (count($err_msg) > 0) {
    $callback = array("state" => -1, "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);