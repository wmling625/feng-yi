<?php

include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$admin_id = params_security($_POST["admin_id"]);
@$model = params_security($_POST["model"]);

@$nickname = params_security($_POST["nickname"]);
@$account = params_security($_POST["account"]);
@$passwd = params_security($_POST["password"]);
@$passwd2 = params_security($_POST["password2"]);
@$qr_type_big_id  = params_security($_POST['qr_type_big_id']);
@$qrcode_big_id  = params_security($_POST['qrcode_big_id']);
@$permission = params_security($_POST["permission"], "none"); // 因為是json 符號可能會被過濾掉
@$note = params_security($_POST["note"], "text");


if (empty($nickname) || empty($account)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
} else {
    if ($passwd != $passwd2) {
        array_push($err_msg, "兩次密碼填寫不同");
    }
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {

    $query = "";
    if ($model == "add") {
        $query = "insert into admin (`admin_id`, `nickname`, `account`, `passwd`, `permission`, `qr_type_big_id`, `qrcode_big_id`, `note`, `pub_date`, `last_date`, `orders`) values (uuid(),'" . $nickname . "','" . $account . "','" . md5($passwd) . "','" . $permission . "','" . $qr_type_big_id . "','" . $qrcode_big_id . "','" . $note . "',now(),now(),1)";
    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        if ($passwd == "") {
            $query = "update admin set nickname = '" . $nickname . "', account = '" . $account . "', permission = '" . $permission . "', qr_type_big_id = '" . $qr_type_big_id . "', qrcode_big_id = '" . $qrcode_big_id . "', note = '" . $note . "' ";
        } else {
            $query = "update admin set nickname = '" . $nickname . "', account = '" . $account . "', passwd = '" . md5($passwd) . "', permission = '" . $permission . "', qr_type_big_id = '" . $qr_type_big_id . "', qrcode_big_id = '" . $qrcode_big_id . "', note = '" . $note . "' ";
        }

        $query .= "where admin_id = '" . $admin_id . "';";
    }
    
    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }


}

