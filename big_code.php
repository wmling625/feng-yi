<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$qr_type_big_id = params_security(aes_decrypt($_GET["qr_type_big_id"]));

$result_arr = array();
$video_arr = array(); // 影片廣告
$photo_arr = array(); // 單一圖片廣告
$text_arr = array(); // 圖文連結廣告

$query = "SELECT * FROM qr_type_big WHERE qr_type_big_id = '" . $qr_type_big_id . "';";
if ($result = $mysqli->query($query)) {
    $total = mysqli_num_rows($result);
    if ($total > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_arr[] = $row;
        }
    } else {
        echo "<script>alert('查無單位條碼');window.close();</script>";
    }
    mysqli_free_result($result);
}

/*
 * 廣告投放條件:
 * (1) 若有設定起訖日，則需要在區間內 A.`start_date` / A.`end_date`
 * (2) 廣告類型要符合，如單一圖片、影片、圖文連結 A.`types_option`
 * (3) 匹配該QRCode對應的單位id A.`types_box2`
 * (4) 該廣告為「啟用」狀態 A.`orders`
 * (5) 該標籤為「啟用」狀態 B.`orders
 */
$query = "";
$query .= "SELECT A.*, 1 AS 'video' FROM `advertisement` AS A
                JOIN `qr_type_big` AS B ON A.`types_box2` LIKE CONCAT('%', B.qr_type_big_id, '%')
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL)
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL)
              AND A.`types_option` = '影片'
              AND A.`types_box2` LIKE '%" . $result_arr[0]['qr_type_big_id'] . "%'
              AND B.`qr_type_big_id` = '" . $result_arr[0]['qr_type_big_id'] . "'
              AND A.`orders` >= 0
              AND B.`orders` >= 0
            ORDER BY RAND() LIMIT 0,1; ";
$query .= "SELECT A.*, 1 AS 'photo' FROM `advertisement` AS A
                JOIN `qr_type_big` AS B ON A.`types_box2` LIKE CONCAT('%', B.qr_type_big_id, '%')
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL)
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL)
              AND A.`types_option` = '單一圖片'
              AND A.`types_box2` LIKE '%" . $result_arr[0]['qr_type_big_id'] . "%'
              AND B.`qr_type_big_id` = '" . $result_arr[0]['qr_type_big_id'] . "'
              AND A.`orders` >= 0
              AND B.`orders` >= 0
            ORDER BY RAND() LIMIT 0,1; ";
$query .= "SELECT A.*, 1 AS 'text' FROM `advertisement` AS A
                JOIN `qr_type_big` AS B ON A.`types_box2` LIKE CONCAT('%', B.qr_type_big_id, '%')
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL)
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL)
              AND A.`types_option` = '圖文連結'
              AND A.`types_box2` LIKE '%" . $result_arr[0]['qr_type_big_id'] . "%'
              AND B.`qr_type_big_id` = '" . $result_arr[0]['qr_type_big_id'] . "'
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
    <title><?php echo isset($result_arr[0]['title']) ? $result_arr[0]['title'] : ""; ?>-會員通知</title>
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
                        <h4 class="title font-weight-6">
                            <?php echo isset($result_arr[0]['title']) ? $result_arr[0]['title'] : ""; ?>
                            <!--<button type="button" name="clearSession" class="btn btn-outline-dark float-right">登出</button>-->
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="container container-shrink">
            <div class="row justify-content-center no-gutters">
                <div class="col-lg-7">
<!--                    <div class="row justify-content-center mb-5">-->
<!--                        <div class="col-8">-->
                            <?php
                            $photo_small1 = "";
                            if (isset($result_arr[0]["file0"])) {
                                $photo_small1 = ($result_arr[0]['file0'] == "") ? "" : $result_arr[0]['file0'];
                            }
                            $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("big_redirect.php?qr_type_big_id=" . aes_encrypt($result_arr[0]['qr_type_big_id']));
                            $qrcode = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($link);

                            echo '<img src="' . $qrcode . '" alt="">';
                            ?>
<!--                        </div>-->
<!--                    </div>-->
                    <div class="info-section mt-5">
                        <div class="btn-group w-100">
                            <a href="<?php echo $link; ?>" class="btn btn-line btn-primary w-50">加入群組</a>
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
</div>
<!-- End of Main Container -->

<!-- Scripts -->
<?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>