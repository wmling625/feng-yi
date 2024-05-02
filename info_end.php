<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$model = aes_decrypt(params_security($_POST["model"])); // add 第一次留言 、 edit 重複留言
@$qrcode_id = aes_decrypt(params_security($_POST["qrcode_id"]));
@$qr_type_id = aes_decrypt(params_security($_POST["qr_type_id"])); // 廣告標籤 > 為了推播廣告在flex
@$history_id = aes_decrypt(params_security($_POST["history_id"]));

@$user_id0 = aes_decrypt(params_security($_POST["user_id0"])); // 留言的人 LINE的userId
@$display_name = aes_decrypt(params_security($_POST["display_name"])); // 留言的人 LINE的displayName
@$user_id1 = aes_decrypt(params_security($_POST["user_id1"])); // 接收的人 LINE的userId
@$license = aes_decrypt(params_security($_POST["license"])); // 家屬姓名

@$lat = aes_decrypt(params_security($_POST["lat"])); // 緯度 latitude
@$lng = aes_decrypt(params_security($_POST["lng"])); // 經度 longitude

@$contents0 = remove_emoji(params_security($_POST["contents0"], 'text'));

$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($contents0)) {
    array_push($err_msg, "請填寫留言內容");
}

if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
} else {
    $query = "";
    $uuid = "";

    /* 留言聯繫家屬  */
    if ($model == "add") {
        $uuid = gen_uuid();
        $query = "INSERT INTO `history`(`history_id`, `qrcode_id`, `lat`, `lng`, `user_id0`, `user_id1`, `display_name`, `contents0`, `pub_date`, `orders`) VALUES ('" . $uuid . "','" . $qrcode_id . "','" . $lat . "','" . $lng . "','" . $user_id0 . "','" . $user_id1 . "','" . $display_name . "','" . $contents0 . "',NOW(),-1)";

    } elseif ($model == "edit") {
        $uuid = $history_id;
        $query = "UPDATE `history` SET `lat` = '" . $lat . "', `lng` = '" . $lng . "', `contents0` = '" . $contents0 . "', `pub_date` = NOW() WHERE `qrcode_id` = '" . $qrcode_id . "' AND history_id = '" . $history_id . "'; ";
    }

    if ($mysqli->query($query)) {
        // 推播給家屬
        $data_arr = array(
            "model" => "toOwner",
            "user_id" => $user_id1,
            "qr_type_id" => $qr_type_id,
            "history_id" => $uuid,
            "lat" => $lat,
            "lng" => $lng,
            "license" => $license
        );
//        $url = "https://findit.linebot.tw/api/notify_line.php?model=toOwner&user_id=" . $user_id1 . "&qr_type_id=" . $qr_type_id . "&history_id=" . $uuid . "&lat=" . $lat . "&lng=" . $lng . "&license=" . $license;
        $url = "https://findit.linebot.tw/api/notify_line.php";
        download_page($url, $data_arr);
        echo "<script>alert('推播成功')</script>";
    }else{
        echo "<script>alert('推播失敗')</script>";
    }
}
echo "<script>history.go(-1)</script>";
exit;