<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$member_id = params_security($_GET["member_id"]);
@$profile = params_security($_GET["profile"]);

$result_arr = array();
$qr_type_arr = array();
$box_arr = array("請選擇性別" => "", "男" => "男", "女" => "女");

$query = "SELECT *, 1 AS 'member_data' FROM member WHERE member_id = '" . $member_id . "'; ";
$query .= "SELECT *, 1 AS 'qr_type_big_data' FROM qr_type_big WHERE orders>=1 ORDER BY orders, pub_date DESC; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['member_data'])) {
                    $result_arr[] = $row;
                }
                if (isset($row['qr_type_big_data'])) {
                    $qr_type_arr[] = $row;
                }
            }
            $result->close();
        }
        if ($mysqli->more_results()) {
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

$query_big = "SELECT *  FROM qrcode_big WHERE member_id = '" . $member_id . "'; ";
$bigId_arr = array();
if ($mysqli->multi_query($query_big)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                $bigId_arr[] = $row;
            }
            $result->close();
        }
        if ($mysqli->more_results()) {
        }
    } while ($mysqli->more_results() && $mysqli->next_result());
}

//$image_arr = array("file0" => "個人頭像");

?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>網站管理系統</title>

    <!--    <link rel="shortcut icon" href="https://www.yicfff.tw/booking/assets/img/favicon.ico" type="image/x-icon"/>-->
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../dist/plugins/fontawesome-free-6.1.1-web/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="../dist/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="../dist/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- JQVMap -->
    <link rel="stylesheet" href="../dist/plugins/jqvmap/jqvmap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../dist/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="../dist/plugins/daterangepicker/daterangepicker.css">
    <!-- summernote -->
    <link rel="stylesheet" href="../dist/plugins/summernote/summernote-bs4.min.css">

    <link rel="stylesheet" href="../dist/css/custom.css?t=<?php echo time(); ?>">
</head>

