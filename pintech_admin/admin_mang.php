<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = params_security($_GET["model"]);
@$admin_id = params_security($_GET["admin_id"]);

$result_arr = array();
$qrcode_big_id = '';
$member_id = '';
$box_arr = array("個人資料" => "me_mang.php", "最新消息" => "news_list.php", "合作店家" => "place_list.php");
$query = "SELECT * FROM admin WHERE admin_id = '" . $admin_id . "';";

if ($result = $mysqli->query($query)) {
    $rows = $result->fetch_array();
    $result_arr[] = $rows;
    mysqli_free_result($result);
}

$query_big = "SELECT *, 1 AS 'qr_type_big_data' FROM qr_type_big WHERE orders>=1 ORDER BY orders, pub_date DESC; ";
$result_big_arr = array();
if ($result = $mysqli->query($query_big)) {
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
        $result_big_arr[] = $row;
    }

    mysqli_free_result($result);
    get_member($mysqli, $result_big_arr[0]['qr_type_big_id']);
}


function get_member($mysqli, $qr_type_big_id)
{
    $query_member = "SELECT A.*, B.title AS 'big_title' FROM member A LEFT JOIN qr_type_big B ON A.qr_type_big_id = B.qr_type_big_id WHERE B.qr_type_big_id = '" . $qr_type_big_id . "' ORDER BY A.orders ASC";

    $result_member_arr = array();

    if ($result = $mysqli->query($query_member)) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $result_member_arr[] = $row;
        }

        mysqli_free_result($result);
    }

    $output = "";
    foreach ($result_member_arr as $member) {
        $output .= "<option value='{$member['title']}' data-member-id='{$member['member_id']}'>{$member['title']}</option>";
    }
    return $output;
}

