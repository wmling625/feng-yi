<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$gallery_id = params_security($_POST["gallery_id"]);
@$model = params_security($_POST["model"]);

//@$title = params_security($_POST["title"]);

if (empty($gallery_id)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}

if (count($err_msg)) {
//    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
//    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";
    $file_sql_arr = [];

    // 先產生與圖片(附件)欄位名相同的變數，有多少個欄位就產生幾個變數
    $file_array = array("file0");
    foreach ($file_array as $key => $value) {
        ${$value} = "";
    }

    // 上傳位置
    $upload_dir = "../uploads/others/";
    // 副檔名限制
    $limited_ext = array(".gif", ".jpg", ".jpeg", ".png", ".bmp");

    for ($i = 0; $i <= count($file_array) - 1; $i++) {

        $new_file = $_FILES['file' . $i];
        // 避免使用者上傳的副檔名為大寫，會無法比對。所以統一轉小寫
        $ext = "." . strtolower(pathinfo($new_file["name"], PATHINFO_EXTENSION));

        $option = array();
        $option["width"] = 1500;
//        $option["height"] = 768;
//        $option["valign"] = "middle";
//        $option["file_name"] = mb_pathinfo($_FILES['file' . $i], PATHINFO_FILENAME);

        $up_arr = file_upload($limited_ext, $upload_dir, $new_file, $option);
        $up_state = $up_arr["up_state"];
        $up_name = $up_arr["up_name"];
        $up_message = $up_arr["up_message"];

        if ($up_state == 0) {
            // 上傳失敗
//            echo "<script>alert('" . $up_message . "')</script>";
//            echo "<script>history.go(-1)</script>";
//            exit;
        } else if ($up_state == 1 && $up_name != "") {
            // 上傳成功且有檔案名稱(為了避免使用者重複按到，但是沒有上傳任何檔案)
            ${$file_array[$i]} = $up_name;

            // 拼接sql update指令，ex: file0 = "file_name.jpg"
            $temp = strval(" " . $file_array[$i] . " = '" . $up_name . "'");
            array_push($file_sql_arr, $temp);

        }
    }

    if ($model == "add") {
        if ($up_state == "1") {
            $query = "insert into gallery_photo (`gallery_photo_id`, `gallery_id`,`file0`, `pub_date`, `last_date`, `orders`) values (uuid(),'" . $gallery_id . "','" . $file0 . "',now(),now(),99)";
            $mysqli->query($query);
        }
        echo json_encode(array('status' => $up_state));

    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
//        $query = "update gallery_photo set title = '" . $title . "', last_date = now()  ";
//        if (count($file_sql_arr) > 0) {
//            $query .= "," . implode(",", $file_sql_arr);
//        }
//        $query .= "where gallery_photo_id = '" . $gallery_photo_id . "';";
    }


//    echo "<script>alert('儲存成功')</script>";
//    echo "<script>history.go(-2)</script>";
//    exit;

}

