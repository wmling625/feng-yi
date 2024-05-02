<?php
set_time_limit(0);
ini_set('memory_limit', '256M');
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');

//三竹二站	
function sms_send_c2c($CELL_PHONE, $others1, $port = "80", $userid, $passwd)
{

    $url = "http://smsb2c.mitake.com.tw/b2c/mtk/SmSend?username=" . $userid .
        "&password=" . $passwd . "&dstaddr=" . $CELL_PHONE .
        "&DestName=" . urlencode("From_APP") . "&smbody=" . str_replace("+", " ", urlencode($others1)) . "&CharsetURL=UTF-8";

    $callback = download_page($url);
    $response_array = array();
    $response_str_array = explode("\r\n", $callback);
    return (iconv("BIG5", "UTF-8", $response_str_array[2]) == "statuscode=1") ? 1 : 0;
}

//三竹三站
function sms_send_b2c($CELL_PHONE, $others1, $port = "80", $userid, $passwd)
{
    $url = "http://smexpress.mitake.com.tw:" . $port . "/SmSendGet.asp?username=" . $userid .
        "&password=" . $passwd . "&dstaddr=" . $CELL_PHONE .
        "&DestName=" . urlencode("From_APP") . "&smbody=" . str_replace("+", " ", urlencode($others1)) . "&encoding=UTF8";

    $callback = download_page($url);
    $response_array = array();
    $response_str_array = explode("\r\n", $callback);
    return (iconv("BIG5", "UTF-8", $response_str_array[2]) == "statuscode=1") ? 1 : 0;
}

function sms_send_api2($CELL_PHONE, $others1, $userid, $passwd)
{
    $url = "https://smsapi.mitake.com.tw/api/mtk/SmSend?CharsetURL=UTF-8"
        . "&username=" . $userid .
        "&password=" . $passwd . "&dstaddr=" . $CELL_PHONE .
        "&smbody=" . str_replace("+", " ", urlencode($others1));

    $callback = download_page($url);
    $response_str_array = explode("\r\n", $callback);
    print_r($response_str_array);
    return (iconv("BIG5", "UTF-8", $response_str_array[2]) == "statuscode=1") ? 1 : 0;
}

function sms_send_api($CELL_PHONE, $others1, $userid, $passwd)
{
    $curl = curl_init();
// url
    $url = 'https://smsapi.mitake.com.tw/api/mtk/SmSend?';
    $url .= 'CharsetURL=UTF-8';
// parameters
    $data = 'username=' . $userid;
    $data .= '&password=' . $passwd;
    $data .= '&dstaddr=' . $CELL_PHONE;
    $data .= '&smbody=簡訊SmSend測試';
// 設定curl網址
    curl_setopt($curl, CURLOPT_URL, $url);
// 設定Header
    curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/x-www-form-urlencoded")
    );
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HEADER, 0);
// 執行
    $output = curl_exec($curl);
    curl_close($curl);
    echo $output;
}