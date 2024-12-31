<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$min_date = params_security($_GET["min_date"]);
@$max_date = params_security($_GET["max_date"]);
@$types = params_security($_GET["types"]);
@$items = params_security($_GET["items"]);
@$keyword = params_security($_GET["keyword"]);
@$form = params_security($_GET["form"]);
@$orders = params_security($_GET["orders"]);
@$qrbigParams = params_security($_GET["qrbigParams"]);
@$paid = params_security($_GET["paid"]);
@$limit = params_security($_GET["limit"]);
@$date_type = params_security($_GET["date_type"]);
@$date_type = params_security($_GET["date_type"]);
@$qr_type_big_id = params_security($_GET["qr_type_big_id"]);


if (empty($min_date)) {
    $min_date = getNewDate(date("Y-m-d"), -365);
}

if (!empty($types)) {
    array_push($filter_sql_arr, "(A.qr_type_big_id = '" . $types . "')");
}

if (empty($max_date)) {
    $max_date = date("Y-m-d");
}
if (empty($date_type)) {
    $date_type = "last_date";
}

$today = date("Y-m-d");

$url_param = add_pararm($_SERVER['PHP_SELF'], array("date_type" => $date_type, "min_date" => $min_date, "max_date" => $max_date, "types" => $types, "keyword" => $keyword, "orders" => $orders, "qrbigParams" => $qrbigParams, "paid" => $paid, "limit" => $limit, "items" => $items));
$search_orders = array("全部狀態" => "", "啟用" => "1", "未啟用" => "-1");
$search_items = array("全部性別" => "", "男" => "男", "女" => "女");
$search_date_type = array("建立時間" => "pub_date", "更新時間" => "last_date");
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

if (!empty($items)) {
    array_push($filter_sql_arr, "(A.types_option = '" . $items . "')");
}

if (!empty($types)) {
    array_push($filter_sql_arr, "(A.types_option4 LIKE '%" . $types . "%')");
}

if (!empty($keyword)) {
    array_push($filter_sql_arr, "((A.nickname LIKE '%" . $keyword . "%') OR (A.account LIKE '%" . $keyword . "%') OR (A.title LIKE '%" . $keyword . "%'))");
}

if (!empty($form)) {
    array_push($filter_sql_arr, "(A.form LIKE '%" . $form . "%')");
}


if (!empty($orders)) {
    if ($orders == -1) {
        array_push($filter_sql_arr, "(A.orders = -1)");
    } else if ($orders == 1) {
        array_push($filter_sql_arr, "(A.orders >= 0)");
    }
}

if (!empty($qrbigParams)) {
    array_push($filter_sql_arr, "(B.qr_type_big_id LIKE '%" . $qrbigParams . "%')");
}

if (count($filter_sql_arr) > 0) {
    $filter_sql_str = implode(" AND ", $filter_sql_arr);
} else {
    $filter_sql_str = "1";
}

$result_arr = array();

if (isset($_SESSION['admin']['qr_type_big_id'])) {
    $query = "SELECT A.*, B.title AS 'big_title' 
    FROM member A 
    LEFT JOIN qr_type_big B ON A.qr_type_big_id = B.qr_type_big_id 
    WHERE B.qr_type_big_id = '" . $mysqli->real_escape_string($_SESSION['admin']['qr_type_big_id']) . "' 
    ORDER BY A.orders ASC";
} else {
    $query = "SELECT A.*, B.title AS 'big_title' FROM member A LEFT JOIN qr_type_big B ON A.qr_type_big_id = B.qr_type_big_id WHERE " . $filter_sql_str . " ORDER BY A.orders ASC, A." . $date_type . " DESC";
}


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


$query_big = "SELECT *, 1 AS 'qr_type_big_data' FROM qr_type_big WHERE orders>=1 ORDER BY orders, pub_date DESC; ";
$result_big_arr = array();

