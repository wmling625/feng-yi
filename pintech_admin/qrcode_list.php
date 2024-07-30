<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$keyword = params_security($_GET["keyword"]);
@$orders = params_security($_GET["orders"]);
@$send = params_security($_GET["send"]);
@$member_id = params_security($_GET["member_id"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);

if (empty($min_date)) {
    $min_date = getNewDate(date("Y-m-d"), -365);
}
if (empty($max_date)) {
    $max_date = date("Y-m-d");
}
if (empty($date_type)) {
    $date_type = "last_date";
}

$url_param = add_pararm($_SERVER['PHP_SELF'], array("date_type" => $date_type, "min_date" => $min_date, "max_date" => $max_date, "types" => $types, "keyword" => $keyword, "orders" => $orders, "send" => $send, "member_id" => $member_id, "limit" => $limit));
//$search_orders = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");
$search_orders = array("全部綁定狀態" => "", "未綁定／註銷 " => "-1", "已綁定" => "1", "停用" => "-2"); // 家屬
$search_send = array("全部發送狀態" => "", "未發送" => "-1", "已發送" => "1");
$search_date_type = array("建立時間" => "pub_date", "更新時間" => "last_date");

// 查詢會員，已便發送qrcode
$member_arr = array();
$query = "SELECT qr_type_id, title, 1 AS 'qrcode' FROM `qr_type` WHERE orders >=0 ORDER BY orders ASC, pub_date DESC; ";
$query .= "SELECT *, 1 AS 'member' FROM `member` WHERE orders >= 1 ORDER BY pub_date DESC; ";
$search_types = array("全部標籤" => "");
if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member'])) {
                    $member_arr[] = $row;
                }
                if (isset($row['qrcode'])) {
                    $search_types[$row["title"]] = $row["qr_type_id"];
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
$showrow = !empty($limit) ? $limit : 50;
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
    array_push($filter_sql_arr, "(B.qr_type_id = '" . $types . "')");
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "((A.title LIKE '%" . $keyword . "%') OR (A.introd LIKE '%" . $keyword . "%') OR (A.contents LIKE '%" . $keyword . "%') OR (A.code LIKE '%" . $keyword . "%') OR (C.title LIKE '%" . $keyword . "%'))");
}

if (!empty($orders)) {
    if ($orders == -1) {
        array_push($filter_sql_arr, "(A.orders = -1)");
    } else if ($orders == 1) {
        array_push($filter_sql_arr, "(A.orders >= 0)");
    } else if ($orders == -2) {
        array_push($filter_sql_arr, "(A.orders = -2)");
    }
}

if (!empty($send)) {
    array_push($filter_sql_arr, "(A.is_send = '" . $send . "')");
}

