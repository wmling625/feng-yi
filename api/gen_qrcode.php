<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$query = "";
$result_arr = array();
$arrData = array();

$query = "SELECT * FROM `qrcode` WHERE file0 = ''; ";
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $result_arr[] = $row;
    }
    mysqli_free_result($result);
}

if (count($result_arr) > 0) {
    foreach ($result_arr as $value) {
        $file0 = "";
        /* 產生QRCode圖片 */
        $link = "https://liff.line.me/1657192181-Wn1RV2db?end_point=" . aes_encrypt("code_redirect.php?qrcode_id=" . aes_encrypt($value['qrcode_id']));

        $temp = [];
        $temp["str"] = $link; // QRCode存放內容
        $temp["end_dir"] = dirname(__FILE__) . "/../uploads/qrcode/"; // QRCODE通常在後台產生, 所以uploads會在上一層
        $temp["end_name"] = $value['qrcode_id'] . ".png"; // QRCODE存檔名

        $temp_callback = qrcode_save($temp);
        if ($temp_callback["state"] == "-1") {
            echo "<script>alert('" . $temp_callback["message"] . "')</script>";
            echo "<script>history.go(-1)</script>";
            exit;
        } else {
            $file0 = $temp_callback["data"]["end_name"]; // 取回檔名後, 記得存回欄位, 不要每次都重新產生.., 返回的
            $query .= "UPDATE `qrcode` SET `file0`='" . $file0 . "',`last_date`=NOW() WHERE qrcode_id = '" . $value['qrcode_id'] . "'; ";
        }
    }


    if ($mysqli->multi_query($query)) {
        while ($mysqli->more_results() && $mysqli->next_result()) ;
        echo "<script>alert('新增成功')</script>";
        exit();
    }
} else {
    echo "<script>alert('查無資訊')</script>";
    echo "<script>window.close()</script>";
    exit();
}

