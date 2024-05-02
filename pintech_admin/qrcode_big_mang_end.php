<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$qrcode_big_id = params_security($_POST["qrcode_big_id"]);
@$model = params_security($_POST["model"]);

@$orders = params_security($_POST["orders"]);
@$qr_type_big_id = params_security($_POST["qr_type_big_id"]);
@$member_id = params_security($_POST["member_id"]);
@$code = params_security($_POST["code"]);
@$title = params_security($_POST["title"]);
@$introd = params_security($_POST["introd"]);
@$contents = params_security($_POST["contents"]);
@$number = params_security($_POST["number"]); // 批次產生數量


if (empty($orders) || empty($qr_type_big_id) || empty($code)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";
    $file_sql_arr = [];

    /* 發送狀態 */
    $is_send = "-1";
    if ($member_id !== "") {
        $is_send = "1";
    }

    if ($model == "add") {

        if (empty($number)) {
            $number = 1;
        }

        /* 批次新增 */
        $number = intval($number);
        if ($number > 0) {
            for ($i = 0; $i < $number; $i++) {
                $file0 = "";
                $codeRandom = "";
                $uuid = gen_uuid();
                $codeRandom = $code . "-" . random_str('6');

                /* 產生QRCode圖片 */
                $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_redirect.php?qrcode_big_id=" . aes_encrypt($uuid));
//                $qrcode = "https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=" . urlencode($link) . "&choe=UTF-8";

                $temp = [];
                $temp["str"] = $link; // QRCode存放內容
                $temp["end_dir"] = dirname(__FILE__) . "/../uploads/qrcode/"; // QRCODE通常在後台產生, 所以uploads會在上一層
                $temp["end_name"] = $uuid . ".png"; // QRCODE存檔名

                $temp_callback = qrcode_big_save($temp);
                if ($temp_callback["state"] == "-1") {
                    echo "<script>alert('" . $temp_callback["message"] . "')</script>";
                    echo "<script>history.go(-1)</script>";
                    exit;
                } else {
                    $file0 = $temp_callback["data"]["end_name"]; // 取回檔名後, 記得存回欄位, 不要每次都重新產生.., 返回的
                }

                $query .= "INSERT INTO qrcode_big (`qrcode_big_id`, `member_id`, `code`, `title`, `introd`, `contents`, `file0`, `qr_type_big_id`, `pub_date`, `last_date`, `is_send`, `orders`) values ('" . $uuid . "','" . $member_id . "','" . $codeRandom . "','" . $title . "','" . $introd . "','" . $contents . "','" . $file0 . "','" . $qr_type_big_id . "',now(),now(), '" . $is_send . "', '" . $orders . "'); ";
            }
        }


        if ($mysqli->multi_query($query)) {
            while ($mysqli->more_results() && $mysqli->next_result()) ;
            echo "<script>alert('新增成功')</script>";
            echo "<script>history.go(-2)</script>";
            exit;
        }

    } else if ($model == "update") {

        /* 如果將狀態改回「未綁定」，則將家屬 資訊註銷 */
        if ($orders == "-1") {
            $title = "";
            $introd = "";
            $contents = "";
        }

        $query = "update qrcode_big set code = '" . $code . "', member_id = '" . $member_id . "', title = '" . $title . "', introd = '" . $introd . "', contents = '" . $contents . "', qr_type_big_id = '" . $qr_type_big_id . "', orders = '" . $orders . "', is_send = '" . $is_send . "', last_date = now()  ";
        if (count($file_sql_arr) > 0) {
            $query .= "," . implode(",", $file_sql_arr);
        }
        $query .= "where qrcode_big_id = '" . $qrcode_big_id . "';";


        if ($mysqli->query($query)) {
            echo "<script>alert('儲存成功')</script>";
            echo "<script>history.go(-2)</script>";
            exit;
        } else {
            echo "<script>alert('QRCode代碼重複，請重新輸入')</script>";
            echo "<script>history.go(-1)</script>";
            exit;
        }
    }

}