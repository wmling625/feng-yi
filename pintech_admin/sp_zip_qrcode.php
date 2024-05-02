<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$cmd = aes_decrypt(params_security($_GET["cmd"]));
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);

$arrData = array();

if (!empty($cmd) && !empty($value) && !empty($token)) {

    if (token_validation($value, $token)) {

        $query = $cmd;

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $arrData[] = $row['file0'];
            }
            mysqli_free_result($result);
        }

        // ★★ 禁止使用AJAX呼叫本頁, 因為沒辦法知道何時壓縮完成, 會有timeout問題
        // ★★ 通常本頁會寫在獨立頁，用新視窗到本頁後, 壓縮成功再產生連結就好
        // ★★ windows主機需先安裝7zip 64位元, linux 不需要安裝任何軟體


        $arrData = array_filter($arrData);

        if (count($arrData) > 0) {
            // 大約一次只能壓縮1300左右，所以就用1000下去產生
            $subarrays = array_chunk($arrData, 1000, false);
            $result_str = "";
            $index = 0;
            $datetime = date("Ymdhis");
            foreach ($subarrays as $sub) {
                $index++;
                $temp = [];
                $temp["end_dir"] = dirname(__FILE__) . "/../uploads/qrcode/"; // zip行為通常在後台產生, 所以uploads會在上一層
                $temp["end_name"] = "QRCode圖片_" . $datetime . "_" . $index . ".zip"; // zip檔名
                $temp["source"] = array();

                foreach ($sub as $value) {
                    $temp["source"][] = dirname(__FILE__) . "/../uploads/qrcode/" . $value; // 一次可壓縮多個檔案, 要放入完整路徑
                }

                $temp_callback = zip_save($temp);
                if ($temp_callback["state"] == "-1") {
                    echo "<script>alert('" . $temp_callback["message"] . "')</script>";
                    echo "<script>window.close()</script>";
                    exit;
                } else {
                    $link = 'https://' . $_SERVER['HTTP_HOST'] . '/uploads/qrcode/' . $temp_callback["data"]["end_name"];
                    //★★ 注意，壓縮的檔案不會100%依照指定的, 即便指定 $temp["end_name"] 檔名是123.zip, 如果server是windows則返回[123.zip] ; 如果server是linux則返回[123.zip.tar], 所以要以$temp_callback["data"]["end_name"]檔名為主 ,要用7zip解開
                    //★★ 如果產生的壓縮檔無法下載, 顯示 Forbidden , 通知明宗開啟主機特殊權限
                    //★★ 如果要在本機測試, 注意上方網址只會有http, 不會是https
                    $result_str .= "<a href='" . $link . "'>點我下載壓縮檔 (" . $index . ")</a> " . count($sub) . " 張QRCode圖片<br/>";
                }
            }
            echo $result_str;
        } else {
            echo "<script>alert('查無QRCode圖片資訊，請重新篩選')</script>";
            echo "<script>window.close()</script>";
            exit();
        }

    } else {
        echo "<script>alert('資料逾時，請重新登入')</script>";
//        echo "<script>document.location.href = 'logout.php'</script>";
        exit;
    }

}