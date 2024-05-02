<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$qrcode_big_id = aes_decrypt(params_security($_GET['qrcode_big_id']));
@$model = "add";
/*
 * 此頁需判斷:
 * (1) $isLogged 是否已註冊/登入 = 攸關能不能使用 LINE留言 給對方
 * 因為進來此頁不須透過 full.php，所以民眾不一定有註冊/登入該服務 = 不一定有 $profile_json['user_id']
 * (2) $canNote 是否可以留言 (2022-08-01 改為民眾可重複留言，但限制5分鐘留言一次)
 */
$isLogged = false;
$user_id0 = "";
$display_name = "";
if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
    $isLogged = true;
    $user_id0 = $profile_json['userId'];
    $display_name = $profile_json['displayName'];
    $_SESSION['passerby'] = array();
    $_SESSION['passerby']['userId'] = $profile_json['userId'];
    $_SESSION['passerby']['displayName'] = $profile_json['displayName'];

} elseif (isset($_SESSION['passerby']['userId']) && $_SESSION['passerby']['userId'] !== "") {
    $isLogged = true;
    $user_id0 = $_SESSION['passerby']['userId'];
    $display_name = $_SESSION['passerby']['displayName'];
}

$canNote = true;
$result_arr = array();
$message_arr = array();
$default_content = array(); // 預設留言內容
$video_arr = array(); // 影片廣告
$photo_arr = array(); // 單一圖片廣告
$text_arr = array(); // 圖文連結廣告

$query = "SELECT B.*, C.*, 1 AS 'qrcode_big' FROM `qrcode_big` A 
    INNER JOIN member B ON A.member_id = B.member_id 
    INNER JOIN qr_type_big C ON A.qr_type_big_id = C.qr_type_big_id 
WHERE A.`qrcode_big_id`='" . $qrcode_big_id . "'; ";
$query .= "SELECT *, 1 AS 'setting' FROM `setting` WHERE `id`='1'; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['qrcode_big'])) {
                    $result_arr[] = $row;
                }
                if (isset($row['history'])) {
                    $message_arr[] = $row;
                }
                if (isset($row['setting'])) {
                    $default_content = $row["content"];
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
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

if ($result_arr[0]['orders'] == "-1") {
    echo "<script>alert('此QRCode已被停用，請聯繫客服')</script>";
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
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
    <title><?php echo isset($result_arr[0]['nickname']) ? $result_arr[0]['nickname'] : ""; ?> 聯絡資訊-會員通知</title>
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
                            <div class="label">單位名稱</div>
                            <div class="content">
                                <?php
                                echo isset($result_arr[0]['title']) ? $result_arr[0]['title'] : "";

                                $link = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/big_code.php?qr_type_big_id=" . aes_encrypt($result_arr[0]['qr_type_big_id']);
                                echo '<a class="mx-1 badge badge-secondary" href="' . $link . '" target="_blank">前往單位條碼 <i class="las la-external-link-square-alt"></i></a>';
                                ?>
                            </div>
                        </div>
                        <div class="row no-gutters">
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">手機</div>
                                    <div class="content"><?php echo isset($result_arr[0]['account']) ? substr_cut($result_arr[0]['account'], 3) : ""; ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">姓名</div>
                                    <div class="content"><?php echo isset($result_arr[0]['nickname']) ? $result_arr[0]['nickname'] : ""; ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">性別</div>
                                    <div class="content"><?php echo isset($result_arr[0]['types_option']) ? $result_arr[0]['types_option'] : ""; ?></div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="item">
                                    <div class="label">活動區域</div>
                                    <div class="content"><?php echo isset($result_arr[0]['city']) ? $result_arr[0]['city'] . $result_arr[0]['region'] : ""; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="btn-group w-100">
                            <a href="tel:<?php echo isset($result_arr[0]['account']) ? $result_arr[0]['account'] : ""; ?>"
                               class="btn btn-secondary w-50"><i class="fas fa-phone-alt"></i>撥打電話</a>
                            <a href="<?php echo $oa; ?>" class="btn btn-line btn-primary w-50"><i class="lab la-line"></i>  返回官方帳號</a>
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
                    echo '<div class="image" style="background-image: url(uploads/others/' . $photo_small1 . ');">';
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

    <!-- 還沒line註冊的人會alert帶他去註冊linebot -->
    <div class="modal fade" id="un-line" data-keyboard="false" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <h5 class="text-center my-5">登入LINE留言 </h5><!--給家屬-->
                    <div class="text-center mb-5">
                        <a class="btn btn-line w-50"
                           href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("info.php?qrcode_id=" . aes_encrypt($qrcode_id)); ?>">
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