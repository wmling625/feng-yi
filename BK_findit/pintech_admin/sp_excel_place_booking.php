<?php
include_once(dirname(__FILE__) . "/../phplibs/backend_head.php");

@$cmd = aes_decrypt(params_security($_GET["cmd"]));
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);

$arrData = array();

if (!empty($cmd) && !empty($value) && !empty($token)) {

    if (token_validation($value, $token)) {

        $title = [['場館名稱', '場次日期', '場次時段', '會員姓名', '會員電話', '入場序號', '預約狀態']];

        $query = $cmd;

        if ($result = $mysqli->query($query)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                // $arr[] <=> array_push($arr, [key=>value])

                // 場次時長
                $unit = 0;
                if ($row["unit"] == "0") {
                    $unit = 60;
                } elseif ($row["unit"] == "1") {
                    $unit = 30;
                }

                // 目前時段
                $time_range = "-";
                if (isset($row["time"])) {
                    $end_time = date("H:i:s", strtotime("+" . $unit . " minute", strtotime($row["time"])));
                    $time_range = $row["time"] . '~' . $end_time;
                }

                // 預約狀態
                $status = "";
                if ($row["orders"] == "0") {
                    $status = "預約成功";
                } elseif ($row["orders"] == "-1") {
                    $status = "取消預約";
                }

                $arrData[] = [$row["place_title"], $row["date"], $time_range, $row["member_title"], $row["account"], $row["checkin_id"], $status];
            }
            mysqli_free_result($result);
        }


        $arrData = array_merge($title, $arrData);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
        $spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal('left');

        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(35);
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(25);

        $spreadsheet->getActiveSheet()->fromArray($arrData);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        header('Content-Description: File Transfer');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment;filename='.date("Ymdhis").'.xlsx');
        header("Content-Disposition: attachment;filename=\"場次預約_" . date("Ymdhis") . ".xlsx\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        exit();

    } else {
        echo "<script>alert('資料逾時，請重新登入')</script>";
        echo "<script>document.location.href = 'logout.php'</script>";
        exit;
    }

}