<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$types = params_security($_GET["types"]);
@$keyword = params_security($_GET["keyword"]);
@$status = params_security($_GET["status"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);
@$model = "update";
@$id = '1';
$code = random_str(6, "int");

$url_param = add_pararm($_SERVER['PHP_SELF'], array("date_type" => $date_type,  "types" => $types, "keyword" => $keyword, "status" => $status, "limit" => $limit));
//$search_orders = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");
$search_orders = array("全部狀態" => "", "已使用" => "-1", "尚未使用" => "1");

// 頁碼相關設定
$total = 0;
$showrow = !empty($limit) ? $limit : 25;
$curpage = empty($_GET['page']) ? 1 : params_security($_GET['page'], "int");
$url_page = "?page={page}";

$filter_sql_arr = array();
$filter_sql_str = "";

if (!empty($status)) {
    if ($status == -1) {
        array_push($filter_sql_arr, "is_ok = -1");
    } else if ($status == 1) {
        array_push($filter_sql_arr, "is_ok = 1");
    }
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "(mobile LIKE '%" . $keyword . "%')");
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$url = $url_param . $url_page;

$result_arr = array();

$query = "SELECT * FROM `smscode` WHERE " . $filter_sql_str . "  ORDER BY `pub_date` DESC";

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
        <?php
        echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM `smscode` WHERE find_in_set(mobile, '?1') >0") . "'/>";
        ?>
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
                            <div class="card">
                                <div class="card-header">
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-bs-toggle="tooltip" title="新增驗證碼" id="smsNotify">
                                            新增
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>
                                    </div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <select class="form-control" name="status" data-bs-toggle="tooltip" title="回覆狀態"
                                                search_ref>
                                                <?php echo gen_options($search_orders, $status) ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <input type="text" class="form-control" data-bs-toggle="tooltip"
                                                title="電話號碼"
                                                name="keyword"
                                                placeholder="電話號碼"
                                                value="<?php echo $keyword; ?>" search_ref>
                                        </div>
                                        <div class="col-md-12 mt-2 row">
                                            <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                                <br /><span class="text-sm text-muted">排序：<span class="text-sm text-primary">新至舊</span></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 float-right d-flex flex-wrap align-items-end justify-content-end p-0">
                                                <?php echo $page->myde_showRow(); ?>
                                                <button type="button" name="search_button"
                                                    class="btn btn-sm btn-outline-danger mx-1">搜尋
                                                </button>
                                                <button type="button" name="clear_filter"
                                                    class="btn btn-sm btn-outline-info mx-1">清除條件
                                                </button>
                                            </div>
                                        </div>
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
                                                    echo '<span class="text-danger">已使用</span>';
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
                                    <input name="verify_code" readonly req class="form-control" id="verify_code" value="<?php echo $code ?>" />
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