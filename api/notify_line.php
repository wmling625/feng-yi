<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");
/*
以下教學在 root_global的 line_notify
LINE MESSAGE除非是PHP錯誤, 不然沒辦法DEBUG
如果超連結參數裡面可能有中文, 記得urlencode, 但如果沒這壞習慣, 就不用 urlencode
*/

@$model = params_security($_POST['model']); // toOwner > 推播給家屬 、 toPeople > 推播給民眾
@$user_id = params_security($_POST['user_id']); // 要被推播的userId
@$qr_type_id = params_security($_POST['qr_type_id']); // 廣告標籤id
@$history_id = params_security($_POST['history_id']); // 留言id
@$lat = params_security($_POST['lat']); // 緯度 latitude
@$lng = params_security($_POST['lng']); // 經度 longitude

@$contents1 = urldecode(params_security($_POST['contents1'], 'none')); // 家屬 的留言內容
@$license = urldecode(params_security($_POST['license'])); // 家屬姓名

@$file0 = urldecode(params_security($_POST['file0'])); // 推播圖片
@$file1 = urldecode(params_security($_POST['file1'])); // 推播影片


file_put_contents(dirname(__FILE__) . "/./api/log/" . date("Ymdhis") . "line_notify.txt", json_encode($_POST, JSON_UNESCAPED_UNICODE));


@$id = '1';
$domain = '';
$query = "SELECT * FROM setting WHERE id = '" . $id . "';";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $result_arr[] = $rows;
    mysqli_free_result($result);
}

$domain = $result_arr[0]["domain"];
$channelAccessToken = $result_arr[0]["linetoken"];

// $users = array("LINE UUID","LINE UUID","LINE UUID","LINE UUID","LINE UUID");
// 可多人
$users = array();
$users = explode(",", $user_id);
//print_r($users);
$contents = "";
if ($model == "toOwner") {

    $user_query = "SELECT * FROM `member` WHERE `user_id` = '" . $user_id . "';";
    $user_arr = array();
    $receive_name = '';
    if ($result = $mysqli->query($user_query)) {
        $rows = $result->fetch_array();
        $user_arr[] = $rows;
        mysqli_free_result($result);
        $receive_name = $user_arr[0]['title'];
    }

    // $contents = "📢 親愛的家屬 ：有人留言給您，來去瞧瞧！👀\n";
    // $contents = "一碼通關心您\n";
    // $contents .= $receive_name . " 您好\n";
    // $contents .= "有人掃描您的會員通知二維碼\n";
    // // $contents .= "請盡速協尋您的家屬\n";
    // $contents .= "\n";
    // $contents .= "↓點撃查看二維碼詳情↓\n";
    // $contents .= "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("comment.php?history_id=" . $history_id) . "\n";

    // if ($lat !== "" && $lng !== "") {
    //     $contents .= "\n";
    //     $contents .= "↓定位如下↓\n";
    //     $contents .= "https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng;
    // }
    $contentsArray = [
        "type" => "template",
        "source" => json_encode([
            "type" => "template",
            "altText" => "有人掃描您的會員通知二維碼",
            "template" => [
                "type" => "buttons",
                "text" => "$receive_name 您好\n有人掃描您的會員通知二維碼",
                "actions" => [
                    [
                        "type" => "uri",
                        "label" => "點擊查看留言資訊",
                        "uri" => "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("comment.php?history_id=" . $history_id)
                    ]
                ]
            ]
        ])
    ];
    
    // ✅ If location data exists, add another button
    if ($lat !== "" && $lng !== "") {
        // Decode JSON, modify, then encode back
        $decodedSource = json_decode($contentsArray["source"], true);
    
        $decodedSource["template"]["actions"][] = [
            "type" => "uri",
            "label" => "查看定位",
            "uri" => "https://www.google.com/maps/search/?api=1&query=" . $lat . "," . $lng
        ];
    
        // Re-encode JSON after modification
        $contentsArray["source"] = json_encode($decodedSource);
    }
    
    $msg[0] = [
        "type" => "template",
        "source" => $contentsArray["source"]  // Keep JSON string format
    ];

    //    $contents .= "https://findit.linebot.tw/comment.php?history_id=" . $history_id;

} elseif ($model == "toPeople") {
    @$reply_user = params_security($_POST['reply_user']);
    $user_query = "SELECT * FROM `member` WHERE `user_id` = '" . $reply_user . "';";
    $user_arr = array();
    if ($result = $mysqli->query($user_query)) {
        $rows = $result->fetch_array();
        $user_arr[] = $rows;
        mysqli_free_result($result);
    }
    $reply_name = $user_arr[0]['title'];

    $contents = "一碼通關心您\n";
    $contents .= $reply_name . " 已回覆您的留言↓\n"; //的家屬
    $contents .= "\n";
    $contents .= "回覆：「" . $contents1 . "」";
    $msg[0] = array("type" => "text", "source" => $contents);
} elseif ($model == "toAll") {
    $contents = br2nl(htmlspecialchars_decode($contents1));
    // if ($file1 != "") {
    //     $video_url = "https://" . $domain . "/uploads/others/" . $file1;
    //     $contents .= "\n $video_url";
    // }
    $msg[0] = array("type" => "text", "source" => $contents);
}



