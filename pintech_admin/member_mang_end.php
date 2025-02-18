<?php

include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");
error_reporting(E_ALL);
ini_set('display_errors', 'On');
$err_msg = array();

@$member_id = params_security($_POST["member_id"]);
@$model = params_security($_POST["model"]);

@$account = params_security($_POST["account"]);
@$nickname = params_security($_POST["nickname"]);
@$types_option = params_security($_POST["types_option"]);
@$city = params_security($_POST["city"]);
@$region = params_security($_POST["region"]);
@$qr_type_big_id = params_security($_POST["qr_type_big_id"]);
@$types = params_security($_GET["types"]);

$communities = [];

if (!empty($_POST['form']['label']) && !empty($_POST['form']['name'])) {
    foreach ($_POST['form']['label'] as $index => $label) {
        $communities[] = [
            'label' => params_security($label),
            'name' => params_security($_POST['form']['name'][$index])
        ];
    }
}
$form = json_encode($communities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


if (empty($nickname) || empty($types_option) || empty($account)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}


if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
} else {
    $query = "";
    if ($model == "add") {
        $query = "SELECT COUNT(*) AS counts FROM member WHERE account = '" . $account . "'";
        if ($result = $mysqli->query($query)) {
            $rows = $result->fetch_assoc();
            $counts = $rows["counts"];
            if ($counts >= 1) {
                echo "<script>alert('此手機已註冊過')</script>";
                echo "<script>history.go(-1)</script>";
                exit;
            }
            mysqli_free_result($result);
        }

        $query = "INSERT INTO `member`(`member_id`, `account`, `user_id`, `title`, `nickname`, `types_option`, `form`, `pub_date`, `last_date`, `orders`) VALUES (uuid(),'" . $account . "','" . $userId . "','" . $title . "','" . $nickname . "','" . $types_option . "','" . $mysqli->real_escape_string($form) . "', NOW(), NOW(), 1) ON DUPLICATE KEY UPDATE `user_id` = '" . $userId . "', `title` = '" . $title . "', `nickname` = '" . $nickname . "', `types_option` = '" . $types_option . "', `city` = '" . $city . "', `region` = '" . $region . "', `last_date` = NOW(), `orders` = 1";
    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        $query = "update member set qr_type_big_id = '" . $qr_type_big_id . "',account = '" . $account . "',nickname = '" . $nickname . "',types_option = '" . $types_option . "',form = '" . $mysqli->real_escape_string($form) . "',last_date = NOW() ";
        $query .= "where member_id = '" . $member_id . "';";
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }
}
exit;
