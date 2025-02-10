<?php

include_once(dirname(__FILE__) . "/./phplibs/front_head.php");
$err_msg = array();

@$ids = params_security($_POST['ids']); // 單位id
@$message = params_security($_POST['message'], 'text'); // 推播訊息內容
@$id = '1';
$setting_arr = array();
$domain = '';
$query = "SELECT * FROM setting WHERE id = '" . $id . "';";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $setting_arr[] = $rows;
    mysqli_free_result($result);
}

$domain = $setting_arr[0]["domain"];

if (empty($ids) || empty($message)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
} else {
    $query = "";

    $file_sql_arr = [];

    // 先產生與圖片(附件)欄位名相同的變數，有多少個欄位就產生幾個變數
    $file_array = array("file0", "file1");
    foreach ($file_array as $key => $value) {
        ${$value} = "";
    }

    // 上傳位置
    $upload_dir = "./uploads/others/";
    for ($i = 0; $i <= count($file_array) - 1; $i++) {

        // 副檔名限制
        if ($file_array[$i] == "file0") {
            $limited_ext = array(".gif", ".jpg", ".jpeg", ".png", ".bmp", ".pdf");
        } elseif ($file_array[$i] == "file1") {
            $limited_ext = array(".mp4", ".m4v", ".webm");
        }


        if (isset($_FILES['file' . $i])) {
            $new_file = $_FILES['file' . $i];
            // 避免使用者上傳的副檔名為大寫，會無法比對。所以統一轉小寫
            $ext = "." . strtolower(pathinfo($new_file["name"], PATHINFO_EXTENSION));

            $option = array();
            $option["width"] = 1024;
            //        $option["height"] = 768;
            //        $option["valign"] = "middle";
            //        $option["file_name"] = mb_pathinfo($_FILES['file' . $i], PATHINFO_FILENAME);

            $up_arr = file_upload($limited_ext, $upload_dir, $new_file, $option);

            $up_state = $up_arr["up_state"];
            $up_name = $up_arr["up_name"];
            $up_message = $up_arr["up_message"];

            if ($up_state == 0) {
                // 上傳失敗
                echo "<script>alert('" . $up_message . "')</script>";
                echo "<script>history.go(-1)</script>";
                exit;
            } else if ($up_state == 1 && $up_name != "") {
                // 上傳成功且有檔案名稱(為了避免使用者重複按到，但是沒有上傳任何檔案)
                ${$file_array[$i]} = $up_name;

                // 拼接sql update指令，ex: file0 = "file_name.jpg"
                $temp = strval(" " . $file_array[$i] . " = '" . $up_name . "'");
                array_push($file_sql_arr, $temp);
            }
        }
    }


    $result_arr = array();
    $query = "SELECT A.`qrcode_big_id`, B.`user_id` FROM `qrcode_big` AS A INNER JOIN `member` AS B ON A.`member_id` = B.`member_id` WHERE find_in_set(A.`qrcode_big_id`, '" . $ids . "') >0 AND A.`orders` >= 0 AND B.`orders` >= 0 ";
    // var_dump($query);exit;

    $query1 = "";
    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        if ($total > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $result_arr[] = $row['user_id'];

                $uuid = gen_uuid();
                $query1 .= "INSERT INTO `history`(`history_id`, `qrcode_big_id`, `user_id1`, `display_name`, `contents0`, `file0`, `file1`, `pub_date`, `orders`) VALUES ('" . $uuid . "','" . $row['qrcode_big_id'] . "','" . $row['user_id'] . "','系統推播','" . $message . "','" . $file0 . "','" . $file1 . "',NOW(),2); ";
            }
        } else {
            echo "<script>alert('查無符合對象')</script>";
            echo "<script>history.go(-1)</script>";
            exit;
        }
        mysqli_free_result($result);
    } else {
        echo "<script>alert('推播失敗" . mysqli_error($mysqli) . "')</script>";
        echo "<script>history.go(-1)</script>";
        exit;
    }

    // var_dump($query1);exit;
    if ($mysqli->multi_query($query1)) {
        while ($mysqli->more_results() && $mysqli->next_result());

        $result_arr = array_unique($result_arr);
        $userIds = implode(",", $result_arr);
        
        $data_arr = array(
            "model" => "toAll",
            "user_id" => $userIds,
            "contents1" => $message,
            "file0" => $file0,
            'file1' => $file1
        );
        //        $url = "https://oneqrcode.feng-yi.tw/api/notify_line.php?model=toAll&user_id=" . $userIds . "&contents1=" . urlencode($message) . "&file0=" . urlencode($file0);
        $url = "https://" . $domain . "/api/notify_line.php";
        file_put_contents(dirname(__FILE__) . "/../api/log/" . date("Ymdhis") . "big_end_ids.txt", json_encode($data_arr, JSON_UNESCAPED_UNICODE));
        download_page($url, $data_arr);
        echo "<script>alert('推播成功')</script>";
    } else {
        file_put_contents(dirname(__FILE__) . "/../api/log/" . date("Ymdhis") . "big_end_query.txt", $query1);
        echo "<script>alert('推播失敗，請聯繫工程師')</script>";
    }
}
echo "<script>history.go(-1)</script>";
exit;
