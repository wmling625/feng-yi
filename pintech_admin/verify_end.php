<?php
include_once(dirname(__FILE__) . "/../phplibs/front_head.php");

$callback = array("state" => "1", "message" => implode("、", $_POST));

@$mobile = findNum(params_security($_POST["verify_mobile"]));
@$code = params_security($_POST["verify_code"]);

$callback = array();
$err_msg = array();

if (!validate_twmobile($mobile)) {
    array_push($err_msg, "行動電話格式錯誤");
}

/* 先檢查是否有該手機 */
$query = "SELECT * FROM `member` WHERE account = '" . $mobile . "' ";
if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_assoc();
    $total = mysqli_num_rows($result);
    if ($total == 1) {
        // $needSMS = false;
        /* 檢查userId是否相符合 */
        if ($userId !== $rows["user_id"]) {
            /* 如果有該手機但是 userId 不同，那就需要更新userId */
            $sameUserId = false;
        } else {
            /* 如果有該手機且 userId 相同，則代表已註冊、且資料無變動 > 可進行登入 */
        }
    } elseif ($total == 0) {
        /* 如果沒有該手機，那就代表尚未註冊，就將資料存進資料庫內。需發送簡訊驗證碼 */
    }
    mysqli_free_result($result);
}

if (count($err_msg) > 0) {
    echo "<script>alert('" . implode("\n", $err_msg) . "')</script>";
} else {

    $query = "INSERT INTO smscode(smscode_id, code, mobile, pub_date, is_ok, orders) VALUES (uuid(),'" . $code . "', '" . $mobile . "', now(), 1, 1)";
    $mysqli->query($query);
    echo "<script>alert('新增成功')</script>";
}
echo "<script>history.go(-1)</script>";
exit;
