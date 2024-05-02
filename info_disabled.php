<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$member_id = params_security($_SESSION['member']['member_id']);
@$qrcode_id = aes_decrypt(params_security($_GET['qrcode_id']));

$result_arr = array();
$video_arr = array(); // 影片廣告
$photo_arr = array(); // 單一圖片廣告
$text_arr = array(); // 圖文連結廣告

$query = "SELECT *, 1 AS 'qrcode' FROM `qrcode` WHERE `qrcode_id` = '" . $qrcode_id . "' AND orders >= 0; ";
if ($result = $mysqli->query($query)) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $result_arr[] = $row;
    }
    mysqli_free_result($result);
}

if (count($result_arr) == 0) {
    echo "<script>alert('此為無效QRCode')</script>";
    echo "<script>history.go(-1)</script>";
    exit;
}

// 還要再判斷 「廣告標籤」....
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
    <title><?php echo isset($result_arr[0]['introd']) ? $result_arr[0]['introd'] : ""; ?>聯絡資訊-會員通知</title>
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
        echo '<p class="vjs-no-js">';
        echo 'To view this video please enable JavaScript, and consider upgrading to a
                web browser that';
        echo '<a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>';
        echo '</p>';
        echo '</video>';
        // <video id="video" controls autoplay="autoplay" loop="loop" muted="" defaultmuted="" playsinline="" preload="auto">
        //            <source src="assets/video/test.mp4" type="video/mp4">
        //        </video>
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

                        <!-- <div class="phone-item">
                            <a href="tel:886-988888888" class="icon">
                                <i class="fas fa-phone-alt"></i>
                                <span>點擊撥打</span>
                            </a>
                        </div> -->
                        <div class="btn-group w-100">
                            <a href="tel:<?php echo isset($result_arr[0]['title']) ? $result_arr[0]['title'] : ""; ?>"
                               class="btn btn-secondary w-50"><i class="fas fa-phone-alt"></i>撥打電話</a>
                            <!-- 還沒line註冊的人會alert帶他去註冊linebot -->
                            <button id="un-btn" class="btn btn-primary btn-line w-50" type="button">
                                <img src="assets/img/icon/line_s.svg" alt="">
                                <span>留言 </span><!--給家屬-->
                            </button>
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
    <div class="modal fade" id="un-line" data-keyboard="false" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <h5 class="text-center my-5">登入LINE留言 </h5><!--給家屬-->
                    <div class="text-center mb-5">
                        <a class="btn btn-line w-50"
                           href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("login.php"); ?>">
                            <img src="assets/img/icon/line_s.svg" alt="">
                            <span>前往登入</span>
                        </a>
                    </div>
                </div>
            </div>
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