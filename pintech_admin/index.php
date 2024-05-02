<?php

include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");
if (isset($_SESSION['admin'])) {
    echo "<script> document.location.href = 'init.php' </script>";
}
?>
<!DOCTYPE html>
<html lang="zh-TW">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>網站管理系統</title>
    <?php include_once(dirname(__FILE__) . "/../phplibs/backend_link.php") ?>    

    <script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<body>

<div class="position-relative w-100 min-vh-100 bg-gradient-liquid">
    <div class="position-relative d-flex justify-content-center align-items-center vh-100">
        <div class="card">
            <div class="card-body">
                <h1 class="h3 mb-3 font-weight-normal text-center">登入管理系統</h1>
                <form id="form" action="index_end.php" method="post">
                    <div class="mb-3">
                        <label for="account" class="form-label">管理員帳號</label>
                        <input type="text" class="form-control" id="account" name="account" required autofocus
                               aria-describedby="請輸入管理員帳號">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">密碼</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <div class="d-flex justify-content-center g-recaptcha"
                             data-sitekey="6Lf3xzkhAAAAAHUhXd8cwBWwLBdcIjj33YerBgfG"></div>
                    </div>
                    <!--                    <div class="form-group">-->
                    <!--                        <div class="custom-control custom-checkbox small">-->
                    <!--                            <input type="checkbox" class="custom-control-input" id="rememberMeCheck">-->
                    <!--                            <label class="custom-control-label" for="rememberMeCheck">Remember-->
                    <!--                                Me</label>-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                    <!--                    <p id="error-mes"></p>-->
                    <button type="submit" class="btn btn-outline-dark w-100 mt-3">
                        登入
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>


<?php include_once(dirname(__FILE__) . "/../phplibs/backend_footer.php") ?>

</body>

</html>