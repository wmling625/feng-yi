<?php
session_start();//必須位於腳本的最頂端
$image = imagecreatetruecolor(100, 30);//imagecreatetruecolor函數建一個真彩色圖像
//生成彩色像素
$bgcolor = imagecolorallocate($image, 255, 255, 255);//白色背景     imagecolorallocate函數為一幅圖像分配顏色
$textcolor = imagecolorallocate($image, 0, 0, 255);//藍色文本
//填充函數，xy確定座標，color顏色執行區域填充顏色
imagefill($image, 0, 0, $bgcolor);
$captch_code = "";//初始空值

//該循環,循環取數
for ($i = 0; $i < 4; $i++) {
    $fontsize = 5;
    $x = ($i * 25) + rand(5, 10);
    $y = rand(5, 10);//位置隨機
    //  $fontcontent=$i>2?chr(rand(97,122)):chr(rand(65,90));//是小寫，否則是大寫
    $data = 'abcdefghijkmnpqrstuvwxy3456789';
    $fontcontent = strtoupper(substr($data, rand(0, strlen($data) - 1), 1));//strlen僅僅是一個計數器的工作  含數字和字母的驗證碼
    //可以理解為數組長度0到30

    $fontcolor = imagecolorallocate($image, 0, 0, 0);//隨機的rgb()值可以自己定

    imagestring($image, $fontsize, $x, $y, $fontcontent, $fontcolor); //水平地畫一行字符串
    $captch_code .= $fontcontent;
}

$_SESSION['authcode'] = strtoupper($captch_code);//將變量保存再session的authcode變量中

//該循環,循環畫背景干擾的點
for ($m = 0; $m <= 250; $m++) {

    $x2 = rand(1, 99);
    $y2 = rand(1, 99);
    $pointcolor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imagesetpixel($image, $x2, $y2, $pointcolor);// 水平地畫一串像素點
}

//該循環,循環畫干擾直線
for ($i = 0; $i <= 10; $i++) {
    $x1 = rand(0, 99);
    $y1 = rand(0, 99);
    $x2 = rand(0, 99);
    $y2 = rand(0, 99);
    $linecolor = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
    imageline($image, $x1, $y1, $x2, $y2, $linecolor);//畫一條線段

}
header('content-type:image/png');
imagepng($image);
//銷燬
imagedestroy($image);
?>