<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

@$value = params_security($_POST["value"]);
@$token = params_security($_POST["token"]);

@$userId = aes_decrypt(params_security($_POST["userId"]));
@$title = aes_decrypt(params_security($_POST["displayName"]));
@$mobile = params_security($_POST["tel"]);
@$code = params_security($_POST["code"]);
@$nickname = params_security($_POST["nickname"]);
@$types_option = params_security($_POST["types_option"]);
@$city = params_security($_POST["city"]);
@$region = params_security($_POST["region"]);
@$isLogin = aes_decrypt(params_security($_POST["isLogin"]));
@$redirect = aes_decrypt(params_security($_POST["redirect"]));
@$profile = params_security($_POST["profile"]);

$err_msg = array();

if (!token_validation($value, $token)) {
    array_push($err_msg, "value與token錯誤");
}

if (empty($mobile) || empty($nickname) || empty($types_option) || empty($city) || empty($region)) {
    array_push($err_msg, "請確認必填項目是否皆已填寫");
} else {
    if (!validate_twmobile($mobile)) {
        array_push($err_msg, "行動電話格式錯誤");
    }
    if (!$isLogin && empty($code)) {
        array_push($err_msg, "請輸入手機驗證碼");
    }
}

// 驗證手機驗證碼
$isValid = false;
if (!$isLogin) {
    /* 註冊流程 */
    if ($code === "88888") {
        $isValid = true;
    } else {
        array_push($err_msg, "驗證碼請輸入88888");
    }
//    $query = "SELECT `code` FROM `smscode` WHERE mobile = '" . $mobile . "' AND orders <= 1 AND TIMESTAMPDIFF(SECOND,pub_date,NOW()) < 305 and is_ok = -1 ORDER BY pub_date DESC LIMIT 0,1; "; // 是否在300秒內驗證
//    if ($result = $mysqli->query($query)) {
//        $total = mysqli_num_rows($result);
//        $rows = $result->fetch_assoc();
//        if ($total <= 0) {
//            array_push($err_msg, "手機驗證碼已失效");
//        } else {
//            if ($code != $rows['code']) {
//                array_push($err_msg, "手機驗證碼輸入錯誤，請確認");
//            } else {
//                $query = "UPDATE smscode SET is_ok = 1 WHERE mobile = '" . $mobile . "' AND `code` = '" . $code . "' AND orders <= 1; ";
//                $mysqli->query($query);
//                $isValid = true;
//            }
//        }
//        mysqli_free_result($result);
//    }
} else {
    /* 已註冊 > 登入流程 */
    $isValid = true;
}


if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";

    /* 已驗證簡訊 */
    if (!$isValid) {
        echo "<script>alert('手機驗證失敗，請再試一次')</script>";
        echo "<script>history.go(-1)</script>";
        exit;
    }

    /* 註冊動作、更新userId */
    $query = "INSERT INTO `member`(`member_id`, `account`, `user_id`, `title`, `nickname`, `types_option`, `city`, `region`, `pub_date`, `last_date`, `orders`) VALUES (uuid(),'" . $mobile . "','" . $userId . "','" . $title . "','" . $nickname . "','" . $types_option . "','" . $city . "','" . $region . "', NOW(), NOW(), 1) ON DUPLICATE KEY UPDATE `user_id` = '" . $userId . "', `title` = '" . $title . "', `nickname` = '" . $nickname . "', `types_option` = '" . $types_option . "', `city` = '" . $city . "', `region` = '" . $region . "', `last_date` = NOW()";
    if ($mysqli->query($query)) {
        if (!$isLogin) {
            echo "<script>alert('註冊成功，將直接為您登入，請稍後...')</script>";
        }
    }

    /* 登入動作 */
    $query = "SELECT *, 1 AS member FROM member WHERE account = '" . $mobile . "'; "; // AND user_id = '" . $userId . "'

    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        if ($total === 0) {
            echo "<script>alert('查無資訊，請確認手機是否輸入正確')</script>";
            echo "<script>history.go(-1)</script>";
        } else {
            echo "<script>alert('登入成功')</script>";
            if ($redirect !== "") {
                echo "<script>document.location.href = '" . $redirect . "&profile=" . $profile . "'</script>";
            } else {
                echo "<script>document.location.href = 'big_code_list.php?profile=" . $profile . "'</script>";
            }
        }
        mysqli_free_result($result);
        exit;
    }
}