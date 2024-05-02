<?php
include_once(dirname(__FILE__) . "/./root.php");
include_once(dirname(__FILE__) . "/./root_global.php");
include_once(dirname(__FILE__)."/./sms/mitake_sms/sms.php");

$filename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']); //取得目前檔名

//@$profile = params_security($_GET["profile"]);
//if (!empty($profile)) {
//    $profile_json = json_decode(aes_decrypt($profile), true);
//} else {
//    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
//    exit;
//}
//
//
////unset($_SESSION['member']);
//if (strpos($_SERVER['PHP_SELF'], "login") !== false || strpos($_SERVER['PHP_SELF'], "index") !== false || strpos($filename, "signup") !== false
//    || strpos($_SERVER['PHP_SELF'], "logout") !== false || strpos($_SERVER['PHP_SELF'], "sms_") !== false
//    || strpos($_SERVER['PHP_SELF'], "captcha64_") !== false || strpos($_SERVER['PHP_SELF'], "terms") !== false
//    || strpos($_SERVER['PHP_SELF'], "/info") !== false || strpos($_SERVER['PHP_SELF'], "_redirect") !== false) {
//    // 只要是 首頁/登入/登出/初始/特殊頁 任一頁都不需要驗證權限
//    if (strpos($_SERVER['PHP_SELF'], "login") !== false && isset($_SESSION['member'])
//        && (isset($_SESSION['member']['member_id']) && $_SESSION['member']['member_id'] !== "")) {
//        echo "<script>document.location.href = 'code_list.php'</script>";
//    }
//} else {
//    // 如果$_SESSION['member']不存在
//    if (!array_key_exists('member', $_SESSION) && !isset($_SESSION['member']['member_id'])) {
//        $redirect = strrchr($_SERVER['REQUEST_URI'], "/");
//        $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("login.php#" . $redirect);
//
//        echo "<script>alert('如欲使用，請先登入服務');</script>";
//        echo "<script>document.location.href = '" . $link . "'</script>";
//        exit();
//    } else {
//        $query = "SELECT * FROM member WHERE member_id = '" . $_SESSION["member"]["member_id"] . "' AND orders >=0";
//
//        if ($result = $mysqli->query($query)) {
//            $rows = $result->fetch_assoc();
//            $counts = mysqli_num_rows($result);
//            if ($counts == 0) {
//                unset($_SESSION['member']);
//                echo "<script>alert('停留過久，已將您登出服務');</script>";
//                echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>"; // 關閉
//                exit;
//            }
//            mysqli_free_result($result);
//        }
//    }
//}