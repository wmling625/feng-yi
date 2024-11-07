<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

$err_msg = array();

@$id = params_security($_POST["id"]);
@$model = params_security($_POST["model"]);

@$content = params_security($_POST["content"], 'text');
@$linefull = params_security($_POST["linefull"]);
@$lineclose = params_security($_POST["lineclose"]);
@$lineoauth = params_security($_POST["lineoauth"]);
@$linetoken = params_security($_POST["linetoken"]);
@$linesecret = params_security($_POST["linesecret"]);


//if (empty($content)) {
//    array_push($err_msg, "必填欄位未填寫，請檢查");
//}

if (count($err_msg)) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
} else {

    $query = "";
    if ($model == "add") {
    } else if ($model == "update") {
        // 可以對照insert欄位, 略過pub_update..等
        $query = "UPDATE `setting` SET 
              `content` = '" . $content . "', 
              `linefull` = '" . $linefull . "',
              `lineclose` = '" . $lineclose . "',
              `lineoauth` = '" . $lineoauth . "',
              `linetoken` = '" . $linetoken . "',
              `linesecret` = '" . $linesecret . "',
              `last_date` = NOW()";
        $query .= " WHERE `id` = '" . $id . "'; ";
        // `failed` = '" . $failed . "',
    }

    if ($mysqli->query($query)) {
        echo "<script>alert('儲存成功')</script>";
    } else {
        echo "<script>alert('儲存失敗')</script>";
    }
}
echo "<script>history.go(-1)</script>";
exit;
