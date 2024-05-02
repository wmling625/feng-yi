<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$mobile = findNum(params_security($_POST["mobile"]));
@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

$code = random_str(6, "int");

$callback = array();
$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($mobile)) {
    array_push($err_msg, "請輸入手機號碼");
} else {
    if (!validate_twmobile($mobile)) {
        array_push($err_msg, "行動電話格式錯誤");
    }
//    if (!check_sms_limit($mobile)) {
//        array_push($err_msg, "今日簡訊發送次數已超過上限");
//    }
}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
} else {

    $counts = 0;
    $member_counts = 0;
    $qrcode_counts = 0;
    // 檢查60內是否發過驗證碼
    $query = "SELECT count(*) as counts FROM `smscode` WHERE mobile = '" . $mobile . "' AND orders >= 2 AND TIMESTAMPDIFF(SECOND, pub_date, NOW()) < 60 ORDER BY pub_date DESC LIMIT 0,1; ";
    $query .= "SELECT count(*) as member_counts FROM `member` WHERE account = '" . $mobile . "' AND orders >= 1; ";
    $query .= "SELECT count(*) as qrcode_counts FROM `qrcode` WHERE title = '" . $mobile . "' AND orders >= 1 ORDER BY pub_date DESC LIMIT 0,1; ";

    if ($mysqli->multi_query($query)) {
        do {
            if ($result = $mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['counts'])) {
                        $counts = intval($row['counts']);
                    }
                    if (isset($row['member_counts'])) {
                        $member_counts = intval($row['member_counts']);
                    }
                    if (isset($row['qrcode_counts'])) {
                        $qrcode_counts = intval($row['qrcode_counts']);
                    }
                }
                $result->close();
            }
            if ($mysqli->more_results()) {

            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }

    $needSMS = true;
    if ($member_counts == 1 || $qrcode_counts == 1) {
        $needSMS = false;
    }

    if ($needSMS) {
//        if ($counts == 0) {
//            // 新增簡訊發送紀錄
//            $query = "INSERT INTO smscode(smscode_id, code, mobile, pub_date, orders) VALUES (uuid(),'" . $code . "', '" . $mobile . "', now(), 2)";
//            $mysqli->query($query);
//
//            //目標,內容,80,帳號,密碼
//            if (sms_send_c2c($mobile, "【會員通知】綁定QRCode驗證碼：" . $code, 80, "0960678008", "nl3aul6app")) {
//                $callback = array("state" => "1", "message" => "驗證簡訊已發送，請在5分鐘內完成驗證");
//            } else {
//                $callback = array("state" => "-1", "message" => "簡訊發送失敗，請稍後再嘗試一次。");
//            }
//        } else {
//            array_push($err_msg, "發送時間請間隔60秒");
//        }
        $callback = array("state" => "1", "message" => "手機驗證成功");
    } else {
        $callback = array("state" => "2", "message" => "手機已驗證，無須再次驗證");
    }

}

if (count($err_msg) > 0) {
    $callback = array("state" => "-1", "message" => implode("、", $err_msg));
}

echo json_encode($callback, JSON_UNESCAPED_UNICODE);