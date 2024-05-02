<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);

$result_arr = array();

if (empty($profile)) {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

$profile_json = json_decode(aes_decrypt($profile), true);
$qr_type_big_id = "";
$title = "";
$query = "SELECT A.qr_type_big_id, B.title FROM member A INNER JOIN qr_type_big B ON A.qr_type_big_id = B.qr_type_big_id WHERE A.user_id = '" . $profile_json['userId'] . "'; ";
if ($result = $mysqli->query($query)) {
    $row = $result->fetch_array(MYSQLI_ASSOC);
    $qr_type_big_id = $row["qr_type_big_id"];
    $title = $row["title"];
    mysqli_free_result($result);
}

if (empty($qr_type_big_id)) {
    echo "<script>alert('您無管理權限')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

$query = "SELECT A.qrcode_big_id, B.* FROM qrcode_big A INNER JOIN member B ON A.member_id= B.member_id WHERE A.qr_type_big_id = '" . $qr_type_big_id . "' ORDER BY A.pub_date DESC; ";
if ($result = $mysqli->query($query)) {
    $total = mysqli_num_rows($result);
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
    <title><?php echo $title; ?> 會員清單-會員通知</title>
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
                        <h4 class="title font-weight-6"><?php echo $title; ?> 會員清單
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
                                $link = "big_info.php?qrcode_big_id=" . aes_encrypt($value['qrcode_big_id']);

                                echo '<div class="item">';
                                echo '<a href="' . $link . '" class="textbox">';
                                echo '<p class="mb-0 title-1"><i class="fas fa-qrcode"></i>' . $value['nickname'] . '</p>';
                                echo '</a>';
                                echo '<div class="btn-box">';
                                echo '<a class="btn btn-primary" href="' . $link . '">查看</a>';
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
<!--            <a class="btn btn-outline-success"-->
<!--               href="--><?php //echo $oa; ?><!--"><i class="lab la-line"></i> 返回官方帳號</a>-->
<!--        </div>-->
    </div>
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>