<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");

//分享功能如果升級帳號要更改LINE ID @@@@@@@@@@@@@@@@@@@@@@
/*
舊
https://admin-official.line.me/beacon/register#/

新
https://www.linebiz.com/tw/
https://manager.line.biz/beacon/register

總設定
https://developers.line.biz/en/
*/

$channelAccessToken = 'LquHnxgBs6+ChUvSfQdsZ0BER/dN1BK9/jDet/WGg3lb4ujVoObBXOKDQoG3cV+gBf5YAhqBrDj8g9T/NnCQwUwN88soy3m112nPI92I44h+02+y61H7JsxrsIWSD5FCQuSX9cN6Abc5sBDNLYIuEAdB04t89/1O/w1cDnyilFU=';
$channelSecret = '3d69302c7055ec4972038f7c3fc1bf40';

$line_string = file_get_contents('php://input');
$line_obj = json_decode($line_string); //為物件

//file_put_contents(dirname(__FILE__) . "/./log/" . date("Ymdhis") . ".txt", $line_string);

/*============================================== *\
當user用手機傳送資料給linebot時，為了要查看linebot回傳的數據，
會需要用到 file_get_contents讀取input的文件
linebot回傳的檔案會是json格式的物件
\*==============================================*/
$event = $line_obj->{"events"}[0];
$type = $event->{"type"};
$source_type = $event->{"source"}->{"type"}; //user / room / group
$userId = $event->{"source"}->{"userId"};
if ($source_type == "room") {
    $userId = $event->{"source"}->{"roomId"};
} else if ($source_type == "group") {
    $userId = $event->{"source"}->{"groupId"};
}
$message = $event->{"message"};
$reply_token = $event->{"replyToken"};
//$displayName = ($source_type === "user") ? getProfile($userId)["displayName"] : "";

$arr = array();

$reply_message = [];

