<?php

include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$history_id = params_security($_POST["history_id"]);
@$model = params_security($_POST["model"]);

@$orders = params_security($_POST["orders"]);
@$note = params_security($_POST["note"], "text");

if (empty($orders)) {
    array_push($err_msg, "必填欄位未填寫，請檢查");
}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";

    if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        $query = "update history set note = '" . $note . "',orders = '" . $orders . "', last_date = now()  ";
        $query .= "where history_id = '" . $history_id . "';";
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }


}

