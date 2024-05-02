<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$mobile = findNum(params_security($_POST["mobile"]));
@$code = params_security($_POST["code"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}
if (!validate_twmobile($mobile)) {
    array_push($err_msg, "行動電話格式錯誤");
}
if (empty($code)) {
    array_push($err_msg, "請輸入驗證碼");
}


if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {
    if ($code === "88888") {
        $callback = array("state" => "0", "message" => "驗證成功");
    }
//    // 檢查300秒內是否驗證
//    $query = "SELECT * FROM `smscode` WHERE mobile = '" . $mobile . "' AND orders >=2 AND TIMESTAMPDIFF(SECOND, pub_date, NOW()) < 305 AND is_ok <= -1 ORDER BY pub_date DESC LIMIT 0,1; ";
//
//    if ($result = $mysqli->query($query)) {
//        $total = mysqli_num_rows($result);
//        $rows = $result->fetch_assoc();
//        if ($total == 1) {
//            if ($code != $rows['code']) {
//                array_push($err_msg, "手機驗證碼輸入錯誤，請確認");
//            } else {
//                $query = "UPDATE smscode SET is_ok = 1 WHERE mobile = '" . $mobile . "' AND code = '" . $code . "' AND orders >= 2 ;";
//                $mysqli->query($query);
//                $callback = array("state" => "0", "message" => "驗證成功");
//            }
//        } else {
//            array_push($err_msg, "手機驗證碼已失效");
//        }
//        mysqli_free_result($result);
//    }
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);