if ($type == "follow") {

} elseif ($type == "message") {
    $message_type = $message->{"type"}; //

    if ($message_type == "text") {
        $keyword = params_security($message->{"text"});

        if ($keyword == "我的QRcode") {

            $is_admin = false;
            $query = "SELECT qr_type_big_id FROM member WHERE user_id = '" . $userId . "'; ";
            if ($result = $mysqli->query($query)) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                if (!empty($row["qr_type_big_id"])) {
                    $is_admin = true;
                }
                mysqli_free_result($result);
            }

//            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "掃描QRcode", "uri" => "https://line.me/R/nv/QRCodeReader"]];
            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "個人通知條碼", "uri" => "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_list.php")]];
            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "所屬單位條碼", "uri" => "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("big_code_list.php")]];
            if ($is_admin) {
                $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "管理單位會員", "uri" => "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("big_all_list.php")]];
            }

            $reply_message = [["type" => "text", "text" => "請點選您要的功能", "quickReply" => ["items" => $arr]]];

        } elseif ($keyword == "觀看影片拿點數") {

            $is_get = false; // 是否已經獲得YT點數，若有則不用轉跳
            $is_done = false; // 是否已經完成YT任務，若無則需要顯示第2個按紐
            $contents = "";
            $link2 = "";
            $query = "SELECT `link2`, `contents1`, 1 AS 'setting_data' FROM `setting` WHERE id = '1'; ";
            $query .= "SELECT *, 1 AS 'point_data' FROM point WHERE user_id = '" . $userId . "' AND types_option = 1; ";
            if ($mysqli->multi_query($query)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['setting_data'])) {
                                $contents = $row['contents1'];
                                $link2 = $row['link2'];
                            }
                            if (isset($row['point_data'])) {
                                if (intval($row["is_done"]) >= 1) {
                                    $is_done = true; // 已經完成YT任務
                                } elseif (intval($row["is_done"]) <= -1) {
                                    $is_get = true; // 已經觀看YT影片，但是還沒完成任務(回答問題)
                                }
                            }

                        }
                        $result->close();
                    }
                    if ($mysqli->more_results()) {

                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }

            if (!$is_get) {
                $link = "https://letsgo.linebot.tw/api/nosession_redirect.php?openExternalBrowser=1&types=" . aes_encrypt(1) . "&user_id=" . aes_encrypt($userId) . "&display_name=" . aes_encrypt($displayName);
            } else {
                $link = $link2;
            }

            $string = br2nl($contents);
            $searchReplaceArray = array(
                '&nbsp;' => '',
                '&amp;' => '&'
            );
            $content = str_replace(array_keys($searchReplaceArray), array_values($searchReplaceArray), $string);
            // $content = html_entity_decode($content);

            $reply_message = [[
                "type" => "flex",
                "altText" => "觀看影片後成功答對問題，即可獲得一點點數",
                "contents" => [
                    "type" => "bubble",
                    "body" => [
                        "type" => "box",
                        "layout" => "vertical",
                        "contents" => [
                            [
                                "type" => "box",
                                "layout" => "vertical",
                                "margin" => "lg",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => $content,
                                        "wrap" => true
                                    ]
                                ]
                            ],
                            [
                                "type" => "box",
                                "layout" => "vertical",
                                "margin" => "lg",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => "（*活動任務每帳號僅限領取一次一點點數，不重複領取）",
                                        "wrap" => true,
                                        "color" => "#F44336",
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
                                "action" => [
                                    "type" => "uri",
                                    "label" => "觀看影片",
                                    "uri" => $link
                                ],
                                "style" => "link"
                            ],
                            [
                                "type" => "spacer",
                                "size" => "sm"
                            ]
                        ],
                        "flex" => 0
                    ]
                ]
            ]];

            // 如果看完影片後，還沒回答問題就跳出旋轉木馬3則
            if ($is_get && !$is_done) {
                $line_arr = [[
                    "type" => "button",
                    "style" => "link",
                    "action" => [
                        "type" => "message",
                        "label" => "問答開始",
                        "text" => "問答開始"
                    ]
                ]];
                array_splice($reply_message[0]["contents"]["footer"]["contents"], 1, 0, $line_arr);
            }

        } elseif ($keyword == "問答開始") {

            $result_arr = array();
            $temp = array("a", "b");
            $is_get = false; // 是否已經獲得YT點數，若有則不用轉跳
            $is_done = false; // 是否已經完成YT任務，若無則需要顯示第2個按紐
            $point_id = ""; // 如果已經看過影片，會有點數uuid

            $query = "SELECT *, 1 AS 'question_data' FROM `question` WHERE orders>=0 AND is_power <= -1 ORDER BY RAND(); ";
            $query .= "SELECT *, 1 AS 'point_data' FROM point WHERE user_id = '" . $userId . "' AND types_option = 1; ";
            if ($mysqli->multi_query($query)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['question_data'])) {
                                $result_arr[] = $row;
                            }
                            if (isset($row['point_data'])) {
                                $point_id = $row["point_id"];
                                if (intval($row["is_done"]) >= 1) {
                                    $is_done = true; // 已經完成YT任務
                                } elseif (intval($row["is_done"]) <= -1) {
                                    $is_get = true; // 已經觀看YT影片，但是還沒完成任務(回答問題)
                                }
                            }

                        }
                        $result->close();
                    }
                    if ($mysqli->more_results()) {

                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }


            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $result_arr[] = $row;
                }
                mysqli_free_result($result);
            }

            if (count($result_arr) > 0) {
                foreach ($result_arr as $index => $value) {
                    $arr[] = [
                        // "size" => "giga",
                        "type" => "bubble",
                        "body" => [
                            "type" => "box",
                            "layout" => "vertical",
                            "contents" => [
                                [
                                    "type" => "text",
                                    "text" => $value['title'],
                                    "weight" => "bold",
                                    "size" => "xl",
                                    "wrap" => true,
                                    "flex" => 5,
                                ]
                            ]
                        ],
                        "footer" => [
                            "type" => "box",
                            "layout" => "vertical",
                            "spacing" => "sm",
                            "contents" => [
                                [
                                    "type" => "spacer",
                                    "size" => "sm"
                                ]
                            ],
                            "flex" => 0
                        ]
                    ];

                    foreach ($temp as $i => $t) {
                        ${$t . "arr"} = [[
                            "type" => "text",
                            "text" => $value["answer_" . $t],
                            "action" => [
                                "type" => "postback",
                                "label" => $value["answer_" . $t],
                            ],
                            "wrap" => true,
                            "align" => "center",
                            "margin" => "md",
                            "size" => "lg",
                            "color" => "#104874"
                        ]];

                        if (intval($value["correct_" . $t]) >= 1) {
                            ${$t . "arr"}[0]["action"]["data"] = "action=bingo&point_id=" . aes_encrypt($point_id) . "&question_id=" . aes_encrypt($value["question_id"]) . "&is_done=" . aes_encrypt($is_done);
                        } else {
                            ${$t . "arr"}[0]["action"]["data"] = "action=wrong";
                        }

                        array_splice($arr[$index]["footer"]["contents"], $i, 0, ${$t . "arr"});
                    }

                }

                $reply_message = [
                    [
                        "type" => "flex",
                        "altText" => "還差一點點就完成活動任務囉",
                        "contents" => [
                            "type" => "bubble",
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "box",
                                        "layout" => "vertical",
                                        "margin" => "lg",
                                        "spacing" => "sm",
                                        "contents" => [
                                            [
                                                "type" => "text",
                                                "text" => "加油！還差一點點就完成活動任務囉，成功答對下方任一問題即可獲得一點點數！",
                                                "wrap" => true
                                            ]
                                        ]
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "vertical",
                                        "margin" => "lg",
                                        "spacing" => "sm",
                                        "contents" => [
                                            [
                                                "type" => "text",
                                                "text" => "（*活動任務每帳號僅限領取一次一點點數，不重複領取）",
                                                "wrap" => true,
                                                "color" => "#F44336",
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
                                        "type" => "spacer",
                                        "size" => "sm"
                                    ]
                                ],
                                "flex" => 0
                            ]
                        ]
                    ],
                    [
                        "type" => "flex",
                        "altText" => "問答開始",
                        "contents" => [
                            "type" => "carousel",
                            "contents" => $arr
                        ]
                    ]
                ];

            } else {
                $reply_message = [["type" => "text", "text" => "問答建置中"]];
            }

        } elseif ($keyword == "搶答拿點數") {

            $is_done = false; // 任務是否完成
            $is_get = false; // 是否已經獲得QA點數，若有則不用轉跳
            $query = "SELECT SUM(`coin`) AS 'points', 1 AS 'mission_data' FROM `point` WHERE `user_id` = '" . $userId . "' AND orders>=1 AND types_option <= 3; ";
            $query .= "SELECT COUNT(*) AS 'counts', 1 AS 'point_data' FROM point WHERE user_id = '" . $userId . "' AND types_option = 5; ";
            if ($mysqli->multi_query($query)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['mission_data'])) {
                                if (intval($row["points"]) === 3) {
                                    $is_done = true; // 任務完成
                                }
                            }
                            if (isset($row['point_data'])) {
                                if (intval($row["counts"]) === 1) {
                                    $is_get = true; // 已經獲得QA點數
                                }
                            }

                        }
                        $result->close();
                    }
                    if ($mysqli->more_results()) {

                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }

            if (!$is_done) {
                $contents = "需先完成活動辦法之三項活動任務，才能開啟『Q&A搶答拿點數』獲得隱藏點數呦！";
                $string = br2nl($contents);
                $searchReplaceArray = array(
                    '&nbsp;' => '',
                    '&amp;' => '&'
                );
                $content = str_replace(array_keys($searchReplaceArray), array_values($searchReplaceArray), $string);
                $arr[] = ["type" => "action", "action" => ["type" => "message", "label" => "觀看影片拿點數", "text" => "觀看影片拿點數"]];
                $arr[] = ["type" => "action", "action" => ["type" => "message", "label" => "Q&A搶答拿點數", "text" => "搶答拿點數"]];

                $reply_message = [["type" => "text", "text" => $content, "quickReply" => ["items" => $arr]]];

            } else {
                $query = "SELECT * FROM `question` WHERE orders>=0 AND is_power >=1  LIMIT 0,1 ";
                $temp = array("a", "b");
                if ($result = $mysqli->query($query)) {
                    $row = $result->fetch_array(MYSQLI_ASSOC);

                    $reply_message = [[
                        "type" => "flex",
                        "altText" => $row["title"],
                        "contents" => [
                            "size" => "giga",
                            "type" => "bubble",
                            "body" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "contents" => [
                                    [
                                        "type" => "text",
                                        "text" => $row['title'],
                                        "weight" => "bold",
                                        "size" => "xl",
                                        "wrap" => true,
                                        "flex" => 5,
                                    ]
                                ]
                            ],
                            "footer" => [
                                "type" => "box",
                                "layout" => "vertical",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "spacer",
                                        "size" => "sm"
                                    ]
                                ],
                                "flex" => 0
                            ]
                        ]
                    ]];

                    foreach ($temp as $i => $t) {
                        ${$t . "arr"} = [[
                            "type" => "button",
                            "action" => [
                                "type" => "postback",
                                "label" => $row["answer_" . $t],
                            ],
                            "style" => "link"
                        ]];

                        if (intval($row["correct_" . $t]) >= 1) {
                            ${$t . "arr"}[0]["action"]["data"] = "action=bingo&question_id=" . aes_encrypt($row["question_id"]) . "&is_done=" . aes_encrypt($is_get);
                        } else {
                            ${$t . "arr"}[0]["action"]["data"] = "action=wrong";
                        }

                        array_splice($reply_message[0]["contents"]["footer"]["contents"], $i, 0, ${$t . "arr"});
                    }
                    mysqli_free_result($result);
                }
            }

        } elseif ($keyword == "庇護好讚") {

            $link = "https://letsgo.linebot.tw/api/nosession_redirect.php?openExternalBrowser=1&types=" . aes_encrypt(2) . "&user_id=" . aes_encrypt($userId) . "&display_name=" . aes_encrypt($displayName);
            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "按讚拿點數", "uri" => $link]];
            $reply_message = [["type" => "text", "text" => "請點選您要的功能", "quickReply" => ["items" => $arr]]];

        } elseif ($keyword == "我的點數") {
            // line message api 如果是uri形式，連結不能有中文 (LIFF內可以)
            $unit = 3;
            $total = 0;
            $query = "SELECT SUM(`coin`) AS 'point_count' FROM `point` WHERE `user_id` = '" . $userId . "' AND orders>=1; ";
            $query .= "SELECT point, 1 AS 'setting_data' FROM `setting_data` WHERE `id` = 1; ";
            if ($mysqli->multi_query($query)) {
                do {
                    if ($result = $mysqli->store_result()) {
                        while ($row = $result->fetch_assoc()) {
                            if (isset($row['setting_data'])) {
                                $unit = intval($row['point']);
                            }
                            if (isset($row['point_count'])) {
                                $total = intval($row['point_count']);
                            }

                        }
                        $result->close();
                    }
                    if ($mysqli->more_results()) {

                    }
                } while ($mysqli->more_results() && $mysqli->next_result());
            }

            $chance = floor($total / $unit);
            $now = date("Y-m-d H:i:s");

            $reply_message = [[
                "type" => "flex",
                "altText" => "完成任務即可累點，" . $unit . "點獲得抽獎券一張",
                "contents" => [
                    "type" => "bubble",
                    "body" => [
                        "type" => "box",
                        "layout" => "vertical",
                        "contents" => [
//                            [
//                                "type" => "text",
//                                "text" => "目前點數",
//                                "weight" => "bold",
//                                "align" => "center",
//                                "size" => "xl",
//                            ],
                            [
                                "type" => "box",
                                "layout" => "vertical",
                                "margin" => "lg",
                                "spacing" => "sm",
                                "contents" => [
                                    [
                                        "type" => "box",
                                        "layout" => "baseline",
                                        "spacing" => "sm",
                                        "contents" => [
                                            [
                                                "type" => "text",
                                                "text" => "我的點數",
                                                "flex" => 3,
                                                "size" => "lg",
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $total . " 點",
                                                "wrap" => true,
                                                "flex" => 5,
                                                "size" => "lg",
                                                "align" => "end",
                                            ],
                                        ]
                                    ],
                                    [
                                        "type" => "box",
                                        "layout" => "baseline",
                                        "spacing" => "sm",
                                        "contents" => [
                                            [
                                                "type" => "text",
                                                "text" => "我的抽獎券",
                                                "flex" => 3,
                                                "size" => "lg",
                                            ],
                                            [
                                                "type" => "text",
                                                "text" => $chance . " 張",
                                                "wrap" => true,
                                                "flex" => 5,
                                                "size" => "lg",
                                                "align" => "end",
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "footer" => [
                        "type" => "box",
                        "layout" => "horizontal",
                        "spacing" => "sm",
                        "contents" => [
                            [
                                "type" => "button",
                                "action" => [
                                    "type" => "uri",
                                    "label" => "集點紀錄",
                                    "uri" => "https://liff.line.me/2000450749-OwL8JKg6?end_point=" . aes_encrypt("/liff/point.php")
                                ],
                                "style" => "link"
                            ]
                        ],
                        "flex" => 0
                    ]
                ]
            ]];

        } elseif ($keyword == "掃描集好運") {

            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "活動辦法", "uri" => "https://liff.line.me/2000450749-OwL8JKg6?end_point=" . aes_encrypt("/liff/info.php")]];
            $arr[] = ["type" => "action", "action" => ["type" => "uri", "label" => "掃描QR", "uri" => "https://line.me/R/nv/QRCodeReader"]];
            $reply_message = [["type" => "text", "text" => "請點選您要的功能", "quickReply" => ["items" => $arr]]];

        } elseif ($keyword == "庇護工場") {

            $result_arr = array();
            $query = "SELECT * FROM `shop` WHERE orders >=0 ORDER BY RAND(), orders DESC LIMIT 0,10 ";
            if ($result = $mysqli->query($query)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $result_arr[] = $row;
                }
                mysqli_free_result($result);
            }

            if (count($result_arr) > 0) {
                foreach ($result_arr as $index => $value) {
                    if (empty($value['file0'])) {
                        $image = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/uploads/others/default.jpg";
                    } else {
                        $image = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/uploads/others/" . $value['file0'];
                    }
                    $arr[] = [
                        "type" => "bubble",
                        "header" => [
                            "type" => "box",
                            "layout" => "vertical",
                            "paddingAll" => "0px",
                            "contents" => [
                                [
                                    "type" => "box",
                                    "layout" => "horizontal",
                                    "contents" => [
                                        [

                                            "type" => "image",
                                            "url" => $image,
                                            "size" => "full",
                                            "aspectMode" => "cover",
                                            "aspectRatio" => "3:2",
                                            "gravity" => "center",
                                            "flex" => 1
                                        ]
                                    ]
                                ]
                            ],
                        ],
                        "body" => [
                            "type" => "box",
                            "layout" => "vertical",
                            // "paddingAll" => "20px",
                            // "backgroundColor" => "#ffffff",
                            "contents" => [
                                [
                                    "type" => "box",
                                    "layout" => "vertical",
                                    "contents" => [
                                        [
                                            "type" => "text",
                                            "text" => $value['title'],
                                            "weight" => "bold",
                                            "size" => "xl",
                                            "wrap" => true,
                                            "flex" => 5,
                                        ],
                                        [
                                            "type" => "box",
                                            "layout" => "vertical",
                                            "margin" => "lg",
                                            "spacing" => "sm",
                                            "contents" => [
                                                [
                                                    "type" => "box",
                                                    "layout" => "baseline",
                                                    "spacing" => "sm",
                                                    "contents" => [
                                                        [
                                                            "type" => "text",
                                                            "text" => str_remove($value['contents'], 30),
                                                            "margin" => "sm",
                                                            "size" => "md",
                                                            "wrap" => true,
                                                            "flex" => 5,
                                                            "color" => "#666666",
                                                        ]
                                                    ]
                                                ]
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "footer" => [
                            "type" => "box",
                            "layout" => "vertical",
                            "spacing" => "sm",
                            "flex" => 0,
                            "contents" => [
                                [
                                    "type" => "button",
                                    "style" => "link",
                                    "height" => "sm",
                                    "action" => [
                                        "type" => "uri",
                                        "label" => "關於我們",
                                        "uri" => "https://liff.line.me/2000450749-OwL8JKg6?end_point=" . aes_encrypt("/liff/workshop.php?shop_id=" . $value['shop_id'])
                                    ]
                                ],
                                [
                                    "type" => "button",
                                    "style" => "link",
                                    "height" => "sm",
                                    "action" => [
                                        "type" => "uri",
                                        "label" => "追蹤拿點數",
                                        "uri" => "https://letsgo.linebot.tw/api/nosession_redirect.php?openExternalBrowser=1&types=" . aes_encrypt(3) . "&shop_id=" . aes_encrypt($value['shop_id']) . "&user_id=" . aes_encrypt($userId) . "&display_name=" . aes_encrypt($displayName)
                                    ]
                                ],
                                [
                                    "type" => "box",
                                    "layout" => "vertical",
                                    "contents" => [],
                                    "margin" => "sm",
                                ]
                            ]
                        ]
                    ];

                    if (!empty($value["link2"])) {
                        $line_arr = [[
                            "type" => "button",
                            "style" => "link",
                            "height" => "sm",
                            "action" => [
                                "type" => "uri",
                                "label" => "加入LINE好友",
                                "uri" => $value["link2"]
                            ]
                        ]];
                        array_splice($arr[$index]["footer"]["contents"], 2, 0, $line_arr);
                    }
                }

                $reply_message = [[
                    "type" => "flex",
                    "altText" => "庇護工場",
                    "contents" => [
                        "type" => "carousel",
                        "contents" => $arr
                    ]
                ]];

            } else {
                $reply_message = [["type" => "text", "text" => "庇護工場建置中"]];
            }


        } elseif ($keyword == "好運降臨") {

        } else {
            $reply_message = [["type" => "text", "text" => "目前為自動回覆模式，無法對用戶個別回覆，請使用【手機】點選下方選單進行操作。"]];
//            $reply_message = [["type" => "text", "text" => "很抱歉，這個帳號目前為自動回覆模式，沒有辦法對用戶個別回覆，請使用【手機】點選下方選單進行操作。\n\n咖小編感謝您！（鞠躬）"]];
        }

    } else {
        $reply_message = [["type" => "text", "text" => "目前為自動回覆模式，請使用【手機】點選下方選單進行操作。"]];
    }

} else if ($type == "postback") {
    $data = $event->{"postback"}->{"data"};
    parse_str($data, $query); // 解析data成array($query)

    if ($query["action"] == "wrong") {

        $reply_message = [["type" => "text", "text" => "答案錯誤"]];

    } elseif ($query["action"] === "bingo") {
        $question_id = aes_decrypt($query["question_id"]);
        $point_id = aes_decrypt($query["point_id"]);
        $is_done = aes_decrypt($query["is_done"]);
        $notify_point = false;
        $contents = "";

        $arr = array();
        $question_arr = array();
        $history_arr = array();

        $query = "SELECT *, 1 AS 'question_data' FROM question WHERE question_id = '" . $question_id . "' AND orders>=0; ";
        $query .= "SELECT *, 1 AS 'history_data' FROM history_question WHERE user_id = '" . $userId . "' AND is_power <=-1; ";
        $query .= "SELECT COUNT(*) AS 'counts', 1 AS 'point_data' FROM point WHERE user_id = '" . $userId . "' AND types_option = 5; ";
        if ($mysqli->multi_query($query)) {
            do {
                if ($result = $mysqli->store_result()) {
                    while ($row = $result->fetch_assoc()) {
                        if (isset($row["question_data"])) {
                            $question_arr[] = $row;
                            $contents = $row["contents"];
                        }
                        if (isset($row["history_data"])) {
                            $history_arr[] = $row;
                        }
                        if (isset($row["point_data"])) {
                            if (intval($row["counts"]) > 0) {
                                $is_done = true;
                            }
                        }
                    }
                    $result->close();
                }
                if ($mysqli->more_results()) {

                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }

        // 答對的回覆內容
        $string = br2nl($contents);
        $searchReplaceArray = array(
            '&nbsp;' => '',
            '&amp;' => '&'
        );
        $content = str_replace(array_keys($searchReplaceArray), array_values($searchReplaceArray), $string);

        $title = "啟航開麥拉";

        $query1 = "INSERT INTO `history_question`(`history_question_id`, `user_id`, `display_name`, `question_id`, `pub_date`, `last_date`, `orders`, `is_power`) SELECT UUID(), '" . $userId . "','" . $displayName . "','" . $question_id . "',NOW(),NOW(),1,'" . $question_arr[0]["is_power"] . "' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `history_question` WHERE `user_id` ='" . $userId . "' AND `question_id` = '" . $question_id . "'); ";
        $query1 .= "UPDATE `question` SET counts = counts + 1 WHERE question_id = '" . $question_id . "'; ";

        if ($question_arr[0]["is_power"] >= 1) {
            if (!$is_done) {
                $title = "Q&A搶答";
                $point_id = gen_uuid();
                $query1 .= "INSERT INTO `point`(`point_id`,`question_id`, `types_option`, `user_id`, `display_name`, `coin`, `pub_date`, `last_date`, `notify_date`, `orders`, `is_notify`, `is_done`) SELECT '" . $point_id . "', '" . $question_id . "',5,'" . $userId . "','" . $displayName . "',1,NOW(),NOW(),NOW(),1,1,1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `point` WHERE `user_id` ='" . $userId . "' AND `types_option` = 5); ";

                $arr = getPointMessage($title);
            }
        } elseif (count($history_arr) === 0 && !empty($point_id)) {
            $title = "啟航開麥拉";
            $query1 .= "UPDATE `point` SET `question_id` = '" . $question_id . "',`orders` = 1, `last_date` = NOW(), is_done = 1, is_notify = 1, notify_date = NOW() WHERE `orders` =-1 AND `point_id` = '" . $point_id . "'; ";

            $arr = getPointMessage($title);
        }

        if ($mysqli->multi_query($query1)) {
            while ($mysqli->more_results() && $mysqli->next_result()) ;
        }

        $reply_message = [["type" => "text", "text" => $content]];

        if (isset($arr["type"])) {
            $reply_message[] = $arr;
        }
    }

} elseif ($type == "join" or $type == "memberJoined") {

    //$reply_message =[["type" => "text","text" => "HI~您好~我是阿龜,初次見面請多多指教。\n1.在群組內輸入【阿龜】或【地圖】兩個字，就能在群組內共用地圖找房功能(目前不支援電腦版LINE)\n\n2.群組對話內容只要含有591物件網址，我也會把知道的所有事情跟大家說喔"]];
}


$header = array('Content-Type:application/json', 'Authorization: Bearer ' . $channelAccessToken);

$post_data = ["replyToken" => $reply_token, "messages" => $reply_message];

//file_put_contents(dirname(__FILE__) . "/./log/" . date("Ymdhis") . ".txt", json_encode($reply_message, JSON_UNESCAPED_UNICODE));
download_page("https://api.line.me/v2/bot/message/reply", json_encode($post_data), $header);

function getPointMessage($title)
{
    // 推播獲得點數訊息
    return [
        "type" => "flex",
        "altText" => "已獲得點數1點",
        "contents" => [
            "type" => "bubble",
            "body" => [
                "type" => "box",
                "layout" => "vertical",
                "contents" => [
                    [
                        "type" => "text",
                        "text" => "恭喜您完成『" . $title . "』",
                        "weight" => "bold",
                        "size" => "xl",
                        "color" => "#F44336",
                        "wrap" => true
                    ],
                    [
                        "type" => "box",
                        "layout" => "vertical",
                        "margin" => "lg",
                        "spacing" => "sm",
                        "contents" => [
                            [
                                "type" => "text",
                                "text" => "已獲得點數1點",
                                "wrap" => true,
                                "size" => "lg",
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
                        "height" => "sm",
                        "action" => [
                            "type" => "message",
                            "label" => "我的點數",
                            "text" => "我的點數"
                        ]
                    ],
                    [
                        "type" => "button",
                        "style" => "link",
                        "height" => "sm",
                        "action" => [
                            "type" => "uri",
                            "label" => "集點紀錄",
                            "uri" => "https://liff.line.me/2000450749-OwL8JKg6?end_point=" . aes_encrypt("/liff/point.php")
                        ]
                    ],
                    [
                        "type" => "spacer",
                        "size" => "sm"
                    ]
                ],
                "flex" => 0
            ]
        ]
    ];
}

// 旋轉木馬
function carousel($arr)
{
    $item = array();
    foreach ($arr as $key => $value) {
        $title = $value["title"];
        $sub = $value["sub"];
        $icon = $value["icon"];
        $link = $value["link"];

        $temp = [
            "thumbnailImageUrl" => $icon,
            "imageBackgroundColor" => "#FFFFFF",
            "title" => $title,
            "text" => $sub,
            "actions" => [
                ["type" => "uri", "label" => "更多", "uri" => $link]
            ]
        ];

        $item[] = $temp;

    }

    $reply_message = [[
        "type" => "template",
        "altText" => "點我看更多",
        "template" => [
            "type" => "carousel",
            "columns" => $item,
            "imageAspectRatio" => "rectangle",
            "imageSize" => "cover"
        ]
    ]];

    return $reply_message;
}

function getProfile($userId)
{
    global $channelAccessToken;
    $request_url = "https://api.line.me/v2/bot/profile/" . $userId;

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $request_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer ' . $channelAccessToken
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return json_decode($response, true);
}

/*
 size: xxs , xs , sm , md , lg , xl , xxl , 3xl , 4xl , 5xl, full

*/