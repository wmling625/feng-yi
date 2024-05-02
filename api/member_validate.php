<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$userId = params_security($_POST["userId"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($userId)) {
    array_push($err_msg, "無法正確取得會員資訊");
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {

    /* 檢查該 userId 是否存在資料庫內 */
    $query = "SELECT * FROM `member` WHERE `user_id` = '" . $userId . "'";

    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        $rows = $result->fetch_assoc();
        if ($total == 1) {
            if ($rows['orders'] == "1") {
                $callback = array("state" => "1", "message" => "會員已註冊");
            } else {
                $callback = array("state" => "0", "message" => "您已被停權，請聯繫客服");
            }
        } else {
            array_push($err_msg, "查無會員資訊，請先註冊");
        }
        mysqli_free_result($result);
    }

}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);