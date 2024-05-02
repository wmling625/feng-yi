<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$history_id = params_security($_GET["history_id"]);

$result_arr = array();
$box_arr = array("全部狀態" => "", "已回覆" => "1", "未回覆" => "-1", "系統推播" => "2");

//$query = "SELECT * FROM history WHERE history_id = '" . $history_id . "';";
$query = "SELECT A.*, B.title AS name FROM history AS A INNER JOIN member AS B ON A.`user_id1`=B.`user_id` WHERE history_id = '" . $history_id . "'";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $result_arr[] = $rows;
    mysqli_free_result($result);
}

$image_arr = array("file0" => "廣告圖片");
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
                                        <tr>
                                            <th width="20%">推播內容</th>
                                            <td>
                                                <?php
                                                if (isset($result_arr[0]["pub_date"]) && isset($result_arr[0]["display_name"]) && isset($result_arr[0]["contents0"])) {
                                                    echo '<span class="text-muted text-sm">' . $result_arr[0]["pub_date"] . '</span>';
                                                    echo '<p class="m-0">';
                                                    echo $result_arr[0]["display_name"] . "：<br/>" . $result_arr[0]["contents0"];
                                                    echo '</p>';
                                                }
                                                $image_arr = array("file0" => "推播圖片");
                                                $upload_dir = "../uploads/others/";
                                                // 圖片
                                                foreach ($image_arr as $key => $value) {
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
                                                                // $file_sql = aes_encrypt("UPDATE advertisement set " . $key . " = '' WHERE advertisement_id = '" . $advertisement_id . "'");
                                                                $prev_str = '<a data-fancybox class="btn btn-sm btn-light btn-preview shadow" data-src="' . $file_loc . '" href="javascript:void(0)">預覽</a>';
                                                            }
                                                        }

                                                        echo '<label class="upload_cover img-thumbnail" style="background-image: url(' . $default . ');" name="' . $key . '_prev">';
                                                        echo '<input class="file" disabled type="file" onchange="' . $func_str . '" name="' . $key . '" id="' . $key . '">';
                                                        echo $prev_str;
                                                        echo '</label>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">回覆資訊</th>
                                            <td>
                                                <?php
                                                if (isset($result_arr[0]["orders"])) {
                                                    if ($result_arr[0]["orders"] == "-1") {
                                                        echo '<span class="text-danger text-sm">尚未回覆</span>';
                                                    } elseif ($result_arr[0]["orders"] == "1") {
                                                        if (isset($result_arr[0]["last_date"]) && isset($result_arr[0]["name"]) && isset($result_arr[0]["contents1"])) {
                                                            echo '<span class="text-muted text-sm">' . $result_arr[0]["last_date"] . '</span>';
                                                            echo '<p class="m-0">';
                                                            echo $result_arr[0]["name"] . "：" . $result_arr[0]["contents1"];
                                                            echo '</p>';
                                                        }
                                                    } else {
                                                        echo '<span class="text-secondary text-sm">系統自動推播，無法回覆</span>';
                                                    }
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <!--  <div class="card-footer d-flex justify-content-end">
                                    <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="history_id"
                                           value="<?php echo $history_id; ?>">
                                    <input type="hidden" name="model" value="<?php echo $model; ?>">
                                </div>
                            </div>-->
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