<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

/* QRCode 轉跳頁 */
@$profile = params_security($_GET["profile"]);
@$qrcode_id = aes_decrypt(params_security($_GET['qrcode_id']));

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
}
//else {
//    echo "<script>document.location.href = 'https://liff.line.me/".$liff_close."';</script>";
//    exit;
//}

/*
 * 當QRCode被掃描時，應先判斷其身分
 * (1) 是否已發送給會員。未發送的QRCode需要進行綁定，已發送則需要判斷是否已綁定
 * (2) [家屬 ] 是否為這個 QRCode 的擁有者，又分為 已綁定=1 或 未綁定=-1
 * (3) [民眾方] 想要留言給家屬
 */
$hasBug = false; // 如果有綁家屬 資訊，但是沒綁定會員
$memberExist = true; // 會員是否存在
$canUse = true; // 是否可以使用，true > 可以使用、false > QRCode被停用
$isSend = false; // 是否已發送，true > 已發送給會員、false > 尚未發送給會員
$isOwner = false; // 是否是擁有者，true > QRCode擁有者、false > QRCode非擁有者
$isBind = false; // 是否已綁定，true > 已綁定家屬 資訊、false > 未綁定家屬 資訊
$link = "";

$member_arr = array();
$result_arr = array();

$query = "";
$query .= "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT *, 1 AS 'qrcode' FROM `qrcode` WHERE qrcode_id = '" . $qrcode_id . "'; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member'])) {
                    $member_arr[] = $row;
                }
                if (isset($row['qrcode'])) {
                    $result_arr[] = $row;
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

/* 如果有此QRCode */
if (count($result_arr) == 1) {
    /* 如果被停用 */
    if ($result_arr[0]['orders'] == "-2") {
        $canUse = false;
    } else {
        /* 如果已發送給會員 */
        if ($result_arr[0]['is_send'] == "1") {
            $isSend = true;
            if (isset($member_arr[0]['member_id']) && $member_arr[0]['member_id'] !== "") {
                /* 判斷會員id是否相符，相符就代表這個會員是QRCode的擁有者 */
                if ($result_arr[0]['member_id'] == $member_arr[0]['member_id']) {
                    $isOwner = true;
                    /* 判斷是否已綁定家屬 資訊 */
                    if ($result_arr[0]['orders'] == "1") {
                        $isBind = true;
                    }
                }
            }
        } else {
            /* 如果未發送，且當前掃描的會員不存在，那就需要先註冊、再綁定 */
            if (count($member_arr) == 0) {
                $memberExist = false;
            }
            /* 如果未發送，但是卻有綁定家屬 ，代表有問題 */
            if ($result_arr[0]['member_id'] == "" && $result_arr[0]['orders'] == "1") {
                $hasBug = true;
            }
        }
    }
} else {
    echo "<script>alert('查無資訊，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "'</script>";
    exit();
}

/* 判斷是否可用 */
if ($canUse) {
    /* 判斷是否有問題 */
    if (!$hasBug) {
        /* 判斷是否發送 */
        if (!$isSend) {
            $link = "fill_in.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&profile=" . $profile . "&is_send=" . aes_encrypt(false); // 家屬 綁定頁，尚未發送給會員(需要更新is_send狀態 + 更新member_id)
            /* 如果未發送，且會員不存在，就先請會員註冊 */
            if (!$memberExist) {
                $link = "login.php?redirect=" . aes_encrypt("fill_in.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&is_send=" . aes_encrypt(false)) . "&profile=" . $profile;
            }
        } else {
            /* 判斷是否為擁有者 */
            if ($isOwner) {
                /* 判斷是否已綁定 */
                if (!$isBind) {
                    $link = "fill_in.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&profile=" . $profile . "&is_send=" . aes_encrypt(true); // 家屬 綁定頁，已發送給會員(不須更新is_send狀態)
                } else {
                    $link = "my_info.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&profile=" . $profile; // 家屬 資訊頁
                }
            } else {
                $link = "info.php?qrcode_id=" . aes_encrypt($qrcode_id); // 民眾留言頁
            }
        }
    } else {
        $link = "https://liff.line.me/" . $liff_close;
        echo "<script>alert('QRCode資訊有誤，請聯繫客服')</script>";
    }
} else {
    $link = "https://liff.line.me/" . $liff_close;
    echo "<script>alert('QRCode已被停用，請聯繫客服')</script>";
}


echo "<script>document.location.href = '" . $link . "'</script>";
exit();