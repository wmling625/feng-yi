<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$term_id = params_security($_POST["term_id"]);
@$model = params_security($_POST["model"]);

@$title = params_security($_POST["title"]);
@$contents = params_security($_POST["contents"], 'html');

$err_msg = [];

if (empty($title) || empty($contents)) {
    array_push($err_msg, "必填欄位未填寫");
}

if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
} else {
    $query = "";
    if ($model == "update") {
        $query = "update term set title = '" . $title . "', contents = '" . $contents . "' where term_id = '" . $term_id . "' ";
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
        echo "<script>history.go(-2)</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
        echo "<script>history.go(-1)</script>";
    }
}