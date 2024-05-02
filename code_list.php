<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);

$result_arr = array();

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);

    $query = "SELECT * FROM `qrcode` WHERE `member_id` IN (SELECT `member_id` FROM member WHERE user_id = '" . $profile_json['userId'] . "') ORDER BY orders ASC";
    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_arr[] = $row;
        }
        mysqli_free_result($result);
    }

} else {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
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
    <title>個人通知條碼-會員通知</title>
    <meta http-equiv="Content-Security-Policy"
          content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
    <meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
</head>

<body>
<div id="main" class="main">
    <div class="page">
        <div class="title-box">
            <div class="container container-shrink">
                <div class="row justify-content-center no-gutters">
                    <div class="col-lg-12">
                        <h4 class="title font-weight-6">個人通知條碼
                            <!--<button type="button" name="clearSession" class="btn btn-outline-dark float-right">登出</button>-->
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-12">
                    <div class="code-list">
                        <?php
                        if (count($result_arr) > 0) {
                            foreach ($result_arr as $key => $value) {
                                $link = "";
                                $action = "";

                                if ($value['orders'] == "1") {
                                    // 已綁定
                                    $link = "my_info.php?qrcode_id=" . aes_encrypt($value['qrcode_id']) . "&profile=" . $profile;
                                    $action = '<a class="btn btn-primary" href="' . $link . '">查看</a>';
                                    $action .= '<button class="del-btn btn bg-bgcolor btn-outline-danger" type="button" data-info="' . $value['code'] . '" data-id="' . aes_encrypt($value['qrcode_id']) . '">註銷</button>';
                                } elseif ($value['orders'] == "-1") {
                                    // 未綁定
                                    $link = "fill_in.php?qrcode_id=" . aes_encrypt($value['qrcode_id']) . "&profile=" . $profile;
                                    $action = '<a class="btn btn-primary" href="' . $link . '">綁定</a>';
                                }

                                echo '<div class="item">';
                                echo '<a href="' . $link . '" class="textbox">';
                                echo '<p class="mb-0 title-1"><i class="fas fa-qrcode"></i>' . $value['code'] . '</p>';
                                echo '</a>';
                                echo '<div class="btn-box">';
                                echo $action;
                                echo '</div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<h4 class="py-5 text-gray">尚無條碼</h4>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
<!--        <div class="container-shrink d-flex justify-content-center py-4">-->
<!--            <div class="btn-group w-100">-->
<!--                <a href="--><?php //echo $oa; ?><!--" class="btn btn-primary w-50"><i class="lab la-line"></i>  返回官方帳號</a>-->
<!--            </div>-->
<!--        </div>-->
    </div>
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>