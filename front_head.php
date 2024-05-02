<?php
include_once(dirname(__FILE__) . "/./root.php");
include_once(dirname(__FILE__) . "/./root_global.php");
//include_once(dirname(__FILE__) . "/./SimpleImage_areafill.php");
//include_once(dirname(__FILE__) . "/./PHPMailer-master/mail_config.php");
//include_once(dirname(__FILE__) . "/./page.class.php");
//include_once(dirname(__FILE__) . "/./sms/mitake_sms/sms.php");
//include_once(dirname(__FILE__) . "/./backend_custom.php");

$filename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']); //取得目前檔名

//if (strpos($_SERVER['PHP_SELF'], "index") !== false && isset($_SESSION['member'])) {
//    echo "<script>document.location.href = 'code_list.php'</script>";
//    exit();
//}

//if (!array_key_exists('member', $_SESSION) || !isset($_SESSION['member'])) {
//    echo '無seesion';
//} else {
//    echo '有session';
//    print_r($_SESSION['member']);
//}
//unset($_SESSION['member']);
if (strpos($_SERVER['PHP_SELF'], "login") !== false || strpos($_SERVER['PHP_SELF'], "index") !== false || strpos($filename, "signup") !== false
    || strpos($_SERVER['PHP_SELF'], "logout") !== false || strpos($_SERVER['PHP_SELF'], "sms_") !== false
    || strpos($_SERVER['PHP_SELF'], "captcha64_") !== false || strpos($_SERVER['PHP_SELF'], "terms") !== false || strpos($_SERVER['PHP_SELF'], "/info") !== false || strpos($_SERVER['PHP_SELF'], "/code_redirect") !== false) {

    // 只要是 首頁/登入/登出/初始/特殊頁 任一頁都不需要驗證權限
    if (strpos($_SERVER['PHP_SELF'], "login") !== false && isset($_SESSION['member'])) {
        echo "<script>document.location.href = 'code_list.php'</script>";
    }
} else {
    // 如果$_SESSION['member']不存在
    if (!array_key_exists('member', $_SESSION) || !isset($_SESSION['member']['member_id'])) {
        $link = "https://liff.line.me/1657192181-Wn1RV2db?end_point=" . aes_encrypt("login.php");
        echo "<script>alert('如欲使用，請先登入服務');</script>";
        echo "<script>document.location.href = '" . $link . "'</script>";
        exit();
    } else {
        $query = "SELECT * FROM member WHERE member_id = '" . $_SESSION["member"]["member_id"] . "' AND orders >=0";

        if ($result = $mysqli->query($query)) {
            $rows = $result->fetch_assoc();
            $counts = mysqli_num_rows($result);
            if ($counts == 0) {
                unset($_SESSION['member']);
                echo "<script>alert('您的帳號已被停權，請聯繫管理員。');</script>";
                echo "<script>document.location.href = 'https://liff.line.me/1657192181-OgEgXVG0';</script>"; // 關閉
                exit;
            }
            mysqli_free_result($result);
        }
    }
}