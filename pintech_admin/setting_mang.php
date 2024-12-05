<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$model = "update";
@$id = '1';

$result_arr = array();
$query = "SELECT * FROM setting WHERE id = '" . $id . "';";

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
                            <form method="post" id="form" enctype="multipart/form-data"
                                action="<?php echo str_replace("_mang.php", "_mang_end.php", $_SERVER['PHP_SELF']) ?>">
                                <div class="card">
                                    <div class="card-body table-responsive-sm">
                                        <h6 class="card-subtitle mb-2 text-muted"><span
                                                class="required font-weight-bold">*</span> <small>為必填欄位</small>
                                        </h6>
                                        <table class="table table-bordered">
                                            <tbody>
                                                <?php
                                                if ($model == "update") {
                                                    echo '<tr>';
                                                    echo '<th width="15%">建立時間</th>';
                                                    echo '<td>' . (isset($result_arr[0]["pub_date"]) ? $result_arr[0]["pub_date"] : "-") . '</td>';
                                                    echo '</tr>';
                                                    echo '<tr>';
                                                    echo '<th width="15%">更新時間</th>';
                                                    echo '<td>' . (isset($result_arr[0]["last_date"]) ? $result_arr[0]["last_date"] : "-") . '</td>';
                                                    echo '</tr>';
                                                }
                                                ?>
                                                <tr>
                                                    <th width="15%">預設推播留言<span class="required"></span></th>
                                                    <td>
                                                        <textarea data-title="預設推播留言" class="form-control"
                                                            name="content"
                                                            rows="3"
                                                            placeholder="請輸入預設推播留言"><?php echo isset($result_arr[0]["content"]) ? br2nl($result_arr[0]["content"]) : "" ?></textarea>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">Line liff 大碼<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="Line Full" class="form-control" name="linefull" placeholder="請輸入Line Full" value="<?php echo isset($result_arr[0]["linefull"]) ? $result_arr[0]["linefull"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">Line liff 關閉<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="Line Close" class="form-control" name="lineclose" placeholder="請輸入Line Close" value="<?php echo isset($result_arr[0]["lineclose"]) ? $result_arr[0]["lineclose"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">Line Oauth<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="Line Oauth" class="form-control" name="lineoauth" placeholder="請輸入Line Oauth" value="<?php echo isset($result_arr[0]["lineoauth"]) ? $result_arr[0]["lineoauth"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">Line Chat Channel Access Token<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="Line Token" class="form-control" name="linetoken" placeholder="請輸入Line Token" value="<?php echo isset($result_arr[0]["linetoken"]) ? $result_arr[0]["linetoken"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">Line Chat Channel Secret<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="Line Close" class="form-control" name="linesecret" placeholder="請輸入Line Secret" value="<?php echo isset($result_arr[0]["linesecret"]) ? $result_arr[0]["linesecret"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">網域名<span class="required">*</span></th>
                                                    <td>
                                                        <input req data-title="網域名" class="form-control" name="domain" placeholder="請輸入網域名" value="<?php echo isset($result_arr[0]["domain"]) ? $result_arr[0]["domain"] : "" ?>">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th width="15%">驗證碼狀態<span class="required">*</span></th>
                                                    <td>
                                                        <!-- Hidden input to hold the toggle state value -->
                                                        <input type="hidden" id="verificationCodeStatus" name="smscode" value="<?php echo isset($result_arr[0]["smscode"]) ? $result_arr[0]["smscode"] : "-1"; ?>">

                                                        <!-- Toggle button -->
                                                        <button type="button" id="toggleVerificationCode" class="btn btn-secondary">
                                                            <?php echo isset($result_arr[0]["smscode"]) && $result_arr[0]["smscode"] == "1" ? "開啟" : "關閉"; ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <th width="15%">社區<span class="required"></span></th>
                                                <td>
                                                    <div id="community-inputs">
                                                        <!-- Existing inputs from the database -->
                                                        <?php
                                                        if (!empty($result_arr[0]["form"])) {
                                                            // Decode the stored JSON or associative array
                                                            $communities = json_decode($result_arr[0]["form"], true);
                                                            if (is_array($communities)) {
                                                                foreach ($communities as $community) {
                                                                    echo '<div class="input-group mb-2">';
                                                                    echo '<input type="text" name="community[label][]" class="form-control" placeholder="請輸入標籤 (Label)" value="' . htmlspecialchars($community['label']) . '">';
                                                                    echo '<input type="text" name="community[name][]" class="form-control" placeholder="請輸入名稱 (Name)" value="' . htmlspecialchars($community['name']) . '">';
                                                                    echo '<button type="button" class="btn btn-danger remove-community">-</button>';
                                                                    echo '</div>';
                                                                }
                                                            }
                                                        } else {
                                                            echo '<div class="input-group mb-2">';
                                                            echo '<input type="text" name="community[label][]" class="form-control" placeholder="請輸入標籤 (Label)">';
                                                            echo '<input type="text" name="community[name][]" class="form-control" placeholder="請輸入名稱 (Name)">';
                                                            echo '<button type="button" class="btn btn-danger remove-community">-</button>';
                                                            echo '</div>';
                                                        }
                                                        ?>
                                                    </div>
                                                    <button type="button" class="btn btn-success" id="add-community">+</button>
                                                </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- /.card-body -->
                                    <div class="card-footer d-flex justify-content-end">
                                        <button type="reset" class="mx-1 btn btn-outline-dark">重設</button>
                                        <button type="submit" class="btn btn-success" name="post">儲存</button>
                                        <input type="hidden" name="id" value="<?php echo $id; ?>">
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
        <!--<footer class="main-footer text-sm">
        <strong>Copyright &copy; <a href="https://www.pintech.com.tw" target="_blank">PinTech</a>.</strong>
        All rights reserved.
    </footer>-->


    </div>
    <!-- ./wrapper -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const communityInputs = document.getElementById("community-inputs");
            const addCommunityBtn = document.getElementById("add-community");

            // Add new community input group
            addCommunityBtn.addEventListener("click", function() {
                const newInputGroup = document.createElement("div");
                newInputGroup.classList.add("input-group", "mb-2");

                newInputGroup.innerHTML = `
            <input type="text" name="community[label][]" class="form-control" placeholder="請輸入標籤 (Label)">
            <input type="text" name="community[name][]" class="form-control" placeholder="請輸入名稱 (Name)">
            <button type="button" class="btn btn-danger remove-community">-</button>
        `;

                communityInputs.appendChild(newInputGroup);
            });

            // Remove a community input group
            communityInputs.addEventListener("click", function(event) {
                if (event.target.classList.contains("remove-community")) {
                    const inputGroup = event.target.parentElement;
                    communityInputs.removeChild(inputGroup);
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            const toggleButton = document.getElementById("toggleVerificationCode");
            const statusInput = document.getElementById("verificationCodeStatus");

            toggleButton.addEventListener("click", function() {
                if (statusInput.value === "1") {
                    // Disable
                    statusInput.value = "-1";
                    toggleButton.textContent = "關閉";
                    toggleButton.classList.remove("btn-success");
                    toggleButton.classList.add("btn-secondary");
                } else {
                    // Enable
                    statusInput.value = "1";
                    toggleButton.textContent = "開啟";
                    toggleButton.classList.remove("btn-secondary");
                    toggleButton.classList.add("btn-success");
                }
            });
        });
    </script>
    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>
</body>

</html>