<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");


@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$keyword = params_security($_GET["keyword"]);
@$orders = params_security($_GET["orders"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);
@$model = "update";
@$id = '1';
$code = random_str(6, "int");


// 頁碼相關設定
$total = 0;
$showrow = !empty($limit) ? $limit : 25;
$curpage = empty($_GET['page']) ? 1 : params_security($_GET['page'], "int");
$url_page = "?page={page}";

$url = $url_param . $url_page;

$result_arr = array();
$query = "SELECT * FROM `smscode` ORDER BY `pub_date` DESC";

if ($result = $mysqli->query($query)) {
    $total = mysqli_num_rows($result);
    if (!empty($_GET['page']) && $total != 0 && $curpage > ceil($total / $showrow)) {
        $curpage = ceil($total / $showrow);
    }
    $query .= " LIMIT " . ((($curpage - 1) * $showrow)) . ", $showrow;";

    if ($result = $mysqli->query($query)) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_arr[] = $row;
        }
        mysqli_free_result($result);
    }
}
$page = new Page($total, $showrow, $curpage, $url, 2);


?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>網站管理系統</title>
    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_link.php") ?>
    <style type="text/css">
        .cke_button__nameofbutton_icon {
            display: none !important;
            /*without icon*/
        }

        .cke_button__nameofbutton_label {
            display: inline !important;
            /*show the text label*/
        }
    </style>
</head>

<body class="sidebar-mini layout-footer-fixed layout-navbar-fixed layout-fixed">

    <div class="wrapper">

        <!-- Preloader -->
        <!-- <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="../assets/img/favicon.ico" alt="AdminLTELogo" height="60" width="60">
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
                <img src="../assets/img/favicon.ico" alt="Logo" class="brand-image"
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
                                    <div class="card-header">
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-bs-toggle="tooltip" title="新增驗證碼" id="smsNotify">
                                                新增
                                            </button>
                                            <!-- <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button> -->
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <!-- /.card-header -->
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-bordered table-hover text-nowrap">
                                            <thead>
                                                <tr class="text-center">
                                                    <th width="5%">
                                                        <div class="icheck-primary d-inline">
                                                            <input type="checkbox" id="box_toggle" name="box_toggle">
                                                            <label for="box_toggle">
                                                            </label>
                                                        </div>
                                                    </th>
                                                    <th width="10%">驗證碼</th>
                                                    <th width="15%">電話號碼</th>
                                                    <th width="10%">狀態</th>
                                                    <th width="10%">創立日期</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-center">
                                                <?php
                                                if ($total == 0) {
                                                    echo '<tr>';
                                                    echo '<td colspan="9">查無資料</td>';
                                                    echo '</tr>';
                                                }

                                                foreach ($result_arr as $key => $value) {

                                                    echo '<tr>';

                                                    echo '<td>';
                                                    echo '<div class="icheck-primary d-inline">';
                                                    echo '<input type="checkbox" id="' . $value['smscode_id'] . '" name="box_list" value="' . $value['smscode_id'] . '">';
                                                    echo '<label for="' . $value['smscode_id'] . '">';
                                                    echo '</label>';
                                                    echo '</div>';
                                                    echo '</td>';
                                                    echo '<td>' . $value["code"] . '</td>';
                                                    echo '<td>' . $value["mobile"]  . '</td>';
                                                    echo '<td>';
                                                    if ($value["is_ok"] == "-1") {
                                                        echo '<span class="text-danger">以使用</span>';
                                                    } elseif ($value["orders"] == "1") {
                                                        echo '<span class="text-success">尚未使用</span>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . $value["pub_date"]  . '</td>';
                                                    echo '</tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->
                                    <?php
                                    if ($total > $showrow) {
                                        echo '<div class="card-footer clearfix">';
                                        echo $page->myde_write();
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                                <!-- /.card -->
                            </form>
                            <!-- /.form -->
                        </div>
                    </div>
                    <!-- /.row -->


                </div><!-- /.container-fluid -->
            </section>


            <!-- Modal -->
            <div class="modal fade" id="smsNotifyModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">新增驗證碼</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" id="form" enctype="multipart/form-data" action="verify_end.php">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">驗證碼<span class="required">*</span></label>
                                    <input name="verify_code" readonly req class="form-control" id="verify_code" value="<?php echo $code?>" />
                                </div>
                                <div class="form-group">
                                    <label for="message">電話號碼<span class="required">*</span></label>
                                    <input name="verify_mobile" req class="form-control" id="verify_mobile" value="" />
                                </div>

                            </div>
                            <div class="modal-footer justify-content-between">
                                <input type="hidden" class="form-control" name="ids" id="ids" value="">
                                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                                <button type="button" class="btn btn-primary" id="smsNotifyConfirm">送出</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <!-- <footer class="main-footer text-sm">
            <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
            All rights reserved.
        </footer> -->


    </div>
    <!-- ./wrapper -->
    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>