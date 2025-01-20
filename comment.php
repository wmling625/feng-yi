<?php
include_once(dirname(__FILE__) . "/./phplibs/front_head.php");

@$profile = params_security($_GET["profile"]);
@$history_id = params_security($_GET['history_id']);
@$model = "add";

if (!empty($profile)) {
    $profile_json = json_decode(aes_decrypt($profile), true);
} else {
    echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>";
    exit;
}

$canReply = true;
$member_arr = array();
$result_arr = array();
$history_arr = array();
$text_arr = array(); // 圖文連結廣告

$query = "";
$query .= "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
$query .= "SELECT A.*, B.`contents` AS license, B.qr_type_id, 1 AS 'history' FROM `history` AS A INNER JOIN `qrcode` AS B ON A.`qrcode_id`=B.`qrcode_id` WHERE A.`history_id` = '" . $history_id . "'; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member'])) {
                    $member_arr[] = $row;
                }
                if (isset($row['history'])) {
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
    $query = "";
    $query .= "SELECT *, 1 AS 'member' FROM member WHERE user_id = '" . $profile_json['userId'] . "' ; ";
    $query .= "SELECT A.*, B.`contents` AS license, B.qr_type_big_id, 1 AS 'history' FROM `history` AS A INNER JOIN `qrcode_big` AS B ON A.`qrcode_big_id` = B.`qrcode_big_id` WHERE A.`history_id` = '" . $history_id . "'; ";
    if ($mysqli->multi_query($query)) {
        do {
            if ($result = $mysqli->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['member'])) {
                        $member_arr[] = $row;
                    }
                    if (isset($row['history'])) {
                        $result_arr[] = $row;
                    }
                }
                $result->close();
            }
            if ($mysqli->more_results()) {
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }

    $select_query = "SELECT * FROM `history_list` WHERE `history_id` = '$history_id'";
    $history_result = $mysqli->query($select_query);

    if ($history_result = $mysqli->query($select_query)) {
        $rows = $history_result->fetch_array();
        $history_arr[] = $rows;
        mysqli_free_result($result);
    }
}


if (count($result_arr) == 0) {
    echo "<script>alert('查無留言紀錄')</script>";
    echo "<script>document.location.href='code_list.php?profile=" . $profile . "'</script>";
    exit;
} else {
    /*
     * 如果接收留言的userID 和 當前進入頁面的會員userId相同的話，才可以回覆
     * 如果留言者 和 當前進入頁面的會員userId相同的話，不可以回覆=自己留言給自己
     */
    if ($result_arr[0]['user_id1'] !== $profile_json['userId'] || $result_arr[0]['user_id0'] == $profile_json['userId']) {
        echo "<script>alert('您不可回覆該則留言，請確認!')</script>";
        echo "<script>document.location.href = 'https://liff.line.me/" . $liff_close . "';</script>"; // 關閉
        exit;
    }
}

/*
 * 廣告投放條件:
 * (1) 若有設定起訖日，則需要在區間內 A.`start_date` / A.`end_date`
 * (2) 廣告類型要符合，如單一圖片、影片、圖文連結 A.`types_option`
 * (3) 匹配該QRCode對應的標籤 A.`types_box`
 * (4) 該廣告為「啟用」狀態 A.`orders`
 * (5) 該標籤為「啟用」狀態 B.`orders
 */
$query = "";
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
    while ($mysqli->more_results() && $mysqli->next_result());
}

?>
<!DOCTYPE html>
<html class="no-js">

<head>
    <meta charset="utf-8">
    <meta name="description" content="會員通知">
    <meta name="keywords" content="會員通知,移車QR CODE,移動條碼" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate" />
    <meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>留言-會員通知</title>
    <meta http-equiv="Content-Security-Policy" content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
    <meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
</head>

