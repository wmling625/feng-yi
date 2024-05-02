<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$admin_id = params_security($_GET["admin_id"]);

$result_arr = array();
$box_arr = array("個人資料" => "me_mang.php", "最新消息" => "news_list.php", "合作店家" => "place_list.php");
$query = "SELECT * FROM admin WHERE admin_id = '" . $admin_id . "';";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $result_arr[] = $rows;
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
                                            <th width="20%">名稱<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="名稱" class="form-control" name="nickname"
                                                       placeholder="請輸入管理員名稱"
                                                       value="<?php echo isset($result_arr[0]["nickname"]) ? $result_arr[0]["nickname"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">帳號<span class="required">*</span></th>
                                            <td>
                                                <input req data-title="帳號" class="form-control" name="account"
                                                       placeholder="請輸入管理員帳號"
                                                       value="<?php echo isset($result_arr[0]["account"]) ? $result_arr[0]["account"] : "" ?>">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">
                                                密碼<?php echo ($model == "add") ? '<span class="required">*</span>' : '<br/><span class="text-xs text-danger font-weight-normal">不填寫表示不修改</span>' ?></th>
                                            <td>
                                                <input <?php echo ($model == "add") ? "req" : "" ?> data-title="密碼"
                                                                                                    placeholder="請輸入密碼"
                                                                                                    type="password"
                                                                                                    class="form-control"
                                                                                                    name="password">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">
                                                密碼確認<?php echo ($model == "add") ? '<span class="required">*</span>' : '<br/><span class="text-xs text-danger font-weight-normal">不填寫表示不修改</span>' ?></th>
                                            <td>
                                                <input <?php echo ($model == "add") ? "req" : "" ?> data-title="密碼確認"
                                                                                                    placeholder="請再輸入一次密碼"
                                                                                                    type="password"
                                                                                                    class="form-control"
                                                                                                    name="password2">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">權限<br/><span class="text-xs text-danger font-weight-normal">不勾選代表權限最大</span>
                                            </th>
                                            <td>
                                                <div class="row p-2">
                                                    <?php
                                                    foreach ($menu_array as $key => $value) {
                                                        foreach ($menu_array[$key] as $key1 => $value1) {
                                                            $checked = "";
                                                            $title = $value1["title"];
                                                            $link = $value1['link'];

                                                            // 如果存在就判斷有沒有勾選
                                                            if (isset($result_arr[0]['permission'])) {
                                                                if (strpos($result_arr[0]['permission'], $link) !== false) {
                                                                    $checked = "checked";
                                                                }
                                                            }

                                                            if ($link != "init.php" && $link != "admin_list.php") {
                                                                echo '<div class="col-md-3 custom-control custom-checkbox">';
                                                                echo '<input class="custom-control-input custom-control-input-primary custom-control-input-outline" type="checkbox" id="' . $title . $link . '" value="' . $link . '" title="' . $title . '" ' . $checked . '>';
                                                                echo '<label class="custom-control-label" for="' . $title . $link . '">' . $title . '</label>';
                                                                echo '</div>';
                                                            }
                                                        }

                                                    }
                                                    ?>
                                                </div>
                                                <input type="text" class="hide" name="permission"
                                                       value="<?php echo isset($result_arr[0]["permission"]) ? htmlspecialchars($result_arr[0]["permission"]) : ""; ?>"
                                                       box_str>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th width="20%">備註<span class="required"></span></th>
                                            <td>
                                                <textarea data-title="備註" class="form-control" name="note" rows="5"
                                                          placeholder="請輸入備註"><?php echo isset($result_arr[0]["note"]) ? br2nl($result_arr[0]["note"]) : "" ?></textarea>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                                <div class="card-footer d-flex justify-content-end">
                                    <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                    <button type="submit" class="btn btn-success" name="post">儲存</button>
                                    <input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>">
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