<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$redirect = aes_decrypt(params_security($_GET["redirect"]));
@$isLogin = false; // true -> 登入、 false -> 註冊狀態

$profile_json = array();
$result_arr = array();

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
//    print_r($profile_json);

    $query = "SELECT * FROM member WHERE user_id = '" . $profile_json['userId'] . "'";
    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_arr[] = $row;
        }
        if ($total == 1) {
            $isLogin = true;
        }
        mysqli_free_result($result);
    }

} else {
    echo "<script>document.location.href = 'https://liff.line.me/1657192181-OgEgXVG0';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="description" content="會員通知">
    <meta name="keywords" content="會員通知,移車QR CODE,移動條碼"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate"/>
    <meta name="viewport"
          content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>手機驗證-會員通知</title>
    <meta http-equiv="Content-Security-Policy"
          content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
    <meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
</head>

<body>
<div id="main" class="main">
    <div class="page">
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-6">
                    <h4 class="title font-weight-6">手機驗證登入/註冊</h4>
                    <?php // echo ($isLogin) ? "登入" : "註冊"; ?>
                    <!-- 如果這個line帳號使用者系統找不到的時候，就讓他驗證手機，找看看是否有相同手機號碼的會員存在，如果找到了就登入，沒有找到就直接幫他註冊 -->
                    <!-- 這樣如果之後會員更換LINE帳號時可以去找手機號碼幫他找回資料&重新綁好LINE帳號 -->
                    <form class="checkthisform" method="post" enctype="multipart/form-data"
                          action="login_end.php">
                        <?php
                        $temp = gettoken_value();
                        echo '<input type="hidden" name="value" value="' . $temp["value"] . '">';
                        echo '<input type="hidden" name="token" value="' . $temp["token"] . '">';
                        ?>
                        <div class="form-item warning-box">
                            <label for="">手機號碼：</label>
                            <div class="input-group">
                                <input id="mobile" name="tel" type="tel"
                                       class="form-control form-control-lg"
                                       placeholder="手機號碼" req="Y"
                                       value="<?php echo isset($result_arr[0]['account']) ? $result_arr[0]['account'] : "" ?>">
                                <!--<button class="progress-btn btn btn-primary border-radius-0 btn-block"
                                        name="notify"
                                        type="button">認證<span></span></button>-->
                            </div>
                            <span class="alert-text">*請填寫正確格式的手機號碼</span>
                            <span class="alert-text-backend"></span>
                        </div>
                        <div class="form-item warning-box" id="mobileValidation">
                            <label for="">驗證碼：</label>
                            <div class="input-group">
                                <input name="code" id="sms-input" type="text" class="form-control form-control-lg"
                                       value="88888" readonly>
                            </div>
                            <span class="alert-text">*此為必填欄位</span>
                        </div>
                        <div class="row justify-content-center pt-4 pb-5">
                            <div class="col-8">
                                <button class="submit-btn btn btn-primary btn-block btn-shadow hover-down"
                                        type="button">送出
                                </button>
                                <input type="hidden" name="isLogin"
                                       value="<?php echo aes_encrypt($isLogin); ?>">
                                <input type="hidden" name="userId"
                                       value="<?php echo aes_encrypt($profile_json['userId']); ?>">
                                <input type="hidden" name="displayName"
                                       value="<?php echo aes_encrypt($profile_json['displayName']); ?>">
                                <input type="hidden" name="redirect"
                                       value="<?php echo aes_encrypt($redirect); ?>">
                                <input type="hidden" name="profile"
                                       value="<?php echo $profile; ?>">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>