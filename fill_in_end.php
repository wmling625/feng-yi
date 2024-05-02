<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$qrcode_id = aes_decrypt(params_security($_POST["qrcode_id"]));
@$member_id = aes_decrypt(params_security($_POST["member_id"]));
@$is_send = aes_decrypt(params_security($_POST["is_send"]));
@$profile = params_security($_POST["profile"]);
@$title = params_security($_POST["tel"]);
@$introd = params_security($_POST["introd"]);
@$contents = params_security($_POST["contents"]);

$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($introd) || empty($contents)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
} else {
    if (!validate_twmobile($title)) {
        array_push($err_msg, "行動電話格式錯誤");
    }
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

    // 綁定QRCode
    if (!$is_send) {
        // 如果還沒發送給會員，那就要更新member_id、is_send = 1
        $query = "UPDATE `qrcode` SET `member_id`='" . $member_id . "',`title`='" . $title . "',`introd`='" . $introd . "',`contents`='" . $contents . "',`last_date`=NOW(),`is_send`= 1,`orders`= 1 WHERE `qrcode_id` = '" . $qrcode_id . "' ";
    } else {
        // 如果已經發送過了，那就正常綁定家屬 資訊
        $query = "UPDATE `qrcode` SET `title`='" . $title . "',`introd`='" . $introd . "',`contents`='" . $contents . "',`last_date`=NOW(),`orders`= 1 WHERE `qrcode_id` = '" . $qrcode_id . "' AND `member_id` = '" . $member_id . "'";
    }

    $mysqli->query($query);
    echo "<script>alert('綁定成功')</script>";
    echo "<script>document.location.href='code_list.php?profile=" . $profile . "'</script>";
    exit;

}