<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$qrcode_id = params_security($_GET["qrcode_id"]);
@$qr_type_id = params_security($_GET["qr_type_id"]);

$result_arr = array();
$box_arr = array();
$box_arr1 = array("請選擇綁定狀態" => "", "未綁定" => "-1", "已綁定" => "1");
if ($model == "update") {
    $box_arr1 = array("請選擇綁定狀態" => "", "未綁定／註銷 " => "-1", "已綁定" => "1", "停用" => "-2");
}
$box_arr2 = array();
$query = "SELECT `qrcode`.*, `member`.`title` AS name, `member`.`account`, 1 AS qrcode FROM qrcode LEFT JOIN `member` ON `qrcode`.`member_id`=`member`.`member_id` WHERE qrcode_id = '" . $qrcode_id . "';";
$query .= "SELECT qr_type_id, title, 1 AS qr_type FROM qr_type WHERE orders >= 0 ORDER BY orders ASC, pub_date DESC;";
$query .= "SELECT *, 1 AS 'member' FROM `member` WHERE orders >= 1 ORDER BY pub_date DESC; ";

if ($mysqli->multi_query($query)) {
    do {
        if ($result = $mysqli->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['qrcode'])) {
                    $result_arr[] = $row;
                }
                if (isset($row['qr_type'])) {
                    $box_arr[$row["title"]] = $row["qr_type_id"];;
                }
                if (isset($row['member'])) {
                    $box_arr2[$row["title"] . '（' . $row['account'] . '）'] = $row["member_id"];;
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
                                            <th width="20%">發送狀態</th>
                                            <td>
                                                <?php
                                                if (isset($result_arr[0]["is_send"])) {
                                                    if ($result_arr[0]["is_send"] == "1") {
                                                        echo "<span class='text-success'>已發送</span>";
                                                    } else {
                                                        echo "<span class='text-danger'>未發送</span>";
                                                    }
                                                } else {
                                                    echo "<span class='text-danger'>未發送</span>";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        if (isset($result_arr[0]["is_send"])) {
                                            if ($result_arr[0]["is_send"] == "1") {
                                                echo '<tr>';
                                                echo '<th width="20%">會員資訊</th>';
                                                echo '<td>';
                                                echo isset($result_arr[0]["name"]) ? $result_arr[0]["name"] : "-";
                                                echo '<br/><span class="text-muted">';
                                                echo isset($result_arr[0]["account"]) ? $result_arr[0]["account"] : "-";
                                                echo '</span>';
                                                echo '</td>';
                                                echo '</tr>';
                                            }
                                        }
                                        ?>

                                        <tr>
                                            <th width="20%">綁定狀態<span class="required">*</span></th>
                                            <td>
                                                <select req data-title="綁定狀態" name="orders"
                                                        class="form-control"
                                                        defaults="<?php echo isset($result_arr[0]["orders"]) ? $result_arr[0]["orders"] : "-1"; ?>">
                                                    <?php
                                                    foreach ($box_arr1 as $key => $value) {
                                                        echo "<option value='" . $value . "'>" . $key . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr id="bindArea4" class="hide">
                                            <th width="20%">綁定會員<span class="required">*</span></th>
                                            <td>
                                                <select req data-title="欲綁定會員" name="member_id"
                                                        class="form-control select2bs4"
                                                        defaults="<?php echo isset($result_arr[0]["member_id"]) ? $result_arr[0]["member_id"] : ""; ?>">
                                                    <option value="">請選擇欲綁定會員</option>
                                                    <?php
                                                    foreach ($box_arr2 as $key => $value) {
                                                        $selected = "";
                                                        if (isset($result_arr[0]["member_id"]) && $result_arr[0]["member_id"] == $value) {
                                                            $selected = "selected";
                                                        }
                                                        echo "<option value='" . $value . "' " . $selected . ">" . $key . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">廣告標籤<span class="required">*</span></th>
                                            <td>
                                                <select req data-title="廣告標籤" name="qr_type_id"
                                                        class="form-control"
                                                        defaults="<?php echo isset($result_arr[0]["qr_type_id"]) ? $result_arr[0]["qr_type_id"] : $qr_type_id; ?>">
                                                    <option value="">請選擇廣告標籤</option>
                                                    <?php
                                                    foreach ($box_arr as $key => $value) {
                                                        echo "<option value='" . $value . "'>" . $key . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">QRCode名稱<span class="required">*</span>
                                                <?php
                                                if ($model == "add") {
                                                    echo '<br/><span class="text-xs text-danger font-weight-normal">系統將產生6位隨機英數於名稱後方，避免名稱重複</span>';
                                                } else {
                                                    echo '<br/><span class="text-xs text-danger font-weight-normal">請勿輸入重複名稱</span>';
                                                }
                                                ?>
                                            </th>
                                            <td>
                                                <input req data-title="QRCode名稱" class="form-control" name="code"
                                                       value="<?php echo isset($result_arr[0]["code"]) ? $result_arr[0]["code"] : "" ?>">
                                                <span class="text-sm" id=""></span>
                                            </td>
                                        </tr>
                                        <?php
                                        if ($model == "add") {
                                            echo '<tr id="numberArea" class="">';
                                            echo '<th width="20%">';
                                            echo '產生數量<span class="required">*</span><br/>';
                                            echo '<span class="text-xs text-danger font-weight-normal">最大數量2000</span>';
                                            echo '</th>';
                                            echo '<td>';
                                            echo '<input req data-title="產生數量" class="form-control" name="number" type="number" min="1" max="2000">';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        <tr id="bindArea1" class="hide">
                                            <th width="20%">綁定手機<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="綁定手機" class="form-control" name="title"
                                                       value="<?php echo isset($result_arr[0]["title"]) ? $result_arr[0]["title"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr id="bindArea2" class="hide">
                                            <th width="20%">綁定暱稱<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="綁定暱稱" class="form-control" name="introd"
                                                       value="<?php echo isset($result_arr[0]["introd"]) ? $result_arr[0]["introd"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr id="bindArea3" class="hide">
                                            <th width="20%">姓名<span class="required">*</span></th><!--家屬-->
                                            <td>
                                                <input req data-title="姓名" class="form-control text-uppercase"
                                                       name="contents"
                                                       value="<?php echo isset($result_arr[0]["contents"]) ? $result_arr[0]["contents"] : "" ?>">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="qrcode_id" value="<?php echo $qrcode_id; ?>">
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