<?php
set_time_limit(0);
ini_set('memory_limit', '256M');
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */

include_once(dirname(__FILE__)."/./PhpSpreadsheet/autoload.php");



$title = [['編號', '使用者']];
$arrData[0] = ["001","品科技1品科技1品科技1品科技1"]; //合併欄位展示
$arrData[1] = ["002","品科技2品科技2品科技2品科技2"];

$arrData = array_merge($title, $arrData);
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$spreadsheet->getDefaultStyle()->getAlignment()->setWrapText(true);
$spreadsheet->getDefaultStyle()->getAlignment()->setHorizontal('left');

//$spreadsheet->getActiveSheet()->mergeCells('A2:B2'); //合併欄位展示

$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$spreadsheet->getActiveSheet()->fromArray($arrData);
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
header('Content-Description: File Transfer');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//header('Content-Disposition: attachment;filename='.date("Ymdhis").'.xlsx');
header("Content-Disposition: attachment;filename=\"".date("Ymdhis").".xlsx\"");
header('Cache-Control: max-age=0');
$writer->save('php://output');

exit();
?>