//可推播文字

if ($model == "toAll") {
    $upload_dir = "../uploads/others/";
    if ($file0 != "") {
        $file_loc = $upload_dir . $file0;

        if (file_exists($file_loc)) {
            $msg[1] = array("type" => "image", "source" => "https://" . $domain . "/uploads/others/" . $file0);
        }

        if ($file1 != "") {
            $file_loc1 = $upload_dir . $file1;
            if (file_exists($file_loc1)) {
                $msg[2] = array("type" => "video", "source" => "https://" . $domain . "/uploads/others/" . $file1, 'preview' => "https://" . $domain . "/assets/img/play.jpg");
            }
        }
    } else {
        if ($file1 != "") {
            $file_loc1 = $upload_dir . $file1;
            if (file_exists($file_loc1)) {
                $msg[1] = array("type" => "video", "source" => "https://" . $domain . "/uploads/others/" . $file1, 'preview' => "https://" . $domain . "/assets/img/play.jpg");
            }
        }
    }
}


/*
$msg[1] = array("type" => "image", "source" => "https://nantoubus.linebot.tw/images/qrcode.png");
//可推播圖片
*/

/*
 * 廣告投放條件:
 * (1) 若有設定起訖日，則需要在區間內 A.`start_date` / A.`end_date`
 * (2) 廣告類型要符合，如單一圖片、影片、圖文連結 A.`types_option`
 * (3) 匹配該QRCode對應的標籤 A.`types_box`
 * (4) 該廣告為「啟用」狀態 A.`orders`
 * (5) 該標籤為「啟用」狀態 B.`orders
 */
