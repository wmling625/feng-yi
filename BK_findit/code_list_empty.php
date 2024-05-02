<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$member_id = params_security($_SESSION['member']['member_id']);

$result_arr = array();

$query = "SELECT * FROM `qrcode` WHERE `member_id` = '" . $member_id . "' AND `orders` = -1";

if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $result_arr[] = $row;
    }
    mysqli_free_result($result);
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
    <title>我的條碼-會員通知</title>
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
                    <div class="col-lg-6">
                        <h4 class="title font-weight-6">我的條碼</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-6">
                    <div class="code-list">
                        <?php
                        if (count($result_arr) > 0) {
                            foreach ($result_arr as $key => $value) {
                                echo '<div class="item">';
                                echo '<a href="fill_in.php?qrcode_id=' . aes_encrypt($value['qrcode_id']) . '" class="textbox">';
                                echo '<p class="mb-0 title-1"><i class="fas fa-qrcode"></i>' . $value['code'] . '</p>';
                                echo '</a>';
                                echo '<div class="btn-box">';
                                echo '<a class="btn btn-primary w-100" href="fill_in.php?qrcode_id=' . aes_encrypt($value['qrcode_id']) . '">綁定</a>';
//                                echo '<button class="del-btn btn bg-bgcolor btn-outline-danger" type="button">註銷</button>';
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
    </div>
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>