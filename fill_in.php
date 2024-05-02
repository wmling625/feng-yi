<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$qrcode_id = aes_decrypt(params_security($_GET["qrcode_id"]));
@$is_send = aes_decrypt(params_security($_GET["is_send"]));

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
} else {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

$member_arr = array();
$result_arr = array();
$video_arr = array(); // 影片廣告

$query = "";
$query .= "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT *, 1 AS 'qrcode' FROM qrcode WHERE `qrcode_id` = '" . $qrcode_id . "'; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member'])) {
                    $member_arr[] = $row;
                }
                if (isset($row['qrcode'])) {
                    $result_arr[] = $row;
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

if (count($result_arr) == 0) {
    echo "<script>alert('查無QRCode')</script>";
    echo "<script>document.location.href='code_list.php?profile=" . $profile . "'</script>";
    exit;
} else {
    if ($result_arr[0]['orders'] == "1") {
        echo "<script>alert('此QRCode已綁定')</script>";
        echo "<script>document.location.href='my_info.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&profile=" . $profile . "'</script>";
        exit;
    } elseif ($result_arr[0]['orders'] == "-2") {
        echo "<script>alert('此QRCode已被停用，請聯繫客服')</script>";
        echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
        exit;
    }
}

if (count($member_arr) > 0) {
    if ($member_arr[0]['orders'] !== "1") {
        echo "<script>alert('您已被停權，請聯繫客服')</script>";
        echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
        exit;
    }
} else {
    echo "<script>alert('查無會員資訊')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("login.php") . "';</script>";
    exit;
}

/*
 * 廣告投放條件
 * (1) 若有設定起訖日，則需要在區間內 A.`start_date` / A.`end_date`
 * (2) 廣告類型要符合，如單一圖片、影片、圖文連結 A.`types_option`
 * (3) 匹配該QRCode對應的標籤 A.`types_box`
 * (4) 該廣告為「啟用」狀態 A.`orders`
 * (5) 該標籤為「啟用」狀態 B.`orders
 */
$query = "";
$query .= "SELECT A.*, 1 AS 'video' FROM `advertisement` AS A 
                JOIN `qr_type` AS B ON A.`types_box` LIKE CONCAT('%', B.qr_type_id, '%') 
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL) 
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL) 
              AND A.`types_option` = '影片'
              AND A.`types_box` LIKE '%" . $result_arr[0]['qr_type_id'] . "%'
              AND B.`qr_type_id` = '" . $result_arr[0]['qr_type_id'] . "'
              AND A.`orders` >= 0 
              AND B.`orders` >= 0 
            ORDER BY RAND() LIMIT 0,1; ";

$query1 = "";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['video'])) {
                    $video_arr[] = $row;
                }
                $query1 .= "UPDATE `advertisement` SET `display` = `display` + 1, `last_date` = NOW() WHERE `advertisement_id` = '" . $row["advertisement_id"] . "'; ";
                $query1 .= "INSERT INTO `advertisement_day` (`advertisement_day_id`, `advertisement_id`, `display`, `count`, `pub_date`) VALUES (UUID(), '" . $row["advertisement_id"] . "', 1, 0, DATE(NOW())) ON DUPLICATE KEY UPDATE `display` = `display` +1; ";
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

if ($mysqli->multi_query($query1)) {
    while ($mysqli->more_results() && $mysqli->next_result()) ;
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
    <title>綁定QR CODE-會員通知</title>
    <meta http-equiv="Content-Security-Policy"
          content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
    <meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
</head>

<body>
<div id="main" class="main">
    <?php
    if (count($video_arr) > 0) {
        echo '<div class="video-section">';
        echo '<video id="videobox" class="video-js vjs-big-play-centered" controls playsinline="true" autoplay muted loop preload="auto">';
        echo '<source src="uploads/others/' . $video_arr[0]['file1'] . '" type="video/mp4"/>';
        echo '</video>';
        echo '</div>';
    }
    ?>
    <div class="page">
        <div class="title-box">
            <div class="container container-shrink">
                <div class="row justify-content-center no-gutters">
                    <div class="col-lg-6">
                        <h4 class="title font-weight-6">綁定QR
                            Code #<?php echo isset($result_arr[0]['code']) ? $result_arr[0]['code'] : ""; ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-6">
                    <form class="checkthisform" method="post" enctype="multipart/form-data"
                          action="fill_in_end.php">
                        <?php
                        $temp = gettoken_value();
                        echo '<input type="hidden" name="value" value="' . $temp["value"] . '">';
                        echo '<input type="hidden" name="token" value="' . $temp["token"] . '">';
                        ?>
                        <div class="st-1">
                            <div class="form-item warning-box">
                                <label for="">手機號碼：</label>
                                <div class="input-group">
                                    <input id="mobile" type="tel" name="tel" class="form-control form-control-lg"
                                           placeholder="手機號碼" req="Y">
                                    <!--<button class="progress-btn btn btn-primary border-radius-0 btn-block disabled"
                                            type="button">認證<span></span></button>-->
                                </div>
                                <span class="alert-text">*請填寫正確格式的手機號碼</span>
                                <!--<span class="okay-text"><i class="las la-check"></i>手機綁定成功！</span>-->
                                <span class="okay-text"><i class="las la-check"></i>手機驗證成功！</span>
                                <span class="alert-text-backend"></span>
                            </div>
                            <div class="form-item warning-box sms-form-item">
                                <label for="">驗證碼：</label>
                                <div class="input-group">
                                    <input id="sms-input" type="text" class="form-control form-control-lg"
                                           value="88888" readonly>
                                    <div class="input-group-append">
                                        <button id="sms-btn" class="btn btn-primary border-radius-0" type="button">確認
                                        </button>
                                    </div>
                                </div>
                                <span class="alert-text">*此為必填欄位</span>
                            </div>
                        </div>

                        <div class="st-2 hidden">
                            <div class="form-item warning-box">
                                <label for="">暱稱：</label>
                                <input type="text" class="form-control form-control-lg" placeholder="暱稱" req="Y"
                                       name="introd">
                                <span class="alert-text">*此為必填欄位</span>
                            </div>
                            <div class="form-item warning-box">
                                <label for="">姓名：</label> <!--家屬-->
                                <input type="text" class="form-control form-control-lg text-uppercase"
                                       placeholder="姓名" req="Y"
                                       name="contents">
                                <span class="alert-text">*此為必填欄位</span>
                            </div>

                            <div class="row justify-content-center pt-4 pb-5">
                                <div class="col-8">
                                    <button class="submit-btn btn btn-primary btn-block btn-shadow hover-down"
                                            type="submit">送出
                                    </button>
                                    <input type="hidden" name="qrcode_id"
                                           value="<?php echo aes_encrypt($qrcode_id); ?>">
                                    <input type="hidden" name="member_id"
                                           value="<?php echo aes_encrypt($member_arr[0]['member_id']); ?>">
                                    <input type="hidden" name="is_send"
                                           value="<?php echo aes_encrypt($is_send); ?>">
                                    <input type="hidden" name="profile"
                                           value="<?php echo $profile; ?>">
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="sms-success" data-keyboard="false" tabindex="-1"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <!--<h4 class="text-center my-5"><i class="las la-check"></i>手機綁定成功！</h4>-->
                    <h4 class="text-center my-5"><i class="las la-check"></i>手機驗證成功！</h4>
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