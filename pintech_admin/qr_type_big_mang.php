<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$qr_type_big_id = params_security($_GET["qr_type_big_id"]);

$result_arr = array();
$qr_type_arr = array();

$query = "SELECT *, 1 AS 'qr_type_big_data' FROM qr_type_big WHERE qr_type_big_id = '" . $qr_type_big_id . "';";
if (!empty($qr_type_big_id)) {
    $query .= "SELECT *, 1 AS 'member_data' FROM member WHERE qr_type_big_id = '" . $qr_type_big_id . "'; ";
}

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member_data'])) {
                    $qr_type_arr[] = $row;
                }
                if (isset($row['qr_type_big_data'])) {
                    $result_arr[] = $row;
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

//$image_arr = array("file0" => array("封面圖", "req"));
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
                                            echo '<th width="15%">建立時間</th>';
                                            echo '<td>' . (isset($result_arr[0]["pub_date"]) ? $result_arr[0]["pub_date"] : "-") . '</td>';
                                            echo '</tr>';
                                            echo '<tr>';
                                            echo '<th width="15%">更新時間</th>';
                                            echo '<td>' . (isset($result_arr[0]["last_date"]) ? $result_arr[0]["last_date"] : "-") . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        <tr>
                                            <th width="20%">單位名稱<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="單位名稱" class="form-control" name="title"
                                                       value="<?php echo isset($result_arr[0]["title"]) ? $result_arr[0]["title"] : "" ?>">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-body table-responsive-sm">
                                    <table class="table table-bordered">
                                        <tbody>
                                        <tr>
                                            <th width="20%">管理人員<span class="required"></span></th>
                                            <td>
                                                <?php
                                                if (count($qr_type_arr) > 0) {
                                                    echo '<ul class="col-md-3 list-group list-group-flush">';
                                                    foreach ($qr_type_arr as $val) {
                                                        echo '<a href="member_mang.php?model=update&member_id=' . $val["member_id"] . '" class="list-group-item d-flex justify-content-between align-items-center text-dark">' . $val["nickname"] . '<span class="badge badge-dark badge-pill">' . $val["account"] . '</span> </a>';
                                                    }
                                                    echo '</ul>';
                                                } else {
                                                    echo "<span class='text-muted'>尚無指定管理人員</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="qr_type_big_id" value="<?php echo $qr_type_big_id; ?>">
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