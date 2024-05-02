<?php
include_once(dirname(__FILE__)."/./phplibs/root.php");
include_once(dirname(__FILE__)."/./phplibs/root_global.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>會員通知menu</title>


    <style>
        body,html {
            color: #484848;
            font-family: 微軟正黑體;
        }
        ul{
            list-style: none;
            margin: 0;
            padding: 0;
            margin-bottom: 32px;
            padding-bottom: 32px;
            border-bottom: 1px solid #eee;
        }
        li{
            margin: 0;
            padding: 12px 24px;
            font-size: 15px;
        }
        a{
            text-decoration: none;
            color: #484848;
        }
        h1 {
            font-size: 24px;
            margin-left: 16px;
        }
        .text-gray {
            color: #9f9f9f;
        }
        .text-accent {
            color: #d96060;
        }
        .text-decoration-underline {
            text-decoration: underline;
        }
        .pb-0 {
            padding-bottom: 0;
        }
    </style>
</head>
<body>
    <h1>會員通知 MENU</h1>
    <ul>
        <!-- <li>六宮格選擇「個人通知條碼列表頁」但系統找不到他的line帳號時</li> -->
        <li class="pb-0"><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("login.php"); ?>">登入/註冊</a></li>
        <li class="text-gray">當使用者換LINE帳號或被登出時，經過登入/註冊頁驗證如果系統有找到相符的手機號碼，就把這支手機的LINE帳號更新成現在登入的這個帳號；如果找不到的話表示這個個人沒註冊過我們就直接幫他註冊</li>
        <hr>
        <li class="pb-0 text-accent">當使用者有註冊/登入時，才能開始綁定QR CODE　↓↓↓</li>
        <li><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("fill_in.php"); ?>">綁定QR CODE</a></li>
        <li><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("code_list.php"); ?>">個人通知條碼列表頁（手機驗證成功）</a></li>
        <li><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("code_list_empty.php"); ?>">個人通知條碼列表頁（手機驗證成功但沒綁定過QR CODE）</a></li>
        <li><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("my_info.php"); ?>">我的綁定好的QR CODE資訊頁</a></li>
        <!-- <li><a class="text-decoration-underline" target="_blank" href="edit.php">編輯QR CODE</a></li> -->
        <hr>
<!--        <li><a class="text-decoration-underline" target="_blank" href="info_disabled.php?qrcode_id=BoSbWH5bZe75R-Gd1Zz_I6wYwQq45MDSWEKibTdLt73mumN8Jj73eksRz2-HsNdQ">查看QR CODE資訊頁（未註冊LINE有彈跳視窗通知）</a></li>-->
        <li><a class="text-decoration-underline" target="_blank" href="info.php?qrcode_id=BoSbWH5bZe75R-Gd1Zz_I6wYwQq45MDSWEKibTdLt73mumN8Jj73eksRz2-HsNdQ">查看QR CODE資訊頁（未註冊LINE有彈跳視窗通知/有註冊LINE可留言）</a></li>
        <hr>
        <li class="pb-0"><a class="text-decoration-underline" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("comment.php"); ?>">其他人留言給家屬 的留言頁</a></li>
        <li class="text-gray">點選 <a class="text-decoration-underline text-gray" target="_blank" href="https://liff.line.me/<?php echo $liff_full; ?>?end_point=<?php echo aes_encrypt("info.php"); ?>">查看QR CODE資訊頁的「留言給家屬 」按鈕</a> 並送出後，家屬 會收到留言頁的連結，可以在此頁查看對方留言/回覆</li>

    </ul>

    <!-- <ul>
        <li><a target="_blank" href="javascript:void(0)">選單六宮格（圖片）</a></li>
    </ul> -->

</body>
</html>