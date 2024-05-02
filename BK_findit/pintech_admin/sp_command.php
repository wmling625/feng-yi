<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$cmd = aes_decrypt(params_security($_GET["cmd"]));
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);

if (!empty($cmd) and !empty($value) and !empty($token)) {


    if (token_validation($value, $token)) {

        $mysqli->query($cmd);
        if (strpos($cmd, "DELETE") !== false || strpos($cmd, "delete") !== false) {
            echo "<script>alert('刪除成功')</script>";
        } else if (strpos($cmd, "UPDATE") !== false || strpos($cmd, "delete") !== false) {
            echo "<script>alert('異動成功')</script>";
        }


        if (strpos($_SERVER['HTTP_REFERER'], "_list.php") !== false) {
            //從列表來的通常返回列表, 刪除跟調整排序
            echo "<script>document.location.href = document.referrer</script>"; //可以說明不用返回上一頁原因
        } else if (strpos($_SERVER['HTTP_REFERER'], "_mang.php") !== false) {
            //從內頁來的通常是返回內頁, 刪除檔案或照片, 用返回方式才能保有textarea內的文字
            echo "<script>document.location.href = document.referrer</script>"; //可以說明不用返回上一頁原因
        }

        exit;

    } else {
        echo "<script>alert('資料逾時請重新登入')</script>";
        echo "<script>document.location.href = 'logout.php'</script>";
        exit;
    }


}