<?php
include_once(dirname(__FILE__) . "/../phplibs/root.php");
include_once(dirname(__FILE__) . "/../phplibs/root_global.php");

if (isset($_GET["liff_state"])) {

} else {
    $end_point = params_security($_GET["end_point"]);
    echo "<script>var end_point = '" . aes_decrypt($end_point) . "'; </script>";
}
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
    <meta name="keywords" conten=""/>
    <meta name="viewport"
          content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="google" content="notranslate"/>

    <!-- 製作LINE WEB 須加上-->
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Cache-Control" content="no-cache, no-store">
    <title>會員通知</title>


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
                liffId: "<?php echo $liff_full; ?>",
                withLoginOnExternalBrowser: true
            },
            function () {
                liff.getProfile().then(function (profile) {
                    var displayName = profile.displayName;
                    var pictureUrl = profile.pictureUrl;
                    var userId = profile.userId;
                    var profile = aes_encrypt(JSON.stringify({
                        "displayName": displayName,
                        "pictureUrl": pictureUrl,
                        "userId": userId
                    }));

                    if (end_point.startsWith("big_redirect.php") || end_point.startsWith("code_redirect.php") || end_point.startsWith("info.php") || end_point.startsWith("big_info.php")) {
                        targetHref(end_point, profile) // 不檢查會員是否存在
                    } else {
                        /* ajax 判斷該 userId 是否存在 */
                        var event_arr = [];
                        event_arr['success'] = function (data) {
                            if (parseInt(data.state) < 0) {
                                /* 會員不存在 > 導向註冊頁 */
                                if (end_point.startsWith("big_")) {
                                    document.location.href = "../big_login.php" + "?t=" + guid() + "&profile=" + profile
                                } else {
                                    document.location.href = "../login.php" + "?t=" + guid() + "&profile=" + profile
                                }
                            }

                            if (parseInt(data.state) === 0) {
                                /* 會員停權 > 關閉 */
                                alert(data.message)
                                document.location.href = 'https://liff.line.me/<?php echo $liff_close;?>';
                            }

                            if (parseInt(data.state) >= 1) {
                                /* 會員存在 > 保持登入狀態 */
                                targetHref(end_point, profile)
                            }
                        }
                        ajax_pub_adv("member_validate.php", {"userId": userId}, event_arr, {"async": true})
                    }

                })
            },
            function (err) {
                alert("錯誤代碼:" + err.code + "\n錯誤訊息" + err.message);
            });

    })

    function targetHref(end_point, profile) {
        if (end_point) {
            // 判斷是否帶有其他導向頁
            var redirect = end_point.split("#")

            if (redirect.length > 1) {
                var url = redirect[1].slice(1);
                document.location.href = "../" + redirect[0] + "?t=" + guid() + "&profile=" + profile + "&redirect=" + url
            } else {
                // 判斷是否帶有其他參數
                var params = end_point.split("?")
                if (params.length > 1) {
                    document.location.href = "../" + params[0] + "?t=" + guid() + "&profile=" + profile + "&" + params[1]
                } else {
                    document.location.href = "../" + end_point + "?t=" + guid() + "&profile=" + profile
                }
            }
        } else {
            document.location.href = "../code_list.php" + "?t=" + guid() + "&profile=" + profile
        }
    }

</script>


</body>
</html> 