<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$qrcode_id = aes_decrypt(params_security($_GET['qrcode_id']));

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
} else {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

$member_arr = array();
$result_arr = array();
$video_arr = array(); // 影片廣告
$photo_arr = array(); // 單一圖片廣告
$text_arr = array(); // 圖文連結廣告

$query = "";
$query .= "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT *, 1 AS 'qrcode' FROM `qrcode` WHERE `qrcode_id` = '" . $qrcode_id . "' AND `member_id` IN (SELECT `member_id` FROM member WHERE user_id = '" . $profile_json['userId'] . "'); "; // AND orders >= 0
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
    if ($result_arr[0]['orders'] == "-1") {
        echo "<script>alert('尚未綁定資訊，請先綁定')</script>"; // 家屬
        echo "<script>document.location.href='fill_in.php?qrcode_id=" . aes_encrypt($qrcode_id) . "&profile=" . $profile . "'</script>";
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
 * 廣告投放條件:
 * (1) 若有設定起訖日，則需要在區間內 A.`start_date` / A.`end_date`
 * (2) 廣告類型要符合，如單一圖片、影片、圖文連結 A.`types_option`
 * (3) 匹配該QRCode對應的標籤id A.`types_box`
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
$query .= "SELECT A.*, 1 AS 'photo' FROM `advertisement` AS A 
                JOIN `qr_type` AS B ON A.`types_box` LIKE CONCAT('%', B.qr_type_id, '%') 
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL) 
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL) 
              AND A.`types_option` = '單一圖片'
              AND A.`types_box` LIKE '%" . $result_arr[0]['qr_type_id'] . "%'
              AND B.`qr_type_id` = '" . $result_arr[0]['qr_type_id'] . "' 
              AND A.`orders` >= 0 
              AND B.`orders` >= 0
            ORDER BY RAND() LIMIT 0,1; ";
$query .= "SELECT A.*, 1 AS 'text' FROM `advertisement` AS A 
                JOIN `qr_type` AS B ON A.`types_box` LIKE CONCAT('%', B.qr_type_id, '%') 
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL) 
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL) 
              AND A.`types_option` = '圖文連結'
              AND A.`types_box` LIKE '%" . $result_arr[0]['qr_type_id'] . "%'
              AND B.`qr_type_id` = '" . $result_arr[0]['qr_type_id'] . "' 
              AND A.`orders` >= 0 
              AND B.`orders` >= 0
            ORDER BY RAND() LIMIT 0,10; ";

$query1 = "";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['video'])) {
                    $video_arr[] = $row;
                }
                if (isset($row['photo'])) {
                    $photo_arr[] = $row;
                }
                if (isset($row['text'])) {
                    $text_arr[] = $row;
                }
                $query1 .= "UPDATE `advertisement` SET `display` = `display` + 1, last_date = NOW() WHERE `advertisement_id` = '" . $row["advertisement_id"] . "'; ";
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
    <title>我的個人資訊-會員通知</title>
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
                    <div class="col-lg-7">
                        <h4 class="title font-weight-6">聯絡資訊</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-7">
                    <div class="info-section">
                        <div class="item">
                            <div class="label">QR Code</div>
                            <div class="content"><?php echo isset($result_arr[0]['code']) ? $result_arr[0]['code'] : ""; ?></div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">暱稱</div>
                                    <div class="content"><?php echo isset($result_arr[0]['introd']) ? $result_arr[0]['introd'] : ""; ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">姓名</div><!--家屬-->
                                    <div class="content"><?php echo isset($result_arr[0]['contents']) ? $result_arr[0]['contents'] : ""; ?></div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="text-center">
                            <a class="btn btn-primary btn-block" href="edit.html"><i class="las la-edit"></i>編輯我的個人資訊</a>
                        </div> -->
                        <div class="btn-group w-100">
                            <a href="<?php echo $oa; ?>" class="btn btn-line btn-primary w-50"><i
                                        class="lab la-line"></i> 返回官方帳號</a>
                        </div>
                    </div>
                    <div class="row justify-content-center mb-5">
                        <div class="col-8">
                            <?php
                            $photo_small1 = "";
                            if (isset($result_arr[0]["file0"])) {
                                $photo_small1 = ($result_arr[0]['file0'] == "") ? "" : $result_arr[0]['file0'];
                            }
                            $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_redirect.php?qrcode_id=" . aes_encrypt($result_arr[0]['qrcode_id']));
                            if (is_file("uploads/qrcode/" . $photo_small1)) {
                                $qrcode = "uploads/qrcode/" . $photo_small1;
                            } else {
                                $qrcode = "https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=" . urlencode($link) . "&choe=UTF-8";
                            }

                            echo '<img src="' . $qrcode . '" alt="">';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 廣告 -->
            <?php
            if (count($text_arr) > 0) {
                echo '<div class="row justify-content-center no-gutters">';
                echo '<div class="col-lg-7">';
                echo '<div class="adv-section pos-r">';
                echo '<p class="mb-0 text-center tag">廣告</p>';
                echo '<div class="swiper ad-swiper">';
                echo '<div class="swiper-wrapper">';
                foreach ($text_arr as $value) {
                    $photo_small1 = ($value['file0'] == "") ? "nophoto.png" : $value['file0'];

                    echo '<div class="swiper-slide">';
                    echo '<a class="item" href="ad_redirect.php?advertisement_id=' . $value['advertisement_id'] . '&link=' . urlencode($value['introd']) . '" target="_blank">';
                    echo '<div class="imgbox__inner">';
                    echo '<div class="image"
                                                 style="background-image: url(uploads/others/' . $photo_small1 . ');">';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="textbox">';
                    echo '<p class="mb-0 title-2">';
                    echo $value['contents'];
                    echo '</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
                echo '<div class="swiper-pagination ad-pagination"></div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
    if (count($photo_arr) > 0) {
        $photo_small1 = ($photo_arr[0]['file0'] == "") ? "nophoto.png" : $photo_arr[0]['file0'];
        echo '<div class="modal fade" id="pic-modal" data-keyboard="false" tabindex="-1" aria-labelledby="" aria-hidden="true">';
        echo '<div class="modal-dialog modal-dialog-centered">';
        echo '<div class="modal-content">';
        echo '<div class="modal-body p-0">';
        echo ' <div class="d-flex align-items-end mb-2">';
        echo '<div class="tag">廣告</div>';
        echo '</div>';
        echo '<div class="text-center mb-4">';
        echo '<div class="imgbox">';
        echo '<div class="imgbox__inner">';
        echo '<div class="image" style="background-image: url(uploads/others/' . $photo_small1 . ');"></div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<div class="text-center">';
        echo ' <button class="btn btn-lg btn-light btn-block" data-dismiss="modal" type="button">關閉</button>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    ?>
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>