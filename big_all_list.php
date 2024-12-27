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
    <meta name="keywords" content="會員通知,移車QR CODE,移動條碼" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate" />
    <meta name="viewport"
        content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo $title; ?> 會員清單-會員通知</title>
    <meta http-equiv="Content-Security-Policy"
        content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
    <meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
    <style>
        .code-list .item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            /* Adjust spacing between elements */
            margin-bottom: 24px;
            position: relative;
        }



        .code-list .item .icheck-primary {
            /* Add spacing between the checkbox and the next elements */
            display: flex;
            align-items: center;
        }

        .code-list .item input[type="checkbox"] {
            position: relative;
            z-index: 1;
            /* Ensure the checkbox doesn't overlap */
            margin: 0;
            margin-right: 8px;
        }

        .code-list .item .btn {
            padding-left: 10px;
            padding-right: 10px;
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
            width: 100%;
        }
    </style>
</head>

<body>
    <div id="main" class="main">
        <div class="page">
            <div class="title-box">
                <div class="container container-shrink">
                    <div class="row justify-content-center no-gutters">
                        <div class="col-lg-12">
                            <div class="item mb-5">
                                <h4 class="title font-weight-6"><?php echo $title; ?> 會員清單 <?php echo count($result_arr); ?>
                                    <!--<button type="button" name="clearSession" class="btn btn-outline-dark float-right">登出</button>-->
                                </h4>
                                <button type="button" class="btn btn-sm btn-primary mb-3" data-toggle="modal" data-bs-toggle="tooltip" title="推播給擁有該單位條碼的所有會員" id="lineNotify">
                                    推播訊息
                                </button>
                            </div>
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
                                echo '<div class="d-flex mb-4">';
                                echo '<div>';
                                echo '<span class="mx-5">全選</span>';
                                echo '</div>';
                                echo '<div class="icheck-primary mr-0">';
                                echo '<input type="checkbox" id="box_toggle" name="box_toggle">';
                                echo '<label for="box_toggle">';
                                echo '</label>';
                                echo '</div>';
                                echo '</div>';

                                foreach ($result_arr as $key => $value) {
                                    $link = "big_info.php?qrcode_big_id=" . aes_encrypt($value['qrcode_big_id']);

                                    echo '<div class="item">';
                                    echo '  <span class="p-3 title-1">' . ($key + 1) . '</span>';
                                    echo '  <div class="icheck-primary">';
                                    echo '    <input type="checkbox" id="' . $value['qrcode_big_id'] . '" name="box_list" value="' . $value['qrcode_big_id'] . '">';
                                    echo '    <label for="' . $value['qrcode_big_id'] . '"></label>';
                                    echo '  </div>';
                                    echo '  <a href="' . $link . '" class="textbox">';
                                    echo '    <p class="mb-0 title-1"><i class="fas fa-qrcode"></i>' . $value['nickname'] . '</p>';
                                    echo '  </a>';
                                    echo '  <div class="btn-box">';
                                    echo '    <a class="btn btn-primary" href="' . $link . '">查看</a>';
                                    echo '  </div>';
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
            <!-- Modal -->
            <div class="modal fade" id="lineNotifyModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">發送 QRCode</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" id="form" enctype="multipart/form-data" action="big_code_notify_end.php">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">推播內容<span class="required">*</span></label>
                                    <textarea req class="form-control" rows="10" name="message" id="message" placeholder="請輸入推播的訊息內容，僅接受純文字"></textarea>
                                </div>
                                <?php
                                $image_arr = array("file0" => "推播圖片");
                                $file_arr = array("file1" => array("推播影片", "", "3"));
                                $upload_dir = "/uploads/others/";
                                // 圖片
                                foreach ($image_arr as $key => $value) {
                                    $file = "";
                                    $default = $upload_dir . "nophoto.png";
                                    $b64_selector = "textarea[name=" . $key . "_64]";
                                    $prev_selector = "[name=" . $key . "_prev]";
                                    $func_str = "selectFileImage(this, 200, 200, '" . $b64_selector . "', '" . $prev_selector . "', 'rwd')";

                                    echo '<div class="form-group">';
                                    echo '<label for="' . $key . '">' . $value . '<span class="required"></span></label>';
                                    echo '<label class="upload_cover img-thumbnail" style="background-image: url(' . $default . ');" name="' . $key . '_prev">';
                                    echo '<input class="file" type="file" onchange="' . $func_str . '" name="' . $key . '" id="' . $key . '">';
                                    echo '</label>';
                                    echo '<textarea name="' . $key . '_64" class="hide"></textarea>';
                                    echo '</div>';
                                }

                                // 附件
                                foreach ($file_arr as $key => $value) {
                                    $required = ($value[1] !== "") ? "*" : "";
                                    $del_str = "";
                                    $file_str = "";
                                    if (isset($result_arr[0][$key]) && $result_arr[0][$key] !== "") {
                                        $file = $result_arr[0][$key];
                                        $file_loc = $upload_dir . $file;

                                        if ($file != "") {
                                            if (file_exists($file_loc)) {
                                                $file_str = "<a href='" . $file_loc . "' target='_blank'>" . $file . "</a>";

                                                $file_sql = aes_encrypt(" update advertisement set " . $key . " = '' where advertisement_id = '" . $advertisement_id . "'; ");

                                                $file_str = "<br/><a class='text-sm text-muted' href='" . $file_loc . "' target='_blank'><i class='fa-regular fa-file mr-2'></i>" . $file . "</a>";
                                                $del_str = '<a href="javascript:void(0)" class="text-sm text-danger ml-2" file_sql="' . $file_sql . '" ><i class="fa-solid fa-trash"></i></a>';
                                            }
                                        }
                                    }
                                    echo '<div class="form-group">';

                                    echo '<label for="' . $key . '">' . $value[0] . '</label>';
                                    echo '<span class="required">' . $required . '</span>';
                                    echo '<span class="mx-1" data-bs-toggle="tooltip" data-bs-placement="top" title="僅接受.mp4、.webm檔">';
                                    echo '<i class="fa-regular fa-circle-question" aria-hidden="true"></i>';
                                    echo '</span>';
                                    echo '<label class="btn btn-outline-primary mb-0">';
                                    echo '<input accept="video/mp4,video/webm" max_mb="70" style="display:none;" ' . $value[1] . ' class="filesupload" data-title="' . $value[0] . '" type="file" name="' . $key . '" value="" data-file="' . aes_encrypt($file) . '" data-id="' . $value[2] . '" />';
                                    echo '<i class="fa-solid fa-cloud-arrow-up"></i> 上傳檔案';
                                    echo '<span class="text-sm mx-2" id="filename"></span>';
                                    echo $file_str;
                                    echo $del_str;
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <input type="hidden" class="form-control" name="ids" id="ids" value="">
                                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                                <button type="button" class="btn btn-primary" id="lineNotifyConfirm">送出</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

            <?php /*
            <div class="container-shrink d-flex justify-content-center py-4">
                <a class="btn btn-outline-success"
                    href="<?php //echo $oa; ?><!--"><i class="lab la-line"></i> 返回官方帳號</a>
            </div>
            */?>
        </div>
    </div>
    <!-- End of Main Container -->

    <!-- Scripts -->
    <?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>
</body>

</html>