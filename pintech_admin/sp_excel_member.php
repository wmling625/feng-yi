<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$cmd = aes_decrypt(params_security($_GET["cmd"]));
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);

$arrData = array();

if (!empty($cmd) && !empty($value) && !empty($token)) {

    if (token_validation($value, $token)) {

        // QRCode紀錄
        $title = [['建立時間', 'Line暱稱', '會員姓名', '會員手機', '會員性別', '活動區域', '所屬單位', '管理權限', '啟用']];

        $query = $cmd;

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                //                $arr = json_decode($row["certificate2"], true);
                $isActive = "未開通";
                if ($row['orders'] == "1") {
                    $isActive = "已開通";
                } elseif ($row['orders'] == "-1") {
                    $isActive = "未開通";
                }

                $isManager = !empty($row['big_title']) ? $row['big_title'] : 'X';
                

                $big_type_query = "SELECT *, qr_type_big.title AS typeTitle FROM `qrcode_big` LEFT JOIN `qr_type_big` ON qrcode_big.qr_type_big_id = qr_type_big.qr_type_big_id WHERE `member_id` = '" . $row['member_id'] . "'";
                $type_arr = array();
                if ($big_type_result = $mysqli->query($big_type_query)) {
                    $rows = $big_type_result->fetch_array();
                    $type_arr[] = $rows;
                    mysqli_free_result($big_type_result);
                }


                foreach ($type_arr as $key => $type) {
                    $typeTitle = !empty($type['typeTitle']) ? $type['typeTitle'] . ',' : 'X';
                }

                //                $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_redirect.php?qrcode_id=" . aes_encrypt($row['qrcode_id']));
                //                $qrcode = "https://chart.googleapis.com/chart?chs=500x500&cht=qr&chl=" . urlencode($link) . "&choe=UTF-8";
                //                $link = "";
                //                $base = "https://oneqrcode.feng-yi.tw/uploads/qrcode/";
                //                $photo_small1 = ($row['file0'] == "") ? "" : $row['file0'];
                //                if (is_file("../uploads/qrcode/" . $photo_small1)) {
                //                    $link = $base . $photo_small1;
                //                }

                $link = "https://liff.line.me/" . $liff_full . "?end_point=" . aes_encrypt("code_redirect.php?qrcode_id=" . aes_encrypt($row["qrcode_id"]));

                $arrData[] = [$row["pub_date"], $row["title"], $row["nickname"], $row["account"], $row['types_option'], $row["city"] . $row["region"], $typeTitle, $isManager, $isActive];
            }
            mysqli_free_result($result);
        }


        $arrData = array_merge($title, $arrData);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal('left');

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(70);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(70);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(25);
        // $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(25);
        // $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(25);
        // $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(40);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(40);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(25);
        //        $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(25);


        $spreadsheet->getActiveSheet()->fromArray($arrData);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment;filename='.date("Ymdhis").'.xlsx');
        header("Content-Disposition: attachment;filename=\"會員列表_" . date("Ymdhis") . ".xlsx\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        exit();
    } else {
        echo "<script>alert('資料逾時，請重新登入')</script>";
        echo "<script>document.location.href = 'logout.php'</script>";
        exit;
    }
}
