<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$qr_type_big_id = aes_decrypt(params_security($_GET["qr_type_big_id"]));
@$title = params_security($_GET["title"]);
@$intro = params_security($_GET["intro"]);
@$contents = params_security($_POST["contents"]);
//if (!empty($profile)) {
//    $profile_json = json_decode(aes_decrypt($profile), true);
//} else {
//    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
//    exit;
//}

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
} else {
    $profile_json = array();
}

$member_arr = array();
$result_arr = array();
$bind_arr = array();
$video_arr = array(); // 影片廣告

$query = "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT *, 1 AS 'qr_type_big' FROM `qr_type_big` WHERE qr_type_big_id = '" . $qr_type_big_id . "'; ";
$query .= "SELECT *, 1 AS 'qrcode_big' FROM `qrcode_big` A INNER JOIN member B ON A.member_id = B.member_id WHERE A.qr_type_big_id = '" . $qr_type_big_id . "' AND B.user_id = '" . $profile_json['userId'] . "'; ";
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

if (count($result_arr) === 0) {
    echo "<script>alert('查無QRCode')</script>";
    echo "<script>document.location.href='code_list.php?profile=" . $profile . "'</script>";
    exit;
}

if (count($member_arr) === 0) {
    echo "<script>alert('查無會員資訊')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("big_login.php") . "';</script>";
    exit;
}

if ($result_arr[0]['orders'] == "-1") {
    echo "<script>alert('此QRCode已被停用，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

if ($member_arr[0]['orders'] !== "1") {
    echo "<script>alert('您已被停權，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

/* 已經有綁定 */
if (count($bind_arr) > 0) {
    if ($bind_arr[0]['orders'] == "-1") {
        echo "<script>alert('您的綁定紀錄已被停用，請聯繫客服')</script>";
        echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
        exit;
    }

    echo "<script>document.location.href='big_my_info.php?qrcode_big_id=" . aes_encrypt($bind_arr[0]["qrcode_big_id"]) . "&profile=" . $profile . "'</script>";
    exit;
}

$uuid = gen_uuid();
$query = "INSERT INTO `qrcode_big`(`qrcode_big_id`, `qr_type_big_id`, `member_id`, `title`, `intro`, `content`, `pub_date`, `last_date`, `orders`) VALUES ('" . $uuid . "','" . $qr_type_big_id . "','" . $member_arr[0]["member_id"] . "','" . $title . "','" . $intro . "','" . $contents . "', NOW(), NOW(), 1)";

// var_dump($mysqli->query($query));exit;

if ($mysqli->query($query)) {
    echo "<script>alert('綁定成功!')</script>";
    // echo "<script>document.location.href='big_my_info.php?qrcode_big_id=" . aes_encrypt($uuid) . "&profile=" . $profile . "'</script>";
    echo '<script>document.location.href = "' . $oa . '"</script>';
    exit;
}
