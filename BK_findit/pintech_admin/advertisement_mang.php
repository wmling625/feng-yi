<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$advertisement_id = params_security($_GET["advertisement_id"]);

$result_arr = array();
$box_arr = array("請選擇廣告類型" => array("", 0), "影片" => array("影片", 1), "圖文連結" => array("圖文連結", 2), "單一圖片" => array("單一圖片", 3));
$box_arr1 = array();
$box_arr2 = array();
$query = "SELECT *, 1 AS advertisement FROM advertisement WHERE advertisement_id = '" . $advertisement_id . "'; ";
$query .= "SELECT qr_type_id, title, 1 AS qr_type FROM `qr_type` WHERE orders >=0 ORDER BY orders ASC, pub_date DESC; ";
$query .= "SELECT qr_type_big_id, title, 1 AS qr_type_big FROM `qr_type_big` WHERE orders >=0 ORDER BY orders ASC, pub_date DESC; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['advertisement'])) {
                    $result_arr[] = $row;
                }
                if (isset($row['qr_type'])) {
                    $box_arr1[$row["title"]] = $row["qr_type_id"];
                }
                if (isset($row['qr_type_big'])) {
                    $box_arr2[$row["title"]] = $row["qr_type_big_id"];
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

$image_arr = array("file0" => array("廣告圖片", "", "1"));
$file_arr = array("file1" => array("廣告影片", "", "3"));
//$file_arr = array("file1" => "附件一");

?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>網站管理系統</title>
    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_link.php") ?>
</head>

<body class="sidebar-mini layout-footer-fixed layout-navbar-fixed layout-fixed">

<div class="wrapper">

    <!-- Preloader -->
    <!-- <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="../dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div> -->

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Admin Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)">
                    <i class="fa fa-user"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-sm-left">
                    <a href="#" class="dropdown-item">
                        <h3 class="dropdown-item-title">
                            <?php echo $_SESSION['admin']['nickname']; ?>
                        </h3>
                        <span class="text-sm text-muted"><?php echo $_SESSION['admin']['account']; ?></span>
                    </a>
                    <div class="dropdown-divider"></div>

                    <a href="logout.php" class="dropdown-item dropdown-footer">
                        <i class="fa fa-power-off" aria-hidden="true">登出</i>
                    </a>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="init.php" class="brand-link">
            <img src="../dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">網站管理系統</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <?php include_once(dirname(__FILE__) . "/../phplibs/backend_left.php"); ?>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?php echo $GLOBALS["template_title"]; ?></h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item">首頁</li>
                            <li class="breadcrumb-item active"><?php echo $GLOBALS["template_sub"]; ?></li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- /.row -->
                <div class="row">
                    <div class="col-12">
                        <form method="post" id="form" enctype="multipart/form-data"
                              action="<?php echo str_replace("_mang.php", "_mang_end.php", $_SERVER['PHP_SELF']) ?>">
                            <div class="card">
                                <div class="card-body table-responsive-sm">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <?php
                                        if ($model == "update") {
                                            echo '<tr>';
                                            echo '<th width="20%">建立時間</th>';
                                            echo '<td>' . (isset($result_arr[0]["pub_date"]) ? $result_arr[0]["pub_date"] : "-") . '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<th width="20%">更新時間</th>';
                                            echo '<td>' . (isset($result_arr[0]["last_date"]) ? $result_arr[0]["last_date"] : "-") . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        <tr>
                                            <th width="20%">標題<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="標題" class="form-control" name="title"
                                                       value="<?php echo isset($result_arr[0]["title"]) ? $result_arr[0]["title"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">廣告類別<span class="required">*</span></th>
                                            <td>
                                                <select req data-title="廣告類別" name="types_option"
                                                        class="form-control"
                                                        defaults="<?php echo isset($result_arr[0]["types_option"]) ? $result_arr[0]["types_option"] : ""; ?>">
                                                    <?php
                                                    foreach ($box_arr as $key => $value) {
                                                        if (!empty($value)) {
                                                            echo "<option value='" . $value[0] . "' data-qu='p" . $value[1] . "'>" . $key . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <span class="text-sm text-danger">※「影片」類廣告建議搭配「單一圖片」廣告，以達到最佳瀏覽效果</span>
                                            </td>
                                        </tr>
                                        <?php
                                        $upload_dir = "../uploads/others/";
                                        // 圖片
                                        foreach ($image_arr as $key => $value) {
                                            $required = ($value[1] !== "") ? "*" : "";
                                            $del_str = "";
                                            $prev_str = "";
                                            $file = "";
                                            $default = $upload_dir . "nophoto.png";
                                            $b64_selector = "textarea[name=" . $key . "_64]";
                                            $prev_selector = "[name=" . $key . "_prev]";
                                            $func_str = "selectFileImage(this, 200, 200, '" . $b64_selector . "', '" . $prev_selector . "', 'rwd')";

                                            if (isset($result_arr[0][$key]) && $result_arr[0][$key] !== "") {
                                                $default = $upload_dir . $result_arr[0][$key];
                                                $file = $result_arr[0][$key];
                                                $file_loc = $upload_dir . $file;
                                                if ($file != "") {
                                                    if (file_exists($file_loc)) {
                                                        $file_sql = aes_encrypt("UPDATE advertisement set " . $key . " = '' WHERE advertisement_id = '" . $advertisement_id . "'");

                                                        $del_str = '<a class="btn btn-light btn-remove shadow" href="javascript:void(0)" file_sql="' . $file_sql . '"><i class="fa-solid fa-xmark"></i></a>';
                                                        $prev_str = '<a data-fancybox class="btn btn-sm btn-light btn-preview shadow" data-src="' . $file_loc . '" href="javascript:void(0)">預覽</a>';
                                                    }
                                                }
                                            }
                                            echo '<tr class="hide">';
                                            echo '<th width="20%">';
                                            echo $value[0];
                                            echo '<span class="required">' . $required . '</span>';
                                            echo '<span class="mx-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                                      title="僅接受.png或.jpg檔">';
                                            echo '<i class="fa-regular fa-circle-question" aria-hidden="true"></i>';
                                            echo '</span>';
                                            echo '<br/><span class="text-xs text-danger font-weight-normal" id="adviseSize"></span>';
                                            echo '</th>';
                                            echo '<td>';

                                            echo '<label class="upload_cover img-thumbnail" style="background-image: url(' . $default . ');" name="' . $key . '_prev">';
                                            echo $del_str;
                                            echo '<input class="file" ' . $value[1] . ' data-title="' . $value[0] . '" type="file" onchange="' . $func_str . '" name="' . $key . '" data-file="' . aes_encrypt($file) . '" data-id="' . $value[2] . '">';
                                            echo $prev_str;
                                            echo '</label>';


                                            echo '<textarea name="' . $key . '_64" class="hide"></textarea>';
                                            echo '</td>';
                                            echo '</tr>';
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
                                            echo '<tr class="hide">';
                                            echo '<th width="20%">';
                                            echo $value[0];
                                            echo '<span class="required">' . $required . '</span>';
                                            echo '<span class="mx-1" data-bs-toggle="tooltip" data-bs-placement="top"
                                                      title="僅接受.mp4、.webm檔">';
                                            echo '<i class="fa-regular fa-circle-question" aria-hidden="true"></i>';
                                            echo '</span></th>';
                                            echo '<td>';
                                            echo '<label class="btn btn-outline-primary mb-0">';
                                            echo '<input accept="video/mp4,video/webm" style="display:none;" ' . $value[1] . ' class="filesupload" data-title="' . $value[0] . '" type="file" name="' . $key . '" value="" data-file="' . aes_encrypt($file) . '" data-id="' . $value[2] . '" />';
                                            echo '<i class="fa-solid fa-cloud-arrow-up"></i> 上傳檔案';
                                            echo '</label>';
                                            echo '<span class="text-sm mx-2" id="filename"></span>';
                                            echo $file_str;
                                            echo $del_str;
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        <tr class="hide">
                                            <th width="20%">廣告文字<span class="required"></span><br/><span class="text-xs text-danger font-weight-normal">至多50字</span></th>
                                            <td>
                                                <textarea data-id="2" data-title="廣告文字" class="form-control" rows="2" placeholder="請輸入廣告內文，至多約50字可達最佳瀏覽效果"
                                                          name="contents"><?php echo isset($result_arr[0]["contents"]) ? br2nl($result_arr[0]["contents"]) : "" ?></textarea>
                                            </td>
                                        </tr>
                                        <tr class="hide">
                                            <th width="20%">廣告連結<span class="required">*</span></th>
                                            <td>
                                                <input data-id="0" data-title="廣告連結" class="form-control"
                                                       name="introd" type="url"
                                                       value="<?php echo isset($result_arr[0]["introd"]) ? $result_arr[0]["introd"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">投放標籤群組</span>
                                            </th>
                                            <td>
                                                <div class="row p-2">
                                                    <?php
                                                    foreach ($box_arr1 as $key => $value) {
                                                        $checked = "";

                                                        // 如果存在就判斷有沒有勾選
                                                        if (isset($result_arr[0]['types_box'])) {
                                                            if (strpos($result_arr[0]['types_box'], $value) !== false) {
                                                                $checked = "checked";
                                                            }
                                                        }

                                                        echo '<div class="col-md-3 custom-control custom-checkbox">';
                                                        echo '<input class="custom-control-input custom-control-input-primary custom-control-input-outline" type="checkbox" id="' . $value . '" value="' . $value . '" title="' . $key . '" ' . $checked . '>';
                                                        echo '<label class="custom-control-label" for="' . $value . '">' . $key . '</label>';
                                                        echo '</div>';

                                                    }
                                                    ?>
                                                </div>
                                                <input type="text" class="hide" name="types_box"
                                                       value="<?php echo isset($result_arr[0]["types_box"]) ? htmlspecialchars($result_arr[0]["types_box"]) : ""; ?>"
                                                       box_str>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">投放單位</span>
                                            </th>
                                            <td>
                                                <div class="row p-2">
                                                    <?php
                                                    foreach ($box_arr2 as $key => $value) {
                                                        $checked = "";

                                                        // 如果存在就判斷有沒有勾選
                                                        if (isset($result_arr[0]['types_box2'])) {
                                                            if (strpos($result_arr[0]['types_box2'], $value) !== false) {
                                                                $checked = "checked";
                                                            }
                                                        }

                                                        echo '<div class="col-md-3 custom-control custom-checkbox">';
                                                        echo '<input class="custom-control-input custom-control-input-primary custom-control-input-outline" type="checkbox" id="' . $value . '" value="' . $value . '" title="' . $key . '" ' . $checked . '>';
                                                        echo '<label class="custom-control-label" for="' . $value . '">' . $key . '</label>';
                                                        echo '</div>';

                                                    }
                                                    ?>
                                                </div>
                                                <input type="text" class="hide" name="types_box2"
                                                       value="<?php echo isset($result_arr[0]["types_box2"]) ? htmlspecialchars($result_arr[0]["types_box2"]) : ""; ?>"
                                                       box_str>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">投放開始時間<span class="required"></span></th>
                                            <td>
                                                <input data-title="投放開始時間" type="text" placeholder="請選擇廣告投放開始時間"
                                                       class="form-control"
                                                       name="start_date"
                                                       value="<?php echo (isset($result_arr[0]["start_date"]) && $result_arr[0]["start_date"] !== "0000-00-00 00:00:00") ? $result_arr[0]["start_date"] : "" ?>">
                                            </td>
                                        </tr>

                                        <tr>
                                            <th width="20%">投放結束時間<span class="required"></span></th>
                                            <td>
                                                <input data-title="投放結束時間" type="text" placeholder="請選擇廣告投放結束時間"
                                                       class="form-control"
                                                       name="end_date"
                                                       value="<?php echo (isset($result_arr[0]["end_date"]) && $result_arr[0]["end_date"] !== "0000-00-00 00:00:00") ? $result_arr[0]["end_date"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">備註</th>
                                            <td>
                                                <textarea data-title="備註" class="form-control" rows="5"
                                                          name="note"><?php echo isset($result_arr[0]["note"]) ? br2nl($result_arr[0]["note"]) : "" ?></textarea>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="advertisement_id"
                                           value="<?php echo $advertisement_id; ?>">
                                    <input type="hidden" name="model" value="<?php echo $model; ?>">
                                </div>
                            </div>
                            <!-- /.card -->
                        </form>
                        <!-- /.form -->
                    </div>
                </div>
                <!-- /.row -->


            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <footer class="main-footer text-sm">
        <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
        All rights reserved.
    </footer>

</div>
<!-- ./wrapper -->

<?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>