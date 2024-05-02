<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

@$mobile = findNum(aes_decrypt(params_security($_POST["mobile"])));
@$code = findNum(aes_decrypt(params_security($_POST["code"])));
@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}
if (!validate_twmobile($mobile)) {
    array_push($err_msg, "行動電話格式錯誤");
}

// 檢查手機是否已註冊
$member_query = "SELECT COUNT(*) as counts FROM `member` WHERE account = '" . $mobile . "'";
if ($result = $mysqli->query($member_query)) {
    $rows = $result->fetch_assoc();
    $counts = $rows["counts"];
    if ($counts >= 1) {
        array_push($err_msg, "此手機已註冊過");
    }
    mysqli_free_result($result);
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {
    // 檢查300秒內是否驗證
    $query = "SELECT * FROM `smscode` WHERE mobile = '" . $mobile . "' AND code = '" . $code . "' AND orders <= 1 AND TIMESTAMPDIFF(SECOND, pub_date, NOW()) < 305 ORDER BY pub_date DESC LIMIT 0,1; ";

    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        $rows = $result->fetch_assoc();
        if ($total == 1) {
            if ($rows['is_ok'] == "1") {
                $callback = array("state" => "0", "message" => "5分鐘內已驗證");
            } else {
                // 告知剩幾秒可以發送第二次
                $second = date("Y-m-d H:m:s", strtotime("+60 sec", $rows['pub_date']));
                echo $second;
            }
        } else {
            array_push($err_msg, "手機驗證碼已失效");
        }
        mysqli_free_result($result);
    }
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);