//$seed = date('YmdHis');
if ($model !== "toAll") {

    $result_arr = array(); // 圖文連結廣告
    $query = "SELECT A.*, 1 AS 'text' FROM `advertisement` AS A 
                JOIN `qr_type` AS B ON A.`types_box` LIKE CONCAT('%', B.qr_type_id, '%') 
            WHERE (A.`start_date` <= NOW() OR A.`start_date` IS NULL) 
              AND (A.`end_date` >= NOW() OR A.`end_date` IS NULL) 
              AND A.`types_option` = '圖文連結'
              AND A.`types_box` LIKE '%" . $qr_type_id . "%'
              AND B.`qr_type_id` = '" . $qr_type_id . "' 
              AND A.`orders` >= 0 
              AND B.`orders` >= 0 
            ORDER BY RAND() LIMIT 0,10; ";

    $query1 = "";

    if ($result = $mysqli->query($query)) {
        $total = mysqli_num_rows($result);
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_arr[] = $row;

            $query1 .= "UPDATE `advertisement` SET `display` = `display` + 1, last_date = NOW() WHERE `advertisement_id` = '" . $row["advertisement_id"] . "'; ";
            $query1 .= "INSERT INTO `advertisement_day` (`advertisement_day_id`, `advertisement_id`, `display`, `count`, `pub_date`) VALUES (UUID(), '" . $row["advertisement_id"] . "', 1, 0, DATE(NOW())) ON DUPLICATE KEY UPDATE `display` = `display` +1; ";
        }
        mysqli_free_result($result);
    }

    if ($mysqli->multi_query($query1)) {
        while ($mysqli->more_results() && $mysqli->next_result());
    }

    if ($total > 0) {
        $result_arr = array_unique($result_arr);
        $rows = array();
        foreach ($result_arr as $value) {
            $photo = "https://" . $domain . "/uploads/others/" . $value['file0'];
            $title = $value['title'];
            $sub = $value['contents'];
            $btn1 = "連結";
            $url1 = "https://" . $domain . "/ad_redirect.php?advertisement_id=" . $value['advertisement_id'] . "&link=" . urlencode($value['introd']);

            $rows[] = [
                "type" => "bubble",
                "hero" => [
                    "type" => "image",
                    "size" => "full",
                    "aspectRatio" => "20:11",
                    "aspectMode" => "cover", //
                    "url" => $photo
                ],
                "body" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "spacing" => "sm",
                    "contents" => [
                        [
                            "type" => "text",
                            "text" => $title,
                            "wrap" => true,
                            "weight" => "bold",
                            "size" => "md"
                        ],
                        [
                            "type" => "box",
                            "layout" => "baseline",
                            "contents" => [
                                [
                                    "type" => "text",
                                    "text" => $sub,
                                    "wrap" => true,
                                    "size" => "sm",
                                    "flex" => 0
                                ]
                            ]
                        ]
                    ]
                ],
                "footer" => [
                    "type" => "box",
                    "layout" => "vertical",
                    "spacing" => "sm",
                    "contents" => [
                        [
                            "type" => "button",
                            "style" => "primary",
                            "action" => [
                                "type" => "uri",
                                "label" => $btn1,
                                "uri" => $url1
                            ],
                            "color" => "#1f7399"
                        ]
                    ]
                ]
            ];
        }
        $msg[1] = array("type" => "flex", "source" => ["type" => "flex", "altText" => "好消息~", "contents" => ["type" => "carousel", "contents" => $rows]]);
    }
}

file_put_contents(dirname(__FILE__) . "/./log/" . date("Ymdhis") . "notify.txt", json_encode($msg, JSON_UNESCAPED_UNICODE));

//echo json_encode($msg);
//exit();


/*
$photo = "https://nantoubus.linebot.tw/images/qrcode.png";
$title = "我是標題";
$sub = "我是副標題";
$btn1 = "我是按紐1";
$btn2 = "我是按紐2";
$url1 = "https://www.pintech.com.tw/blog.php?" . urlencode("id=61&title=魔鬼藏在低價裡 | 網站委外建置，資安必備了嗎？關於資訊安全,網站外包,網站建置,資安,SSDLC,資安威脅,系統資安,網站開發的問題討論");
$url2 = "https://tw.yahoo.com";
//下面是推播旋轉木馬, rows可以多筆
$rows[] = [
    "type" => "bubble",
    "hero" => [
        "type" => "image",
        "size" => "full",
        "aspectRatio" => "20:11",
        "aspectMode" => "cover", //
        "url" => $photo
    ],
    "body" => [
        "type" => "box",
        "layout" => "vertical",
        "spacing" => "sm",
        "contents" => [
            [
                "type" => "text",
                "text" => $title,
                "wrap" => true,
                "weight" => "bold",
                "size" => "lg"
            ],
            [
                "type" => "box",
                "layout" => "baseline",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => $sub,
                        "wrap" => true,
                        "size" => "lg",
                        "flex" => 0
                    ]
                ]
            ]
        ]
    ],
    "footer" => [
        "type" => "box",
        "layout" => "vertical",
        "spacing" => "sm",
        "contents" => [
            [
                "type" => "button",
                "style" => "primary",
                "action" => [
                    "type" => "uri",
                    "label" => $btn1,
                    "uri" => $url1
                ],
                "color" => "#1f7399"
            ], [
                "type" => "button",
                "style" => "primary",
                "action" => [
                    "type" => "uri",
                    "label" => $btn2,
                    "uri" => $url2
                ],
                "color" => "#1f7399"
            ]
        ]
    ]
];

$msg[0] = array("type" => "flex", "source" => ["type" => "flex", "altText" => "我是在手機訊息列表標題", "contents" => ["type" => "carousel", "contents" => $rows]]);
*/

line_notify($channelAccessToken, $users, $msg);
