<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$keyword = params_security($_GET["keyword"]);
@$orders = params_security($_GET["orders"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);

if (empty($min_date)) {
    $min_date = getNewDate(date("Y-m-d"), -180);
}
if (empty($max_date)) {
    $max_date = date("Y-m-d");
}
if (empty($date_type)) {
    $date_type = "last_date";
}

$url_param = add_pararm($_SERVER['PHP_SELF'], array("date_type" => $date_type, "min_date" => $min_date, "max_date" => $max_date, "types" => $types, "keyword" => $keyword, "orders" => $orders, "limit" => $limit));
//$search_orders = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");
$search_orders = array("全部回覆狀態" => "", "已回覆" => "1", "未回覆" => "-1", "系統推播" => "2");
$search_date_type = array("初次推播時間" => "pub_date", "最新回覆時間" => "last_date");

// 頁碼相關設定
$total = 0;
$showrow = !empty($limit) ? $limit : 25;
$curpage = empty($_GET['page']) ? 1 : params_security($_GET['page'], "int");
$url_page = "&page={page}";

$url = $url_param . $url_page;

$filter_sql_arr = array();
$filter_sql_str = "";

if (!empty($min_date)) {
    array_push($filter_sql_arr, "(date(A." . $date_type . ") >= '" . $min_date . "') ");
}

if (!empty($max_date)) {
    array_push($filter_sql_arr, "(date(A." . $date_type . ") <= '" . $max_date . "') ");
}

if (!empty($types)) {
    array_push($filter_sql_arr, "(region LIKE '%" . $types . "%')");
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "((A.display_name LIKE '%" . $keyword . "%') OR (B.title LIKE '%" . $keyword . "%') OR (contents0 LIKE '%" . $keyword . "%') OR (contents1 LIKE '%" . $keyword . "%'))");
}

if (!empty($orders)) {
    if ($orders == -1) {
        array_push($filter_sql_arr, "(A.orders <= -1)");
    } else if ($orders == 1) {
        array_push($filter_sql_arr, "(A.orders = 1)");
    } else if ($orders == 2) {
        array_push($filter_sql_arr, "(A.orders = 2)");
    }
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$result_arr = array();
$query = "SELECT A.*, B.title AS name FROM history AS A INNER JOIN member AS B ON A.`user_id1`=B.`user_id` WHERE " . $filter_sql_str . " ORDER BY A." . $date_type . " DESC";

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
</head>

<body class="sidebar-mini layout-footer-fixed layout-navbar-fixed layout-fixed">

<div class="wrapper">
    <?php
    echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM history WHERE find_in_set(history_id, '?1') >0") . "'/>";
    echo "<input type='hidden' name='orders_sql' value='" . aes_encrypt("UPDATE history SET orders = '?1' WHERE find_in_set(history_id, '?2') >0") . "'/>";
    ?>
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
                    <a href="javascript:void(0)" class="dropdown-item">
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
                        <div class="card">
                            <!--                            <div class="card-header">-->
                            <!--                                <div class="card-tools">-->
                            <!--                                    <a href="history_mang.php?model=add" type="button"-->
                            <!--                                       class="btn btn-sm btn-success">新增</a>-->
                            <!--                                    <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>-->
                            <!--                                </div>-->
                            <!--                            </div>-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 input-group mt-2">
                                        <div class="input-group-prepend">
                                            <select class="form-control" name="date_type" data-bs-toggle="tooltip"
                                                    title="篩選日期"
                                                    search_ref>
                                                <?php echo gen_options($search_date_type, $date_type) ?>
                                            </select>
                                        </div>
                                        <input type="date" class="form-control" name="min_date" data-bs-toggle="tooltip"
                                               title="<?php echo array_search($date_type, $search_date_type); ?>（預設180天前）"
                                               value="<?php echo $min_date; ?>" search_ref>
                                        <span class="input-group-text">至</span>
                                        <input type="date" class="form-control" name="max_date" data-bs-toggle="tooltip"
                                               title="<?php echo array_search($date_type, $search_date_type); ?>（預設今日）"
                                               value="<?php echo $max_date; ?>" search_ref>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="orders" data-bs-toggle="tooltip" title="回覆狀態"
                                                search_ref>
                                            <?php echo gen_options($search_orders, $orders) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <input type="text" class="form-control" data-bs-toggle="tooltip"
                                               title="暱稱、內容"
                                               name="keyword"
                                               placeholder="暱稱、內容"
                                               value="<?php echo $keyword; ?>" search_ref>
                                    </div>
                                    <div class="col-md-12 mt-2 row">
                                        <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                            <br/><span class="text-sm text-muted">排序：<span class="text-sm text-primary"><?php echo array_search($date_type, $search_date_type); ?>新至舊</span></span>
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
                                        <th width="10%">留言者</th>
                                        <th width="15%">推播內容</th>
                                        <th width="10%">狀態</th>
                                        <th width="10%">推播對象</th>
                                        <th width="15%">回覆內容</th>
                                        <th width="15%">功能</th>
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
                                        echo '<input type="checkbox" id="' . $value['history_id'] . '" name="box_list" value="' . $value['history_id'] . '">';
                                        echo '<label for="' . $value['history_id'] . '">';
                                        echo '</label>';
                                        echo '</div>';
                                        echo '</td>';
                                        echo '<td>' . $value["display_name"] . '</td>';
                                        echo '<td>' . str_remove($value["contents0"], 20) . '<br/><span class="text-muted text-sm">' . (strtotime($value["pub_date"]) && $value["pub_date"] !== "0000-00-00 00:00:00" ? $value["pub_date"] : "-") . '</span></td>';

                                        echo '<td>';
                                        if ($value["orders"] == "-1") {
                                            echo '<span class="text-danger">尚未回覆</span>';
                                        } elseif ($value["orders"] == "1") {
                                            echo '<span class="text-success">已回覆</span>';
                                        } elseif ($value["orders"] == "2") {
                                            echo '<span class="text-secondary">系統推播</span>';
                                        }
                                        echo '</td>';

                                        echo '<td>' . $value["name"] . '</td>';
                                        echo '<td>' . str_remove($value["contents1"], 20) . '<br/><span class="text-muted text-sm">' . (strtotime($value["last_date"]) && $value["last_date"] !== "0000-00-00 00:00:00" ? $value["last_date"] : "-") . '</span></td>';

                                        echo '<td>';
                                        echo '<a href="history_mang.php?model=update&history_id=' . $value["history_id"] . '" class="mx-1 btn btn-sm btn-outline-primary">查看</a>';
                                        echo '</td>';

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
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </section>

    </div><!-- /.container-fluid -->

    <!-- /.content -->

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