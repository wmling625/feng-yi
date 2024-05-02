<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$qr = params_security($_GET["qr"]);
@$qr_big = params_security($_GET["qr_big"]);
@$keyword = params_security($_GET["keyword"]);
@$orders = params_security($_GET["orders"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);

if (empty($min_date)) {
    $min_date = getNewDate(date("Y-m-d"), -365); //注意!
}
if (empty($max_date)) {
    $max_date = date("Y-m-d");
}
if (empty($date_type)) {
    $date_type = "last_date";
}

$url_param = add_pararm($_SERVER['PHP_SELF'], array("date_type" => $date_type, "min_date" => $min_date, "max_date" => $max_date, "types" => $types, "keyword" => $keyword, "orders" => $orders, "qr" => $qr, "qr_big" => $qr_big, "limit" => $limit));
$search_orders = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");
$search_types = array("全部廣告類型" => "", "影片" => "影片", "圖文連結" => "圖文連結", "單一圖片" => "單一圖片");
$search_date_type = array("建立時間" => "pub_date", "更新時間" => "last_date");

$query = "SELECT qr_type_id, title, 1 AS qr_type FROM `qr_type` WHERE orders >=0 ORDER BY orders ASC, pub_date DESC;";
$query .= "SELECT qr_type_big_id, title, 1 AS qr_type_big FROM `qr_type_big` WHERE orders >=0 ORDER BY orders ASC, pub_date DESC;";
$search_qr = array("全部標籤群組" => "");
$search_qr_big = array("全部單位" => "");
if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['qr_type'])) {
                    $search_qr[$row["title"]] = $row["qr_type_id"];
                }
                if (isset($row['qr_type_big'])) {
                    $search_qr_big[$row["title"]] = $row["qr_type_big_id"];
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {

        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}


// 頁碼相關設定
$total = 0;
$showrow = !empty($limit) ? $limit : 25;
$curpage = empty($_GET['page']) ? 1 : params_security($_GET['page'], "int");
$url_page = "&page={page}";

$url = $url_param . $url_page;

$filter_sql_arr = array();
$filter_sql_str = "";

if (!empty($min_date)) {
    array_push($filter_sql_arr, "(date(" . $date_type . ") >= '" . $min_date . "') ");
}

if (!empty($max_date)) {
    array_push($filter_sql_arr, "(date(" . $date_type . ") <= '" . $max_date . "') ");
}

if (!empty($types)) {
    array_push($filter_sql_arr, "(types_option LIKE '%" . $types . "%')");
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "((title LIKE '%" . $keyword . "%') OR (introd LIKE '%" . $keyword . "%') OR (sponsor LIKE '%" . $keyword . "%'))");
}

if (!empty($orders)) {
    if ($orders == -1) {
        array_push($filter_sql_arr, "(orders = -1)");
    } else if ($orders == 1) {
        array_push($filter_sql_arr, "(orders >= 0)");
    }
}

if (!empty($qr)) {
    array_push($filter_sql_arr, "(types_box LIKE '%" . $qr . "%')");
}

