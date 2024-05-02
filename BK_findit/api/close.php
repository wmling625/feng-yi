<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
?>
<!DOCTYPE html>
<html dir="ltr" lang="zh-TW">
<head>
    <!-- Standard Meta -->
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="google" content="notranslate"/>
    <meta name="author" content="品科技 pintech https://www.pintech.com.tw/">
    <meta name="description" content="">
    <meta name="keywords" content=""/>
    <meta name="viewport"
          content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="google" content="notranslate"/>

    <!-- 製作LINE WEB 須加上-->
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache, no-store">
    <title>關閉視窗</title>


    <script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
</head>

<body>


<!-- Scripts -->
<script src="../js/jquery.min.js"></script>
<script src="../js/global.js?t=<?php echo time(); ?>"></script>

<script>

    $(function () {

        liff.init(
            {
                liffId: "<?php echo $liff_close;?>"
            },
            function () {
                liff.closeWindow();
            },
            function (err) {
                alert("錯誤代碼:" + err.code + "\n錯誤訊息" + err.message);
            });
    })

</script>


</body>
</html> 