<body>
    <div id="main" class="main">
        <div class="page">
            <div class="title-box">
                <div class="container container-shrink">
                    <div class="row justify-content-center no-gutters">
                        <div class="col-lg-7">
                            <h4 class="title font-weight-6">留言</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container container-shrink">
                <div class="row justify-content-center no-gutters">
                    <div class="col-lg-7">
                        <div class="reply-section">
                            <form action="comment_end.php" method="post" enctype="multipart/form-data">
                                <?php
                                $temp = gettoken_value();
                                echo '<input type="hidden" name="value" value="' . $temp["value"] . '">';
                                echo '<input type="hidden" name="token" value="' . $temp["token"] . '">';
                                ?>
                                <div class="reply-item">
                                    <div class="textbox"><?php echo isset($result_arr[0]['contents0']) ? $result_arr[0]['contents0'] : ""; ?></div>
                                    <div class="date"><?php echo isset($result_arr[0]['pub_date']) ? $result_arr[0]['pub_date'] : ""; ?></div>

                                    <?php

                                    $readonly = "";
                                    $disabled = "";
                                    $hidden = "";
                                    $btnText = "編輯留言<i class='fas fa-pen'></i>";
                                    // if (count($result_arr) > 0) {
                                    //     if (isset($result_arr[0]['orders'])) {
                                    //         if ($result_arr[0]['orders'] == '1') {
                                    //             $hidden = "hidden";
                                    //             $model = "edit";
                                    //             $now = date('Y-m-d H:i:s');
                                    //             if (isset($result_arr[0]['last_date'])) {
                                    //                 $diff = DateDiff($now, $result_arr[0]['last_date'], "s");
                                    //                 if ($diff <= 300) {
                                    //                     /* 未間隔5分鐘，需要重複留言 */
                                    //                     $canReply = false;
                                    //                     $readonly = "readonly";
                                    //                     $disabled = "disabled";
                                    //                     $btnText = "回覆請間隔5分鐘";
                                    //                 }
                                    //             }
                                    //         }
                                    //     }
                                    // }
                                    ?>

                                    <div class="reply-box <?php echo $hidden; ?>">
                                        <textarea class="form-control" name="contents1" id="contents1" placeholder="請輸入回覆內容" cols="30" rows="3"><?php echo isset($result_arr[0]['contents1']) ? br2nl($result_arr[0]['contents1']) : ""; ?></textarea>
                                        <div class="text-right">
                                            <button class="btn btn-sm btn-primary mt-3 reply-btn disabled" type="submit">
                                                回覆<i class="fas fa-reply"></i></button>
                                        </div>
                                    </div>

                                    <?php
                                    if (isset($result_arr[0]['orders']) && $result_arr[0]['orders'] == "1") {
                                        echo '<div class="my-reply">';
                                        echo '<span class="label">我的回覆：</span>';
                                        echo '<p class="sent-content mb-0">';
                                        echo isset($result_arr[0]['contents1']) ? br2nl($result_arr[0]['contents1']) : "";
                                        echo '</p>';
                                        echo '<div class="date">';
                                        echo isset($result_arr[0]['last_date']) ? $result_arr[0]['last_date'] : "";
                                        echo '</div>';
                                        if ($history_result) {
                                            foreach ($history_result as $key => $history) {
                                                echo '<p class="sent-content mb-0">';
                                                echo isset($history['content']) ? br2nl($history['content']) : "";
                                                echo '</p>';
                                                echo '<div class="date">';
                                                echo isset($history['last_date']) ? $history['last_date'] : "";
                                                echo '</div>';
                                            }
                                        }
                                        echo '<div class="text-right">';
                                        // echo '<button class="btn btn-sm btn-short btn-primary editopen-btn"
                                        //         type="button">' . $btnText . '</button>';
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                    <?php /*
                                    <div class="my-reply">
                                        <span class="label">我的回覆：</span>
                                        <p class="sent-content mb-0">
                                            <?php echo isset($result_arr[0]['contents1']) ? br2nl($result_arr[0]['contents1']) : ""; ?>
                                        </p>
                                        <div class="date"><?php echo isset($result_arr[0]['last_date']) ? $result_arr[0]['last_date'] : ""; ?></div>
                                        <div class="text-right">
                                            <button class="btn btn-sm btn-short btn-primary editopen-btn"
                                                type="button" <?php echo $disabled; ?>><?php echo $btnText; ?>
                                            </button>
                                        </div>
                                    </div>
                                    */ ?>

                                </div>
                                <input type="hidden" name="history_id" value="<?php echo aes_encrypt($history_id); ?>">
                                <input type="hidden" name="model" value="<?php echo aes_encrypt($model); ?>">
                                <input type="hidden" name="qr_type_id" value="<?php echo aes_encrypt($result_arr[0]['qr_type_id']); ?>">
                                <input type="hidden" name="user_id0" value="<?php echo aes_encrypt($result_arr[0]['user_id0']); ?>">
                                <input type="hidden" name="user_id1" value="<?php echo aes_encrypt($result_arr[0]['user_id1']); ?>">
                                <input type="hidden" name="license" value="<?php echo aes_encrypt($result_arr[0]['license']); ?>">
                            </form>
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
    </div>
    <!-- End of Main Container -->

    <!-- Scripts -->
    <?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>

</body>

</html>