// Function to get qrcode_big_id
function get_qrcode_big_id($mysqli, $qr_type_big_id, $member_id)
{
    $query_qrcode = "SELECT qrcode_big_id FROM qrcode_big WHERE qr_type_big_id = '" . $mysqli->real_escape_string($qr_type_big_id) . "' AND member_id = '" . $mysqli->real_escape_string($member_id) . "'";
    $result_qrcode = $mysqli->query($query_qrcode);

    if ($row = $result_qrcode->fetch_array(MYSQLI_ASSOC)) {
        return  $row['qrcode_big_id'];
    } else {
        return null;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['qr_type_big_id']) && !isset($_POST['member_id'])) {
        echo get_member($mysqli, $_POST['qr_type_big_id']);
    } elseif (isset($_POST['qr_type_big_id']) && isset($_POST['member_id'])) {
        echo get_qrcode_big_id($mysqli, $_POST['qr_type_big_id'], $_POST['member_id']);
    }
    exit;
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
                            <form method="post" id="form" enctype="multipart/form-data" action="<?php echo str_replace("_mang.php", "_mang_end.php", $_SERVER['PHP_SELF']) ?>">
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
                                                    <th width="20%">選擇分會</th>
                                                    <td>
                                                        <select data-title="單位管理權限" name="qr_type_big_id" id="qr_type_big_id" class="form-control col-md-6" defaults="<?php echo isset($result_arr[0]["qr_type_big_id"]) ? $result_arr[0]["qr_type_big_id"] : ""; ?>">
                                                            <option value="">請選擇可管理的單位 (全部分會)</option>
                                                            <?php
                                                            foreach ($result_big_arr as $value) {
                                                                if ($result_arr[0]["qr_type_big_id"] == $value["qr_type_big_id"]) {
                                                                    echo "<option value='" . $value["qr_type_big_id"] . "' selected>" . $value["title"] . "</option>";
                                                                } else {
                                                                    echo "<option value='" . $value["qr_type_big_id"] . "'>" . $value["title"] . "</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">名稱<span class="required">*</span></th>
                                                    <td>
                                                        <?php if (isset($result_arr[0]["nickname"])) { ?>
                                                            <input req readonly data-title="名稱" class="form-control" name="nickname" placeholder="請輸入管理員名稱" value="<?php echo isset($result_arr[0]["nickname"]) ? $result_arr[0]["nickname"] : "" ?>">
                                                        <?php } else { ?>
                                                            <input data-title="名稱" class="form-control nickname_input" name="nickname" placeholder="請輸入管理員名稱" value="">
                                                            <select style="display:none;" data-title="單位管理權限" name="nickname" id="nickname_dropdown" class="form-control col-md-6" defaults="<?php echo isset($result_arr[0]["nickname"]) ? $result_arr[0]["nickname"] : ""; ?>">
                                                            </select>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">帳號<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="帳號" class="form-control" name="account" placeholder="請輸入管理員帳號" value="<?php echo isset($result_arr[0]["account"]) ? $result_arr[0]["account"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">
                                                        密碼<?php echo ($model == "add") ? '<span class="required">*</span>' : '<br/><span class="text-xs text-danger font-weight-normal">不填寫表示不修改</span>' ?></th>
                                                    <td>
                                                        <input <?php echo ($model == "add") ? "req" : "" ?> data-title="密碼" placeholder="請輸入密碼" type="password" class="form-control" name="password">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">
                                                        密碼確認<?php echo ($model == "add") ? '<span class="required">*</span>' : '<br/><span class="text-xs text-danger font-weight-normal">不填寫表示不修改</span>' ?></th>
                                                    <td>
                                                        <input <?php echo ($model == "add") ? "req" : "" ?> data-title="密碼確認" placeholder="請再輸入一次密碼" type="password" class="form-control" name="password2">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <th width="20%">權限<br /><span class="text-xs text-danger font-weight-normal">不勾選代表權限最大</span>
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
                                                        <input type="text" class="hide" name="permission" value="<?php echo isset($result_arr[0]["permission"]) ? htmlspecialchars($result_arr[0]["permission"]) : ""; ?>" box_str>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="20%">備註<span class="required"></span></th>
                                                    <td>
                                                        <textarea data-title="備註" class="form-control" name="note" rows="5" placeholder="請輸入備註"><?php echo isset($result_arr[0]["note"]) ? br2nl($result_arr[0]["note"]) : "" ?></textarea>
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
                                        <!-- Hidden input for qrcode_big_id -->
                                        <input type="hidden" id="member_id" name="member_id" value="<?php echo $member_id; ?>">

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
            <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
            <script>
                $(document).ready(function() {
                    $('#qr_type_big_id').change(function() {
                        var qr_type_big_id = $(this).val();

                        $.ajax({
                            url: '',
                            type: 'POST',
                            data: {
                                qr_type_big_id: qr_type_big_id
                            },
                            success: function(response) {
                                console.log(response);
                                if (response) {
                                    $('.nickname_input').hide();
                                    $('#nickname_dropdown').show();
                                    $('#nickname_dropdown').html(response);
                                    var member_id = $('#nickname_dropdown').find(':selected').data('member-id');
                                    $('#member_id').val(member_id);
                                    $('#qrcode_big_id').val('');
                                } else {
                                    $('.nickname_input').show();
                                    $('#nickname_dropdown').hide();
                                }

                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                            }
                        });
                    });

                    $('#nickname_dropdown').change(function() {
                        var qr_type_big_id = $('#qr_type_big_id').val();
                        var member_id = $('#nickname_dropdown').find(':selected').data('member-id');

                        $.ajax({
                            url: '', // Send the request to the same PHP file
                            type: 'POST',
                            data: {
                                qr_type_big_id: qr_type_big_id,
                                member_id: member_id
                            },
                            success: function(response) {
                                console.log(response);
                                $('#qrcode_big_id').val(response);
                                $('#member_id').val(member_id);
                            },
                            error: function(xhr, status, error) {
                                console.error('AJAX Error:', status, error);
                            }
                        });
                    });
                });
            </script>
            <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
            All rights reserved.
        </footer>


    </div>
    <!-- ./wrapper -->

    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>