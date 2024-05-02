<?php

include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$qr_type_big_id = params_security($_POST["qr_type_big_id"]);
@$model = params_security($_POST["model"]);

@$title = params_security($_POST["title"]);
//@$contents = params_security($_POST["contents"], "html");

if (empty($title)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";
    $file_sql_arr = [];

    if ($model == "add") {
        $query = "insert into qr_type_big (`qr_type_big_id`, `title`, `pub_date`, `last_date`, `orders`) values (uuid(),'" . $title . "',now(),now(),1)";
    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        $query = "update qr_type_big set title = '" . $title . "', last_date = now()  ";
        if (count($file_sql_arr) > 0) {
            $query .= "," . implode(",", $file_sql_arr);
        }
        $query .= "where qr_type_big_id = '" . $qr_type_big_id . "';";
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }


}

