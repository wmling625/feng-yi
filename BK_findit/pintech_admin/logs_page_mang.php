<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$id = params_security($_GET["id"]);

$result_arr = array();
$box_arr = array("admin" => "管理員", "qr_type" => "標籤群組", "advertisement" => "廣告", "qrcode" => "QRCode", "member" => "會員", "smscode" => "簡訊發送紀錄");
$query = "SELECT * FROM `pintech_logs`.`listpage` WHERE `id`='" . $id . "'";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $result_arr[] = $rows;
    mysqli_free_result($result);
}

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
                        <!--<form method="post" id="form" enctype="application/x-www-form-urlencoded"
                              action="<?php echo str_replace("_mang.php", "_mang_end.php", $_SERVER['PHP_SELF']) ?>">-->
                        <div class="card">
                            <div class="card-body table-responsive-sm">
                                <table class="table table-bordered">
                                    <tbody>
                                    <tr>
                                        <th width="25%">操作時間</th>
                                        <td><?php echo isset($result_arr[0]["pub_date"]) ? $result_arr[0]["pub_date"] : "-"; ?></td>
                                    </tr>
                                    <tr>
                                        <th width="25%">域名</th>
                                        <td>
                                            <?php echo isset($result_arr[0]['domains']) ? $result_arr[0]['domains'] : ""; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="25%">來源頁</th>
                                        <td>
                                            <?php echo isset($result_arr[0]['start_link']) ? $result_arr[0]['start_link'] : ""; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="25%">目標頁</th>
                                        <td>
                                            <?php echo isset($result_arr[0]['end_link']) ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $result_arr[0]['end_link'] : ""; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="25%">IP</th>
                                        <td>
                                            <?php echo isset($result_arr[0]['ip']) ? $result_arr[0]['ip'] : ""; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th width="25%">Session</th>
                                        <td>
                                            <?php
                                            if (isset($result_arr[0]['session_content'])) {
                                                $arr = logs_replace_json($result_arr[0]['session_content'], 'permission');

                                                echo '<pre class="m-0">';
                                                echo json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                                                echo '</pre>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                            <!--<div class="card-footer d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="model" value="<?php echo $model; ?>">
                                </div>-->
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

        <!-- /.content-wrapper -->
        <footer class="main-footer text-sm">
            <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
            All rights reserved.
        </footer>
    </div>
</div>
<!-- ./wrapper -->

<?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>