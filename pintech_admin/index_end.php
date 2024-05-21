<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$account = params_security($_POST["account"]);
@$password = params_security($_POST["password"]);

$captcha = isset($_POST["g-recaptcha-response"]) ? $_POST['g-recaptcha-response'] : "";
$captcha_secret = "6LfloBgTAAAAAN1sO-a-opN1keTcFFzyfb0UfzPk";

if (!empty($account) && !empty($password)) {

   // $isok = json_decode(download_page("https://www.google.com/recaptcha/api/siteverify?secret=" . $captcha_secret . "&response=" . $captcha));
        $query = "SELECT * FROM admin WHERE account = '" . $account . "' AND passwd = '" . md5($password) . "' AND orders >-1";
        $result = $mysqli->query($query);
        $rows = $result->fetch_assoc();
        
        if ($result = $mysqli->query($query)) {
            $rows = $result->fetch_assoc();
            $counts = mysqli_num_rows($result);
            if ($counts == 0) {
                echo "<script>alert('密碼錯誤或帳號未啟用')</script>";
                echo "<script>history.go(-1)</script>";
                exit;
            } else {
                $_SESSION['admin'] = array();
                $_SESSION['admin']['admin_id'] = $rows['admin_id'];
                $_SESSION['admin']['account'] = $rows['account'];
                $_SESSION['admin']['nickname'] = $rows['nickname'];
                $_SESSION['admin']['permission'] = $rows['permission'];

                $query = "UPDATE admin SET last_date = now() WHERE admin_id = '" . $rows['admin_id'] . "';";
                $mysqli->query($query);

                echo "<script>alert('登入成功')</script>";
                echo "<script>document.location.href = 'init.php'</script>";
                exit;
            }
            mysqli_free_result($result);
        }
} else {
    echo "<script>alert('請正確填寫資料並勾選方塊')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
}





    // 如果成功才做，但google驗證在local端無法執行，因此先註解掉
//     if (!$isok->success) {
//         $query = "SELECT * FROM admin WHERE account = '" . $account . "' AND passwd = '" . md5($password) . "' AND orders >-1";
//         if ($result = $mysqli->query($query)) {
//             $rows = $result->fetch_assoc();
//             $counts = mysqli_num_rows($result);
//             if ($counts == 0) {
//                 echo "<script>alert('密碼錯誤或帳號未啟用')</script>";
//                 echo "<script>history.go(-1)</script>";
//                 exit;
//             } else {
//                 $_SESSION['admin'] = array();
//                 $_SESSION['admin']['admin_id'] = $rows['admin_id'];
//                 $_SESSION['admin']['account'] = $rows['account'];
//                 $_SESSION['admin']['nickname'] = $rows['nickname'];
//                 $_SESSION['admin']['permission'] = $rows['permission'];

//                 $query = "UPDATE admin SET last_date = now() WHERE admin_id = '" . $rows['admin_id'] . "';";
//                 $mysqli->query($query);

//                 echo "<script>alert('登入成功')</script>";
//                 echo "<script>document.location.href = 'init.php'</script>";
//                 exit;
//             }
//             mysqli_free_result($result);
//         }
//     } else {
//         echo "<script>alert('未通過機器人驗證功能')</script>";
//         echo "<script>history.go(-1)</script>";
//         exit;
//     }
// } else {
//     echo "<script>alert('請正確填寫資料並勾選方塊')</script>";
//     echo "<script>history.go(-1)</script>";
//     exit;
// }
