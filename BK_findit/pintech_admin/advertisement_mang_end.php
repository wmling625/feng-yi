<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$advertisement_id = params_security($_POST["advertisement_id"]);
@$model = params_security($_POST["model"]);

@$title = params_security($_POST["title"]);
@$introd = params_security($_POST["introd"]);
@$sponsor = params_security($_POST["sponsor"]);
@$start_date = params_security($_POST["start_date"]);
@$end_date = params_security($_POST["end_date"]);
@$contents = params_security($_POST["contents"], "text");
@$note = params_security($_POST["note"], "text");
@$types_option = params_security($_POST["types_option"]);
@$types_box = params_security($_POST["types_box"], 'none');
@$types_box2 = params_security($_POST["types_box2"], 'none');

if (empty($title) || empty($types_option)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
} else {
    if (!empty($introd) && !validate_url($introd)) {
        array_push($err_msg, "您輸入的連結無效，請檢查");
    }
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";

    $file_sql_arr = [];

    // 先產生與圖片(附件)欄位名相同的變數，有多少個欄位就產生幾個變數
    $file_array = array("file0", "file1");
    foreach ($file_array as $key => $value) {
        ${$value} = "";
    }


    // 上傳位置
    $upload_dir = "../uploads/others/";

    for ($i = 0; $i <= count($file_array) - 1; $i++) {

        // 副檔名限制
        if ($file_array[$i] == "file0") {
            $limited_ext = array(".gif", ".jpg", ".jpeg", ".png", ".bmp", ".pdf");
        } elseif ($file_array[$i] == "file1") {
            $limited_ext = array(".mp4", ".m4v", ".webm");
        }

        $new_file = $_FILES['file' . $i];
        // 避免使用者上傳的副檔名為大寫，會無法比對。所以統一轉小寫
        $ext = "." . strtolower(pathinfo($new_file["name"], PATHINFO_EXTENSION));

        $option = array();
        $option["width"] = 600;
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

    if ($model == "add") {
        $query = "insert into advertisement (`advertisement_id`, `title`, `introd`, `contents`, `file0`, `file1`, `start_date`, `end_date`, `types_option`, `types_box`, `types_box2`, `note`, `pub_date`, `last_date`, `orders`) values (uuid(),'" . $title . "','" . $introd . "','" . $contents . "','" . $file0 . "','" . $file1 . "','" . $start_date . "','" . $end_date . "','" . $types_option . "','" . $types_box . "','" . $types_box2 . "','" . $note . "',now(),now(),1)";
    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        $query = "update advertisement set title = '" . $title . "', introd = '" . $introd . "', contents = '" . $contents . "', start_date = '" . $start_date . "', end_date = '" . $end_date . "', types_option = '" . $types_option . "', types_box = '" . $types_box . "', types_box2 = '" . $types_box2 . "', note = '" . $note . "', last_date = now()  ";
        if (count($file_sql_arr) > 0) {
            $query .= "," . implode(",", $file_sql_arr);
        }
        $query .= "where advertisement_id = '" . $advertisement_id . "';";
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }


}

