<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$mobile = findNum(params_security($_POST["mobile"]));
@$userId = aes_decrypt(params_security($_POST["userId"]));
@$displayName = aes_decrypt(params_security($_POST["displayName"]));
@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

$code = random_str(6, "int");

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}
if (!validate_twmobile($mobile)) {
    array_push($err_msg, "行動電話格式錯誤");
}

/* 是否需要發送sms手機驗證碼 */
$needSMS = true;
$sameUserId = true;

/* 先檢查是否有該手機 */
$query = "SELECT * FROM `member` WHERE account = '" . $mobile . "' ";
if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_assoc();
    $total = mysqli_num_rows($result);
    if ($total == 1) {
        // $needSMS = false;
        /* 檢查userId是否相符合 */
        if ($userId !== $rows["user_id"]) {
            /* 如果有該手機但是 userId 不同，那就需要更新userId */
            $sameUserId = false;
        } else {
            /* 如果有該手機且 userId 相同，則代表已註冊、且資料無變動 > 可進行登入 */
        }
    } elseif ($total == 0) {
        /* 如果沒有該手機，那就代表尚未註冊，就將資料存進資料庫內。需發送簡訊驗證碼 */
    }
    mysqli_free_result($result);
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {

    if ($needSMS) {

//        // 檢查60內是否發過驗證碼
//        $query = "SELECT count(*) as counts FROM `smscode` WHERE mobile = '" . $mobile . "' AND orders <= 1 AND TIMESTAMPDIFF(SECOND, pub_date, NOW()) < 60 ORDER BY pub_date DESC LIMIT 0,1";
//
//        if ($result = $mysqli->query($query)) {
//            $rows = $result->fetch_assoc();
//            $counts = $rows["counts"];
//            if ($counts == 0) {
//                // 新增簡訊發送紀錄
//                $query = "INSERT INTO smscode(smscode_id, code, mobile, pub_date, orders) VALUES (uuid(),'" . $code . "', '" . $mobile . "', now(), 1)";
//                $mysqli->query($query);
//
//                //目標,內容,80,帳號,密碼
//                if (sms_send_c2c($mobile, "【會員通知】註冊驗證碼：" . $code, 80, "0960678008", "nl3aul6app")) {
//                    $callback = array("state" => "1", "message" => "驗證簡訊已發送，請在5分鐘內完成驗證");
//                } else {
//                    $callback = array("state" => "-1", "message" => "簡訊發送失敗，請稍後再嘗試一次。");
//                }
//            } else {
//                array_push($err_msg, "發送時間請間隔60秒");
//            }
//            mysqli_free_result($result);
//        }
        $callback = array("state" => "1", "message" => "手機驗證成功");
    }

}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);