if ($result = $mysqli->query($query_big)) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $result_big_arr[] = $row;
    }

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
        <?php
        echo "<input type='hidden' name='del_sql' value='" . aes_encrypt("DELETE FROM member WHERE find_in_set(member_id, '?1') >0") . "'/>";
        echo "<input type='hidden' name='orders_sql' value='" . aes_encrypt("UPDATE member SET orders = '?1' WHERE find_in_set(member_id, '?2') >0") . "'/>";
        echo "<input type='hidden' name='excel_sql' value='" . aes_encrypt("SELECT * FROM member ORDER BY orders ASC, pub_date DESC") . "' sp='sp_excel_member.php' />";
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
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-bs-toggle="tooltip" title="推播給該單位條碼會員" id="lineNotify">
                                            推播訊息
                                        </button>
                                        <!--                                    <a href="member_mang.php?model=add" type="button"-->
                                        <!--                                       class="btn btn-sm btn-success">新增</a>-->
                                        <button type="button" class="btn btn-sm btn-danger" name="box_del">批次刪除</button>
                                        <!--<button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="tooltip" title="會員列表" name="excel_button">
                                        匯出
                                    </button>-->
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
                                            <select class="form-control" name="orders" data-bs-toggle="tooltip" title="啟用狀態" search_ref>
                                                <?php echo gen_options($search_orders, $orders) ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <select class="form-control" name="items" data-bs-toggle="tooltip" title="性別" search_ref>
                                                <?php echo gen_options($search_items, $items) ?>
                                            </select>
                                        </div>
                                        <?php if (!isset($_SESSION['admin']['qr_type_big_id'])) { ?>
                                            <div class="col-md-3 col-sm-6 mt-2">
                                                <select data-title="單位管理權限" name="qr_type_big_id" class="form-control" defaults="<?php echo isset($result_big_arr[0]["qr_type_big_id"]) ? $result_big_arr[0]["qr_type_big_id"] : ""; ?>">
                                                    <option value="">請選擇可管理的單位</option>
                                                    <?php
                                                    foreach ($result_big_arr as $value) {
                                                        if ($result_big_arr[0]["qr_type_big_id"] === $value["qr_type_big_id"]) {
                                                            echo "<option value='" . $value["qr_type_big_id"] . "' selected>" . $value["title"] . "</option>";
                                                        } else {
                                                            echo "<option value='" . $value["qr_type_big_id"] . "'>" . $value["title"] . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <input type="text" class="form-control" name="keyword" data-bs-toggle="tooltip" title="會員姓名、Line暱稱、手機" placeholder="會員姓名、Line暱稱、手機" value="<?php echo $keyword; ?>" search_ref>
                                        </div>

                                        <div class="col-md-3 col-sm-6 mt-2">
                                            <input type="text" class="form-control" name="form" data-bs-toggle="tooltip" title="關鍵字" placeholder="會員社區關鍵字" value="<?php echo $form; ?>" search_ref>
                                        </div>

                                        <div class="col-md-12 mt-2 row">
                                            <div class="col-md-6 col-sm-12 float-left"><?php echo $page->myde_showTotal(); ?>
                                                <br /><span class="text-sm text-muted">排序：<span class="text-sm text-primary">停用|啟用小至大&#8594;<?php echo array_search($date_type, $search_date_type); ?>新至舊</span></span>
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
                                                <th width="10%">LINE暱稱</th>
                                                <th width="10%">會員姓名<br />
                                                    <span class="text-sm text-muted font-weight-normal">會員手機</span>
                                                </th>
                                                <th width="10%">會員性別<br />
                                                    <span class="text-sm text-muted font-weight-normal">活動區域</span>
                                                </th>
                                                <th width="10%">所屬單位</th>
                                                <th width="10%">擁有單位管理權限</th>
                                                <th width="10%">
                                                    啟用<br />
                                                    <span class="text-sm text-muted font-weight-normal">-1停用/0~99順位排序</span>
                                                </th>
                                                <th width="5%">建立時間<br />
                                                    <span class="text-sm text-muted font-weight-normal">更新時間</span>
                                                </th>
                                                <th width="10%">功能</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-center">
                                            <?php
                                            if ($total == 0) {
                                                echo '<tr>';
                                                echo '<td colspan="7">查無資料</td>';
                                                echo '</tr>';
                                            } else {

                                                foreach ($result_arr as $key => $value) {
                                                    $query = "SELECT *, qr_type_big.title AS typeTitle FROM `qrcode_big` LEFT JOIN `qr_type_big` ON qrcode_big.qr_type_big_id = qr_type_big.qr_type_big_id WHERE `member_id` = '" . $value['member_id'] . "'";
                                                    $type_arr = array();
                                                    if ($result = $mysqli->query($query)) {
                                                        $rows = $result->fetch_array();
                                                        $type_arr[] = $rows;
                                                        mysqli_free_result($result);
                                                    }

                                                    if (isset($type_arr[0]['typeTitle'])) {
                                                        $typeTitle = $type_arr[0]['typeTitle'];
                                                    }


                                                    echo '<tr>';
                                                    echo '<td>';
                                                    echo '<div class="icheck-primary d-inline">';
                                                    echo '<input type="checkbox" id="' . $value['member_id'] . '" name="box_list" value="' . $value['member_id'] . '">';
                                                    echo '<label for="' . $value['member_id'] . '">';
                                                    echo '</label>';
                                                    echo '</div>';
                                                    echo '</td>';

                                                    echo '<td>' . $value["title"] . '</td>';
                                                    echo '<td>' . $value["nickname"] . '<br/><span class="text-sm text-muted">' . $value["account"] . '</span></td>';
                                                    echo '<td>' . $value["types_option"] . '<br/><span class="text-sm text-muted">' . $value["city"] . $value["region"] . '</span></td>';
                                                    echo '<td>';
                                                    foreach ($type_arr as $key => $type) {
                                                        echo (!empty($type['typeTitle'])) ? $type['typeTitle'] . ',' : '<i class="fa-solid fa-x"></i>';
                                                    }
                                                    echo '</td>';
                                                    echo '<td>';
                                                    echo (!empty($value["qr_type_big_id"])) ? '<i class="fa-solid fa-check text-success"></i> ' . $value["big_title"] : '<i class="fa-solid fa-x"></i>';
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
                                                    echo '<a href="member_mang.php?model=update&member_id=' . $value["member_id"] . '" class="mx-1 btn btn-sm btn-outline-primary">查看</a>';
                                                    //                                            echo '<a href="qrcode_list.php?member_id=' . $value["member_id"] . '" class="mx-1 btn btn-sm btn-outline-dark">QRCode</a>';
                                                    echo '<button data-title="' . $value["account"] . '" data-id="' . $value["member_id"] . '" class="mx-1 btn btn-sm btn-outline-dark" name="member_qr">QRCode</button>';
                                                    echo '<button data-title="' . $value["account"] . '" data-id="' . $value["member_id"] . '" class="mx-1 btn btn-sm btn-outline-dark" name="member_qr_big">單位條碼</button>';
                                                    //                                            echo '<a href="qrcode_big_list.php?member_id=' . $value["member_id"] . '" class="mx-1 btn btn-sm btn-outline-dark">單位條碼</a>';
                                                    //                                        echo '<a href="member_a4.php?member_id=' . $value["member_id"] . '" target="_blank" class="mx-1 btn btn-sm btn-outline-dark">A4列印</a>';
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
            <div class="modal fade" id="lineNotifyModal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title">發送 QRCode</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" id="form" enctype="multipart/form-data" action="member_notify_end.php">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="message">推播內容<span class="required">*</span></label>
                                    <textarea req class="form-control" rows="10" name="message" id="message" placeholder="請輸入推播的訊息內容，僅接受純文字"></textarea>
                                </div>
                                <?php
                                $image_arr = array("file0" => "推播圖片");
                                $file_arr = array("file1" => array("推播影片", "", "3"));
                                $upload_dir = "../uploads/others/";
                                // 圖片
                                foreach ($image_arr as $key => $value) {
                                    $file = "";
                                    $default = $upload_dir . "nophoto.png";
                                    $b64_selector = "textarea[name=" . $key . "_64]";
                                    $prev_selector = "[name=" . $key . "_prev]";
                                    $func_str = "selectFileImage(this, 200, 200, '" . $b64_selector . "', '" . $prev_selector . "', 'rwd')";

                                    echo '<div class="form-group">';
                                    echo '<label for="' . $key . '">' . $value . '<span class="required"></span></label>';
                                    echo '<label class="upload_cover img-thumbnail" style="background-image: url(' . $default . ');" name="' . $key . '_prev">';
                                    echo '<input class="file" type="file" onchange="' . $func_str . '" name="' . $key . '" id="' . $key . '">';
                                    echo '</label>';
                                    echo '<textarea name="' . $key . '_64" class="hide"></textarea>';
                                    echo '</div>';
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
                                    echo '<div class="form-group">';

                                    echo '<label for="' . $key . '">' . $value[0] . '</label>';
                                    echo '<span class="required">' . $required . '</span>';
                                    echo '<span class="mx-1" data-bs-toggle="tooltip" data-bs-placement="top" title="僅接受.mp4、.webm檔">';
                                    echo '<i class="fa-regular fa-circle-question" aria-hidden="true"></i>';
                                    echo '</span>';
                                    echo '<label class="btn btn-outline-primary mb-0">';
                                    echo '<input accept="video/mp4,video/webm" style="display:none;" ' . $value[1] . ' class="filesupload" data-title="' . $value[0] . '" type="file" name="' . $key . '" value="" data-file="' . aes_encrypt($file) . '" data-id="' . $value[2] . '" />';
                                    echo '<i class="fa-solid fa-cloud-arrow-up"></i> 上傳檔案';
                                    echo '<span class="text-sm mx-2" id="filename"></span>';
                                    echo $file_str;
                                    echo $del_str;
                                    echo '</div>';
                                }
                                ?>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <input type="hidden" class="form-control" name="ids" id="ids" value="">
                                <button type="button" class="btn btn-default" data-dismiss="modal">關閉</button>
                                <button type="button" class="btn btn-primary" id="lineNotifyConfirm">送出</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.modal-content -->
                </div>
                <!-- /.modal-dialog -->
            </div>
            <!-- /.modal -->
        </div><!-- /.container-fluid -->

        <!-- /.content-wrapper -->
        <footer class="main-footer text-sm">
            <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
            All rights reserved.
        </footer>
        <!-- /.content -->
    </div>
    <!-- ./wrapper -->

    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>