<?php
include_once(dirname(__FILE__) . "/phplibs/front_head.php");

/* QRCode 轉跳頁 */
@$profile = params_security($_GET["profile"]);
@$qr_type_big_id = params_security(aes_decrypt($_GET['qr_type_big_id'])); // 單位條碼uuid

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
} else {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

/*
 * 當QRCode被掃描時，應先判斷其身分
 * (1) 是否已發送給會員。未發送的QRCode需要進行綁定，已發送則需要判斷是否已綁定
 * (2) [家屬 ] 是否為這個 QRCode 的擁有者，又分為 已綁定=1 或 未綁定=-1
 * (3) [民眾方] 想要留言給家屬
 */
$isBind = false; // 是否已綁定該標籤
$link = "";

$member_arr = array();
$result_arr = array();
$bind_arr = array();

$query = "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT *, 1 AS 'qr_type_big' FROM `qr_type_big` WHERE qr_type_big_id = '" . $qr_type_big_id . "'; ";
$query .= "SELECT A.*, 1 AS 'qrcode_big' FROM `qrcode_big` A INNER JOIN member B ON A.member_id = B.member_id WHERE A.qr_type_big_id = '" . $qr_type_big_id . "' AND B.user_id = '" . $profile_json['userId'] . "'; ";
if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member'])) {
                    $member_arr[] = $row;
                }
                if (isset($row['qr_type_big'])) {
                    $result_arr[] = $row;
                }
                if (isset($row['qrcode_big'])) {
                    $bind_arr[] = $row;
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

/* 如果無此QRCode */
if (count($result_arr) === 0) {
    echo "<script>alert('查無資訊，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "'</script>";
    exit();
}

/* 如果被停用 */
if ($result_arr[0]['orders'] == "-1") {
    echo "<script>alert('QRCode已被停用，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "'</script>";
    exit();
}

/* 如果當前掃描的會員不存在 or 會員資料不齊全(手機、姓名、性別、活動區域)，那就需要先註冊/補齊資料、再綁定 */
if (count($member_arr) === 0 || (count($member_arr) > 0 && (empty($member_arr[0]["account"]) || empty($member_arr[0]["nickname"]) || empty($member_arr[0]["types_option"])))) {
    $link = "big_login.php?redirect=" . aes_encrypt("big_fill_in.php?qr_type_big_id=" . aes_encrypt($qr_type_big_id)) . "&profile=" . $profile;
} else {
    /* 判斷是否已綁定 */
    if (count($bind_arr) > 0) {
        $link = "big_my_info.php?qrcode_big_id=" . aes_encrypt($bind_arr[0]["qrcode_big_id"]) . "&profile=" . $profile; // 單位資訊頁
    } else {
        $link = "big_fill_in.php?qr_type_big_id=" . aes_encrypt($qr_type_big_id) . "&profile=" . $profile; // 會員資料綁定頁
    }
}

echo "<script>document.location.href = '" . $link . "'</script>";
exit();