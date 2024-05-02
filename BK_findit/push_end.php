<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$model = params_security($_POST["model"]);
@$qrcode_id = aes_decrypt(params_security($_POST["qrcode_id"]));
@$user_id = aes_decrypt(params_security($_POST["userId"])); // 留言的人 LINE的userId
@$display_name = aes_decrypt(params_security($_POST["display_name"])); // 留言的人 LINE的displayName
@$push_id = aes_decrypt(params_security($_POST["push_id"])); // 接收的人 LINE的userId
@$contents = params_security($_POST["contents"], 'text');

$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($contents)) {
    array_push($err_msg, "請填寫留言內容");
} else {
//    if (!validate_twmobile($title)) {
//        array_push($err_msg, "行動電話格式錯誤");
//    }
//    if ($authcode !== $_SESSION["authcode"]) {
//        array_push($err_msg, "圖片驗證碼錯誤");
//    }
}

if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";


    if ($model == "push") {
        // 推播留言聯繫車主
        $query = "INSERT INTO `history`(`history_id`, `qrcode_id`, `user_id`, `display_name`, `push_id`, `contents`, `pub_date`, `last_date`, `orders`) VALUES (UUID(),'" . $qrcode_id . "','" . $user_id . "','" . $display_name . "','" . $push_id . "','" . $contents . "',NOW(),NOW(),1)";

        // 之後要推播LINE給車主


    } elseif ($model == "reply") {
        // 車主回覆民眾
        $query = "INSERT INTO `history`(`history_id`, `user_id`, `title`, `contents`, `pub_date`, `last_date`, `orders`) VALUES (UUID(),'" . $user_id . "','車主回覆民眾','" . $contents . "',NOW(),NOW(),1)";
    }

    $mysqli->query($query);
    echo "<script>alert('推播成功')</script>";
    echo "<script>history.go(-1)</script>";
    exit;

}