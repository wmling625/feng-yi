<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$model = aes_decrypt(params_security($_POST["model"])); // add 第一次回覆 、 edit 重複回覆
@$history_id = aes_decrypt(params_security($_POST["history_id"]));
@$qr_type_id = aes_decrypt(params_security($_POST["qr_type_id"])); // 廣告標籤 > 為了推播廣告在flex

@$user_id0 = aes_decrypt(params_security($_POST["user_id0"])); // 留言的人 LINE的userId
@$display_name = aes_decrypt(params_security($_POST["display_name"])); // 留言的人 LINE的displayName
@$user_id1 = aes_decrypt(params_security($_POST["user_id1"])); // 家屬  LINE的userId
@$license = aes_decrypt(params_security($_POST["license"])); // 家屬姓名

@$contents0 = params_security($_POST["contents0"], 'text');
@$contents1 = remove_emoji(params_security($_POST["contents1"], 'text'));

@$id = '1';
$setting_arr = array();
$domain = '';
$query = "SELECT * FROM setting WHERE id = '" . $id . "';";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $setting_arr[] = $rows;
    mysqli_free_result($result);
}

$domain = $setting_arr[0]["domain"];

$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($contents1)) {
    array_push($err_msg, "請填寫留言內容");
}

if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";


    $uuid = gen_uuid();

    $select_query = "SELECT * FROM `history` WHERE `history_id` = '$history_id'";
    $result = $mysqli->query($select_query);

    if ($result && $result->num_rows > 0) {
        $query = "INSERT INTO `history_list` (`history_id`, `content`, `last_date`) 
          VALUES ('" . $history_id . "', '" . $contents1 . "', NOW())";
        $mysqli->query($query);
    }
    // 家屬 回覆民眾
    $query = "UPDATE `history` SET `orders`= 1 WHERE `history_id`='" . $history_id . "' ";
    $mysqli->query($query);

    // 推播給民眾
    $data_arr = array(
        "model" => "toPeople",
        "user_id" => $user_id0,
        "qr_type_id" => $qr_type_id,
        "history_id" => $history_id,
        "contents1" => $contents1,
        "license" => $license,
        'reply_user' => $user_id1
    );
    //        $url = "https://oneqrcode.feng-yi.tw/api/notify_line.php?model=toPeople&user_id=" . $user_id0 . "&qr_type_id=" . $qr_type_id . "&history_id=" . $history_id . "&contents1=" . urlencode($contents1) . "&license=" . $license;
    $url = "https://" . $domain . "/api/notify_line.php";
    download_page($url, $data_arr);

    echo "<script>alert('推播成功')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
}