if (!empty($qr_big)) {
    array_push($filter_sql_arr, "(types_box2 LIKE '%" . $qr_big . "%')");
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$result_arr = array();
$query = "SELECT * FROM advertisement WHERE " . $filter_sql_str . " ORDER BY orders ASC, " . $date_type . " DESC";

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
    echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM advertisement WHERE find_in_set(advertisement_id, '?1') >0") . "'/>";
    echo "<input type='hidden' name='orders_sql' value='" . aes_encrypt("UPDATE advertisement SET orders = '?1' WHERE find_in_set(advertisement_id, '?2') >0") . "'/>";
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
                            <div class="card-header">
                                <div class="card-tools">
                                    <a href="advertisement_mang.php?model=add" type="button"
                                       class="btn btn-sm btn-success">新增</a>
                                    <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>
                                </div>
                            </div>
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
                                               title="<?php echo array_search($date_type, $search_date_type); ?>（預設365天前）"
                                               value="<?php echo $min_date; ?>" search_ref>
                                        <span class="input-group-text">至</span>
                                        <input type="date" class="form-control" name="max_date" data-bs-toggle="tooltip"
                                               title="<?php echo array_search($date_type, $search_date_type); ?>（預設今日）"
                                               value="<?php echo $max_date; ?>" search_ref>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="orders" data-bs-toggle="tooltip" title="啟用狀態"
                                                search_ref>
                                            <?php echo gen_options($search_orders, $orders) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="types" data-bs-toggle="tooltip" title="廣告類型"
                                                search_ref>
                                            <?php echo gen_options($search_types, $types) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="qr" data-bs-toggle="tooltip" title="標籤"
                                                search_ref>
                                            <?php echo gen_options($search_qr, $qr) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <select class="form-control" name="qr_big" data-bs-toggle="tooltip" title="單位"
                                                search_ref>
                                            <?php echo gen_options($search_qr_big, $qr_big) ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6 mt-2">
                                        <input type="text" class="form-control" data-bs-toggle="tooltip"
                                               title="標題、連結"
                                               name="keyword"
                                               placeholder="標題、連結"
                                               value="<?php echo $keyword; ?>" search_ref>
                                    </div>
                                    <div class="col-md-12 mt-2 row">
                                        <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                            <br/><span class="text-sm text-danger">※「影片」類廣告建議搭配「單一圖片」廣告，以達到最佳瀏覽效果</span>
                                            <br/><span class="text-sm text-muted">排序：<span class="text-sm text-primary">停用|啟用&#8594;<?php echo array_search($date_type, $search_date_type); ?>新至舊</span></span>
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
                                        <th width="10%">圖片</th>
                                        <th width="15%">標題 (廣告類別）<br/><span
                                                    class="text-sm text-muted font-weight-normal">連結</span></th>
                                        <th>廣告群組</th>
                                        <th>單位</th>
                                        <th width="15%">投放區間</th>
                                        <th width="10%">
                                            廣告點擊率<br/>
                                            <span class="text-sm text-muted font-weight-normal">被點擊數/顯示總次數</span>
                                        </th>
                                        <th width="10%">
                                            啟用<br/>
                                            <span class="text-sm text-muted font-weight-normal">-1停用/0~99順位排序</span>
                                        </th>
                                        <th width="5%">建立時間<br/>
                                            <span class="text-sm text-muted font-weight-normal">更新時間</span></th>
                                        <th width="10%">功能</th>
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
                                        $photo_small1 = ($value['file0'] == "") ? "nophoto.png" : $value['file0'];
                                        $color = array("影片" => "bg-success", "圖文連結" => "bg-primary", "單一圖片" => "bg-warning");
                                        $arr = json_decode($value["types_box"], true);
                                        $arr2 = (!empty($value["types_box2"])) ? json_decode($value["types_box2"], true) : array();
                                        /*
                                         * 需判斷廣告群組內的所有標籤是否啟用，可對應上方　$search_qr　取得的所有「已啟用」的標籤
                                         * 如果已啟用，才會顯示在列表頁上
                                         */

                                        $types_box = [];
                                        foreach ($arr as $name => $item) {
                                            if (in_array($item, $search_qr)) {
                                                $types_box[$name] = $item;
                                            }
                                        }
                                        $types_box2 = [];
                                        foreach ($arr2 as $name => $item) {
                                            if (in_array($item, $search_qr_big)) {
                                                $types_box2[$name] = $item;
                                            }
                                        }


                                        echo '<tr>';

                                        echo '<td>';
                                        echo '<div class="icheck-primary d-inline">';
                                        echo '<input type="checkbox" id="' . $value['advertisement_id'] . '" name="box_list" value="' . $value['advertisement_id'] . '">';
                                        echo '<label for="' . $value['advertisement_id'] . '">';
                                        echo '</label>';
                                        echo '</div>';
                                        echo '</td>';

                                        echo '<td>';
                                        echo '<a data-fancybox data-src="../uploads/others/' . $photo_small1 . '" data-caption="' . $value["title"] . '" href="javascript.void(0)">';
                                        echo '<img height="50" src="../uploads/others/' . $photo_small1 . '">';
                                        echo '</a>';
                                        echo '</td>';

                                        echo '<td class="text-left">' . $value["title"];
                                        echo '<span class="badge ' . $color[$value["types_option"]] . ' mx-2">' . $value["types_option"] . '</span>';
                                        if ($value["introd"] !== "") {
                                            echo '<br/><a class="text-sm text-muted" href="' . $value["introd"] . '" target="_blank">' . str_remove($value["introd"], 20) . '</a>';
                                            // <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        }
                                        echo '</td>';

                                        echo '<td class="text-sm text-wrap">' . implode("、", array_keys($types_box)) . '</td>';
                                        echo '<td class="text-sm text-wrap">' . implode("、", array_keys($types_box2)) . '</td>';

                                        echo '<td>';
                                        echo '<span class="text-primary text-sm">' . (strtotime($value["start_date"]) && $value["start_date"] !== "0000-00-00 00:00:00" ? $value["start_date"] : "-") . '</span><br/>';
                                        echo '<hr class="m-0"><span class="text-danger text-sm">' . (strtotime($value["end_date"]) && $value["end_date"] !== "0000-00-00 00:00:00" ? $value["end_date"] : "-") . '</span>';
                                        echo '</td>';

                                        echo '<td>' . cal_percentage($value["count"], $value['display']) . '%<br/>';
                                        echo '<span class="text-sm text-muted font-weight-normal">(' . $value["count"] . '/' . $value['display'] . ')</span>';
                                        echo '</td>';

                                        echo '<td>';
                                        echo '<div class="input-group">';
                                        echo '<input class="form-control" type="number" min="-1" max="99" name="orders" value="' . $value["orders"] . '">';
                                        echo '<div class="input-group-append">';
                                        echo '<button class="btn btn-outline-secondary" type="button" name="save">儲存</button>';
                                        echo '</div>';
                                        echo '</div>';
                                        echo '</td>';

                                        echo '<td>';
                                        echo '<span class="text-sm">' . $value["pub_date"] . '</span><br/>';
                                        echo '<hr class="m-0">';
                                        echo '<span class="text-sm text-secondary">' . $value["last_date"] . '</span><br/>';
                                        echo '</td>';

                                        echo '<td>';
                                        echo '<a href="advertisement_mang.php?model=update&advertisement_id=' . $value["advertisement_id"] . '" class="mx-1 btn btn-sm btn-outline-primary">查看</a>';
                                        // echo '<a href="place_time_list.php?keyword=' . $value["advertisement_id"] . '" class="mx-1 btn btn-sm btn-outline-dark">場次</a>';
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