if (!empty($member_id)) {
    array_push($filter_sql_arr, "(A.member_id = '" . $member_id . "')");
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$result_arr = array();
$query = "SELECT A.*, B.orders AS qr_type_orders, B.title AS types_option, C.title AS member_name, C.account FROM qrcode AS A 
            LEFT JOIN qr_type AS B ON A.qr_type_id = B.qr_type_id 
            LEFT JOIN member AS C ON A.member_id = C.member_id 
            WHERE " . $filter_sql_str . " ORDER BY A.is_send ASC, A.orders ASC, A." . $date_type . " DESC";
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
        echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM qrcode WHERE find_in_set(qrcode_id, '?1') >0") . "'/>";
        echo "<input type='hidden' name='orders_sql' value='" . aes_encrypt("UPDATE qrcode SET orders = '?1', last_date = NOW() WHERE find_in_set(qrcode_id, '?2') >0") . "'/>";
        echo "<input type='hidden' name='orders_cancel_sql' value='" . aes_encrypt("UPDATE qrcode SET orders = '?1', title='', introd = '', contents = '', last_date = NOW() WHERE find_in_set(qrcode_id, '?2') >0") . "'/>";
        echo "<input type='hidden' name='excel_sql' value='" . aes_encrypt("SELECT A.*, B.title AS types_option, C.title AS member_name, C.account FROM qrcode AS A 
            LEFT JOIN qr_type AS B ON A.qr_type_id = B.qr_type_id 
            LEFT JOIN member AS C ON A.member_id = C.member_id 
            WHERE " . $filter_sql_str . " ORDER BY A.is_send ASC, A.orders ASC, A.pub_date DESC LIMIT 0,2000") . "' sp='sp_excel_qrcode.php' />";
        echo "<input type='hidden' name='zip_sql' value='" . aes_encrypt("SELECT A.file0 FROM qrcode AS A 
            LEFT JOIN qr_type AS B ON A.qr_type_id = B.qr_type_id 
            LEFT JOIN member AS C ON A.member_id = C.member_id 
            WHERE " . $filter_sql_str . " ORDER BY A.is_send ASC, A.orders ASC, A.pub_date DESC LIMIT 0,2000") . "' sp='sp_zip_qrcode.php' />";
        echo "<input type='hidden' name='send_sql' value='" . aes_encrypt("UPDATE qrcode SET member_id = '?1', is_send = 1, last_date = NOW() WHERE find_in_set(qrcode_id, '?2') >0 AND member_id = ''") . "'/>";
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
                <img src="../dist/img/AdminLTELogo.png" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
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
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-bs-toggle="tooltip" title="發送QRCode" id="sendToMember">
                                            發送
                                        </button>
                                        <a href="qrcode_mang.php?model=add" type="button" class="btn btn-sm btn-success">批次新增</a>
                                        <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="根據搜尋結果匯出（至多2000筆）" name="excel_button">
                                            匯出Excel
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="tooltip" title="根據搜尋結果下載（至多2000筆）" name="zip_button">
                                            下載QRCode圖片
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 input-group mt-2">
                                            <div class="input-group-prepend">
                                                <select class="form-control" name="date_type" data-bs-toggle="tooltip" title="篩選日期" search_ref>
                                                    <?php echo gen_options($search_date_type, $date_type) ?>
                                                </select>
                                            </div>
                                            <input type="date" class="form-control" name="min_date" data-bs-toggle="tooltip" title="<?php echo array_search($date_type, $search_date_type); ?>（預設365天前）" value="<?php echo $min_date; ?>" search_ref>
                                            <span class="input-group-text">至</span>
                                            <input type="date" class="form-control" name="max_date" data-bs-toggle="tooltip" title="<?php echo array_search($date_type, $search_date_type); ?>（預設今日）" value="<?php echo $max_date; ?>" search_ref>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <select class="form-control" name="send" data-bs-toggle="tooltip" title="發送狀態" search_ref>
                                                <?php echo gen_options($search_send, $send) ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <select class="form-control" name="orders" data-bs-toggle="tooltip" title="綁定狀態" search_ref>
                                                <?php echo gen_options($search_orders, $orders) ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <select class="form-control" name="types" data-bs-toggle="tooltip" title="標籤" search_ref>
                                                <?php echo gen_options($search_types, $types) ?>
                                            </select>
                                        </div>
                                        <input type="hidden" value="<?php echo $member_id; ?>" name="member_id" search_ref>
                                        <div class="col-md-6 col-sm-6 mt-2">
                                            <input type="text" class="form-control" data-bs-toggle="tooltip" title="QRCode名稱、會員名稱、綁定手機、暱稱、姓名" name="keyword" placeholder="請輸入QRCode名稱、會員名稱、綁定手機、暱稱、姓名" value="<?php echo $keyword; ?>" search_ref>
                                        </div>
                                        <div class="col-md-12 mt-2 row">
                                            <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                                <br /><span class="text-sm text-muted">排序：<span class="text-sm text-primary">未發送|已發送&#8594;停用|未綁定|已綁定&#8594;<?php echo array_search($date_type, $search_date_type); ?>新至舊</span></span>
                                            </div>
                                            <div class="col-md-6 col-sm-12 float-right d-flex flex-wrap align-items-end justify-content-end p-0">
                                                <?php echo $page->myde_showRow(); ?>
                                                <button type="button" name="search_button" class="btn btn-sm btn-outline-danger mx-1">搜尋
                                                </button>
                                                <button type="button" name="clear_filter" class="btn btn-sm btn-outline-info mx-1">清除條件
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
                                                <th width="10%">標籤群組<br />
                                                    <span class="text-sm text-muted font-weight-normal">QR名稱</span>
                                                </th>
                                                <!--                                        <th width="10%">標籤群組</th>-->
                                                <th width="10%">會員資訊</th>
                                                <th width="10%">綁定資訊</th>
                                                <th>姓名</th><!--家屬-->
                                                <th width="10%">發送狀態</th>
                                                <th width="20%">
                                                    綁定狀態<br />
                                                    <!-- <span class="text-sm text-muted font-weight-normal">-1停用/0~99順位排序</span>-->
                                                </th>
                                                <th width="5%">建立時間<br />
                                                    <span class="text-sm text-muted font-weight-normal">更新時間</span>
                                                </th>
                                                <th width="15%">功能</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <?php
                                            if ($total == 0) {
                                                echo '<tr>';
                                                echo '<td colspan="11">查無資料</td>';
                                                echo '</tr>';
                                            } else {
                                                foreach ($result_arr as $key => $value) {
                                                    $options = "";
                                                    foreach ($search_orders as $k => $v) {
                                                        if ($v !== "") {
                                                            if ($value['orders'] == $v) {
                                                                $options .= '<option value="' . $v . '" selected>' . $k . '</option>';
                                                            } else {
                                                                $options .= '<option value="' . $v . '">' . $k . '</option>';
                                                            }
                                                        }
                                                    }

                                                    $photo_small1 = ($value['file0'] == "") ? "" : $value['file0'];
                                                    //                                            $link = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . "/code_redirect.php?qrcode_id=" . aes_encrypt($value['qrcode_id']);

                                                    $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_redirect.php?qrcode_id=" . aes_encrypt($value['qrcode_id']));

                                                    // if (is_file("../uploads/qrcode/" . $photo_small1)) {
                                                    //     $qrcode = "../uploads/qrcode/" . $photo_small1;
                                                    // } else {
                                                    //     $qrcode = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($link) . "&choe=UTF-8";
                                                    // }

                                                    $qrcode = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($link) . "&choe=UTF-8";


                                                    echo '<tr>';

                                                    echo '<td>';
                                                    echo '<div class="icheck-primary d-inline">';
                                                    echo '<input type="checkbox" id="' . $value['qrcode_id'] . '" name="box_list" value="' . $value['qrcode_id'] . '">';
                                                    echo '<label for="' . $value['qrcode_id'] . '">';
                                                    echo '</label>';
                                                    echo '</div>';
                                                    echo '</td>';

                                                    echo '<td>';
                                                    echo '<a data-fancybox="qrcode" data-src="' . $qrcode . '" data-caption="' . $value["file0"] . '" href="javascript.void(0)">';
                                                    echo '<img height="50" src="' . $qrcode . '">';
                                                    echo '</a>';
                                                    echo '</td>';

                                                    echo '<td>';
                                                    if ($value['qr_type_orders'] !== "-1") {
                                                        echo $value["types_option"];
                                                    } else {
                                                        echo '<span class="text-danger" data-bs-toggle="tooltip" title="標籤已停用"><i class="fa-solid fa-triangle-exclamation"></i> </span>' . $value["types_option"];
                                                    }
                                                    echo '<br/><a href="' . $link . '" target="_blank" class="text-sm text-muted">' . $value["code"] . '</a>';
                                                    echo '</td>';
                                                    //                                            echo '<td>';
                                                    //
                                                    //                                            echo '</td>';
                                                    echo '<td>' . $value["member_name"] . '<br/><span class="text-sm text-muted">' . $value["account"] . '</span></td>';
                                                    echo '<td>' . $value["introd"] . '<br/><span class="text-sm text-muted">' . $value["title"] . '</span></td>';
                                                    echo '<td>' . $value["contents"] . '</td>';

                                                    echo '<td>';
                                                    if ($value["is_send"] == "1") {
                                                        echo "<span class='text-success'>已發送</span>";
                                                    } else {
                                                        echo "<span class='text-danger'>未發送</span>";
                                                    }
                                                    echo '</td>';

                                                    echo '<td>';
                                                    echo '<div class="input-group">';
                                                    echo '<select class="form-control" name="orders" defaults="' . $value['orders'] . '">';
                                                    echo $options;
                                                    echo '</select>';
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
                                                    echo '<a href="qrcode_mang.php?model=update&qrcode_id=' . $value["qrcode_id"] . '" class="mx-1 btn btn-sm btn-outline-primary">查看</a>';
                                                    // echo '<a href="place_time_list.php?keyword=' . $value["qrcode_id"] . '" class="mx-1 btn btn-sm btn-outline-dark">場次</a>';
                                                    echo '</td>';

                                                    echo '</tr>';
                                                }
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

            <!-- Modal -->
            <div class="modal fade" id="sendToMemberModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">發送 QRCode</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <select req data-title="會員" name="memberId" class="form-control select2bs4" style="width: 100%;">
                                <option value="">請選擇欲發送的會員</option>
                                <?php
                                foreach ($member_arr as $value) {
                                    echo "<option value='" . $value['member_id'] . "'>" . $value['title'] . "（" . $value['account'] . "）</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                            <button type="button" class="btn btn-primary" id="sendToMemberConfirm">送出</button>
                        </div>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->

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