<body class="sidebar-mini layout-footer-fixed layout-navbar-fixed layout-fixed">

    <div class="wrapper">
        <?php
        $uuid = gen_uuid();
        $title = $result_arr[0]['account'];
        $intro = $result_arr[0]['nickname'];
        echo "<input type='hidden' name='bind_sql' value='" . aes_encrypt("INSERT INTO `qrcode_big`(`qrcode_big_id`, `qr_type_big_id`, `member_id`, `title`, `intro`, `contents`, `pub_date`, `last_date`, `orders`) VALUES ('" . $uuid . "', '{selected_value}', '" . $result_arr[0]["member_id"] . "', '" . $title . "', '" . $intro . "', '', NOW(), NOW(), 1)") . "' sp='sp_command_bind.php' />";
        ?>
        
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
                            <form method="post" id="form" enctype="multipart/form-data"
                                action="<?php echo str_replace("_mang.php", "_mang_end.php", $_SERVER['PHP_SELF']) ?>">
                                <div class="card">
                                    <div class="card-body table-responsive-sm">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <?php
                                                if ($model == "update") {
                                                    echo '<tr>';
                                                    echo '<th width="20%">建立時間</th>';
                                                    echo '<td>' . (isset($result_arr[0]["pub_date"]) ? $result_arr[0]["pub_date"] : "-") . '</td>';
                                                    echo '</tr>';
                                                    echo '<tr>';
                                                    echo '<th width="20%">更新時間</th>';
                                                    echo '<td>' . (isset($result_arr[0]["last_date"]) ? $result_arr[0]["last_date"] : "-") . '</td>';
                                                    echo '</tr>';
                                                }
                                                ?>
                                                <tr>
                                                    <th width="25%">LINE暱稱<span class="required"></span></th>
                                                    <td>
                                                        <?php echo isset($result_arr[0]["title"]) ? $result_arr[0]["title"] : "" ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="25%">帳號(手機)<span class="required"></span></th>
                                                    <td>
                                                        <?php echo isset($result_arr[0]["account"]) ? $result_arr[0]["account"] : "" ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="25%">姓名<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="姓名" class="form-control" type="nickname"
                                                            name="nickname" placeholder="請輸入姓名"
                                                            value="<?php echo isset($result_arr[0]["nickname"]) ? $result_arr[0]["nickname"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="25%">性別<span class="required">*</span></th>
                                                    <td>
                                                        <select req data-title="性別" name="types_option"
                                                            class="form-control col-md-6"
                                                            defaults="<?php echo isset($result_arr[0]["types_option"]) ? $result_arr[0]["types_option"] : ""; ?>">
                                                            <?php
                                                            foreach ($box_arr as $key => $value) {
                                                                if ($result_arr[0]["types_option"] === $value) {
                                                                    echo "<option value='" . $value . "' selected>" . $key . "</option>";
                                                                } else {
                                                                    echo "<option value='" . $value . "'>" . $key . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="25%">活動區域<span class="required">*</span></th>
                                                    <td>
                                                        <div class="input-group">
                                                            <select req data-title="活動區域-縣市" id="city"
                                                                name="city"
                                                                class="col-md-3 form-control"
                                                                defaults="<?php echo isset($result_arr[0]["city"]) ? $result_arr[0]["city"] : ""; ?>"
                                                                title="請選擇一個縣市">
                                                                <option selected disabled>請選擇縣市</option>
                                                            </select>
                                                            <select req data-title="活動區域-鄉鎮市區" id="region"
                                                                name="region"
                                                                class="col-md-3 form-control"
                                                                defaults="<?php echo isset($result_arr[0]["region"]) ? $result_arr[0]["region"] : ""; ?>"
                                                                title="請選擇一個鄉鎮市區">
                                                                <option selected disabled>請選擇鄉鎮市區</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="25%">綁定單位<span class="required"></span></th>
                                                    <td>
                                                        <?php
                                                        if (!empty($bigId_arr)) {
                                                            foreach ($bigId_arr as $key => $value) {
                                                                $query = "SELECT qr_type_big.title AS typeTitle, qrcode_big.qrcode_big_id AS bigId FROM `qrcode_big` LEFT JOIN `qr_type_big` ON qrcode_big.qr_type_big_id = qr_type_big.qr_type_big_id WHERE `member_id` = '" . $value['member_id'] . "'";
                                                                $type_arr = array();

                                                                if ($result = $mysqli->query($query)) {
                                                                    $type_arr = $result->fetch_all(MYSQLI_ASSOC); // ✅ Fetch all rows at once
                                                                    mysqli_free_result($result);
                                                                }
                                                            }

                                                            $totalItems = count($type_arr); // Count total elements
                                                            $currentIndex = 0; // Track current position
                                                            foreach ($type_arr as $key => $type) {
                                                                $currentIndex++; // Increment counter

                                                                echo (!empty($type['typeTitle'])) ? $type['typeTitle'] : '<i class="fa-solid fa-x"></i>';

                                                                // Add a comma only if it's not the last item
                                                                if ($currentIndex < $totalItems) {
                                                                    echo ', ';
                                                                }
                                                            }
                                                            // echo (!empty($value["big_title"])) ? $value["big_title"] : '<i class="fa-solid fa-x"></i>';

                                                            // $qrcode_big_id = $bigId_arr[0]['qrcode_big_id'];
                                                            // // echo "<script>document.location.href='big_my_info.php?qrcode_big_id=" . aes_encrypt($qrcode_big_id) . "&profile=" . $profile . "'</script>";
                                                            // $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("big_info.php?qrcode_big_id=" . aes_encrypt($qrcode_big_id)) . "&qrcode_id=" . $qrcode_id;

                                                            // // if (is_file("uploads/big/" . $photo_small1)) {
                                                            // //     $qrcode = "uploads/big/" . $photo_small1;
                                                            // // } else {
                                                            // //     $qrcode = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($link) . "&choe=UTF-8";
                                                            // // }
                                                            // $qrcode = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=" . urlencode($link) . "&choe=UTF-8";
                                                            // echo '<img src="' . $qrcode . '" alt="">';
                                                        } else { ?>
                                                            <div class="d-flex">
                                                                <select data-title="綁定單位" name="qr_type_big_bind"
                                                                    class="form-control col-md-6"
                                                                    defaults="<?php echo isset($result_arr[0]["qr_type_big_id"]) ? $result_arr[0]["qr_type_big_id"] : ""; ?>">
                                                                    <option value="">請選擇綁定單位</option>
                                                                    <?php
                                                                    foreach ($qr_type_arr as $value) {
                                                                        if ($result_arr[0]["qr_type_big_id"] === $value["qr_type_big_id"]) {
                                                                            echo "<option value='" . $value["qr_type_big_id"] . "' selected>" . $value["title"] . "</option>";
                                                                        } else {
                                                                            echo "<option value='" . $value["qr_type_big_id"] . "'>" . $value["title"] . "</option>";
                                                                        }
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <button type="button" class="btn btn-primary ml-4" id="qr_type_bind">綁定</button>
                                                            </div>
                                                        <?php } ?>
                                                    </td>

                                                </tr>
                                                <tr>
                                                    <th width="25%">單位管理權限<span class="required"></span></th>
                                                    <td>
                                                        <select data-title="單位管理權限" name="qr_type_big_id"
                                                            class="form-control col-md-6"
                                                            defaults="<?php echo isset($result_arr[0]["qr_type_big_id"]) ? $result_arr[0]["qr_type_big_id"] : ""; ?>">
                                                            <option value="">請選擇可管理的單位</option>
                                                            <?php
                                                            foreach ($qr_type_arr as $value) {
                                                                if ($result_arr[0]["qr_type_big_id"] === $value["qr_type_big_id"]) {
                                                                    echo "<option value='" . $value["qr_type_big_id"] . "' selected>" . $value["title"] . "</option>";
                                                                } else {
                                                                    echo "<option value='" . $value["qr_type_big_id"] . "'>" . $value["title"] . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success" name="post">儲存</button>
                                        <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
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