<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$cmd = aes_decrypt(params_security($_GET["cmd"]));
@$qr_type_big_id = params_security($_GET["qr_type_big_id"]);
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);

if (!empty($cmd) && !empty($qr_type_big_id) && !empty($value) && !empty($token)) {

    if (token_validation($value, $token)) {

        $cmd = str_replace("{selected_value}", $qr_type_big_id, $cmd);
        $mysqli->query($cmd); // Execute the decrypted SQL command

        if (strpos($cmd, "INSERT") !== false || strpos($cmd, "insert") !== false) {
            echo "<script>alert('綁定成功');</script>";
        }

        if (strpos($_SERVER['HTTP_REFERER'], "_mang.php") !== false) {
            echo "<script>document.location.href = document.referrer;</script>";
        }
        exit;
    } else {
        echo "<script>alert('資料逾時請重新登入');</script>";
        echo "<script>document.location.href = 'logout.php';</script>";
        exit;
    }
}
?>
