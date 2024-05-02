<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$keyword = params_security($_GET["keyword"]);
@$orders = params_security($_GET["orders"]);
@$limit = params_security($_GET["limit"]);

if (empty($min_date)) {
    $min_date = getNewDate(date("Y-m-d"), -180);
}
if (empty($max_date)) {
    $max_date = date("Y-m-d");
}

$url_param = add_pararm($_SERVER['PHP_SELF'], array("min_date" => $min_date, "max_date" => $max_date, "types" => $types, "keyword" => $keyword, "orders" => $orders, "limit" => $limit));
$types_option = array("全部權限" => "");
foreach ($menu_array as $key => $value) {
    foreach ($menu_array[$key] as $key1 => $value1) {
        if ($value1['link'] != "init.php" && $value1['link'] != "admin_list.php") {
            $types_option[$value1["title"]] = $value1['link'];
        }
    }
}
$orders_option = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");

// 頁碼相關設定
$total = 0;
$showrow = !empty($limit) ? $limit : 10;
$curpage = empty($_GET['page']) ? 1 : params_security($_GET['page'], "int");
$url_page = "&page={page}";

$url = $url_param . $url_page;

$filter_sql_arr = array();
$filter_sql_str = "";

if (!empty($min_date)) {
    array_push($filter_sql_arr, "(date(pub_date) >= '" . $min_date . "') ");
}

if (!empty($max_date)) {
    array_push($filter_sql_arr, "(date(pub_date) <= '" . $max_date . "') ");
}

if (!empty($types)) {
    array_push($filter_sql_arr, "(permission LIKE '%" . $types . "%')");
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "(nickname LIKE '%" . $keyword . "%')");
}

if (!empty($orders)) {
    if ($orders == -1) {
        array_push($filter_sql_arr, "(orders = -1)");
    } else if ($orders == 1) {
        array_push($filter_sql_arr, "(orders >= 0)");
    }
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$result_arr = array();
$query = "SELECT * FROM admin WHERE " . $filter_sql_str . " ORDER BY orders ASC, pub_date DESC";

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
    echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM admin WHERE find_in_set(admin_id, '?1') >0") . "'/>";
    echo "<input type='hidden' name='orders_sql' value='" . aes_encrypt("UPDATE admin SET orders = '?1' WHERE find_in_set(admin_id, '?2') >0") . "'/>";
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
                        <div class="card">
                            <div class="card-header">
                                <div class="card-tools">
                                    <a href="admin_mang.php?model=add" type="button"
                                       class="btn btn-sm btn-success">新增</a>
                                    <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 input-group mt-2">
                                        <input type="date" class="form-control" name="min_date" data-bs-toggle="tooltip"
                                               title="建立時間 (預設180天前)"
                                               value="<?php echo $min_date; ?>" search_ref>
                                        <span class="input-group-text">至</span>
                                        <input type="date" class="form-control" name="max_date" data-bs-toggle="tooltip"
                                               title="建立時間 (預設今日)"
                                               value="<?php echo $max_date; ?>" search_ref>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="types" search_ref data-bs-toggle="tooltip"
                                                title="權限">
                                            <?php echo gen_options($types_option, $types) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="orders" search_ref data-bs-toggle="tooltip"
                                                title="啟用狀態">
                                            <?php echo gen_options($orders_option, $orders) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <input type="text" class="form-control" name="keyword"
                                               placeholder="請輸入管理員名稱"
                                               value="<?php echo $keyword; ?>" search_ref>
                                    </div>
                                    <div class="col-md-12 mt-2 row">
                                        <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                            <br/><span class="text-sm text-muted">排序：<span class="text-sm text-primary">停用|啟用&#8594;建立時間新至舊</span></span>
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
                                        <th width="10%">名稱</th>
                                        <th width="10%">帳號</th>
                                        <th>權限</th>
                                        <th width="15%">備註</th>
                                        <th width="5%">建立時間<br/>
                                            <span class="text-sm text-muted font-weight-normal">最後登入時間</span></th>
                                        <th width="10%">
                                            啟用<br/>
                                            <span class="text-sm text-muted font-weight-normal">-1停用/0~99順位排序</span>
                                        </th>
                                        <th width="15%">功能</th>
                                    </tr>
                                    </thead>
                                    <tbody class="text-center">
                                    <?php
                                    if ($total == 0) {
                                        echo '<tr>';
                                        echo '<td colspan="8">查無資料</td>';
                                        echo '</tr>';
                                    }

                                    foreach ($result_arr as $key => $value) {
                                        $arr = json_decode($value["permission"], true);

                                        echo '<tr>';

                                        if ($value["account"] !== 'admin') {
                                            echo '<td>';
                                            echo '<div class="icheck-primary d-inline">';
                                            echo '<input type="checkbox" id="' . $value['admin_id'] . '" name="box_list" value="' . $value['admin_id'] . '">';
                                            echo '<label for="' . $value['admin_id'] . '">';
                                            echo '</label>';
                                            echo '</div>';
                                            echo '</td>';
                                        } else {
                                            echo "<td></td>";
                                        }

                                        echo '<td>' . $value["nickname"] . '</td>';
                                        echo '<td>' . $value["account"] . '</td>';
                                        echo '<td class="text-wrap">' . implode("、", array_keys($arr)) . '</td>';

                                        echo '<td>' . str_remove($value["note"], 15) . '</td>';
                                        echo '<td>';
                                        echo '<span class="text-sm">' . $value["pub_date"] . '</span><br/>';
                                        echo '<hr class="m-0">';
                                        echo '<span class="text-sm text-secondary">' . $value["last_date"] . '</span><br/>';
                                        echo '</td>';

                                        if ($value["account"] !== 'admin') {
                                            echo '<td>';
                                            echo '<div class="input-group">';
                                            echo '<input class="form-control" type="number" min="-1" max="99" name="orders" value="' . $value["orders"] . '">';
                                            echo '<div class="input-group-append">';
                                            echo '<button class="btn btn-outline-secondary" type="button" name="save">儲存</button>';
                                            echo '</div>';
                                            echo '</div>';
                                            echo '</td>';
                                            echo '<td>';

                                            echo '<a href="admin_mang.php?model=update&admin_id=' . $value["admin_id"] . '" class="mx-1 btn btn-sm btn-outline-primary">查看</a>';
                                            echo '</td>';
                                        } else {
                                            echo '<td><input readonly class="form-control" type="number" min="-1" max="99" name="orders" value="' . $value["orders"] . '"></td>';
                                            echo "<td></td>";
                                        }

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
        <footer class="main-footer text-sm">
            <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
            All rights reserved.
        </footer>
    </div><!-- /.container-fluid -->
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- ./wrapper -->

<?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>