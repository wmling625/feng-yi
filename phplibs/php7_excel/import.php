<?php
set_time_limit(0);
ini_set('memory_limit', '256M');
header("Content-Type:text/html; charset=utf-8");
date_default_timezone_set('Asia/Taipei');

if (PHP_SAPI == 'cli')
	die('This example should only be run from a Web Browser');

/** Include PHPExcel */
include_once(dirname(__FILE__)."/./phplibs/root.php");
include_once(dirname(__FILE__)."/./phplibs/root_global.php");
include_once(dirname(__FILE__)."/./PhpSpreadsheet/autoload.php");


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


print_r(importExecl("demo.xlsx"));

function importExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, $options = [])
{
    try {
        /* 轉碼 */
        $file = mb_convert_encoding($file,"utf-8","big5");

        if (empty($file) OR !file_exists($file)) {
            throw new \Exception('檔案不存在!');
        }
	
        /** @var Xlsx $objRead */
        $objRead = IOFactory::createReader('Xlsx');

        if (!$objRead->canRead($file)) {
            /** @var Xls $objRead */
            $objRead = IOFactory::createReader('Xls');

            if (!$objRead->canRead($file)) {
                throw new \Exception('只支援匯入Excel檔案！');
            }
        }

        /* 如果不需要獲取特殊操作，則只讀內容，可以大幅度提升讀取Excel效率 */
        empty($options) && $objRead->setReadDataOnly(true);
        /* 建立excel物件 */
        $obj = $objRead->load($file);
        /* 獲取指定的sheet表 */
        $currSheet = $obj->getSheet($sheet);

        if (isset($options['mergeCells'])) {
            /* 讀取合併行列 */
            $options['mergeCells'] = $currSheet->getMergeCells();
        }

        if (0 == $columnCnt) {
            /* 取得最大的列號 */
            $columnH = $currSheet->getHighestColumn();
            /* 相容原邏輯，迴圈時使用的是小於等於 */
            $columnCnt = Coordinate::columnIndexFromString($columnH);
        }

        /* 獲取總行數 */
        $rowCnt = $currSheet->getHighestRow();
        $data   = [];

        /* 讀取內容 */
        for ($_row = 2; $_row <= $rowCnt; $_row++) {
            $isNull = true;

            for ($_column = 1; $_column <= $columnCnt; $_column++) {
                $cellName = Coordinate::stringFromColumnIndex($_column);
                $cellId   = $cellName . $_row;
                $cell     = $currSheet->getCell($cellId);

                if (isset($options['format'])) {
                    /* 獲取格式 */
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    /* 記錄格式 */
                    $options['format'][$_row][$cellName] = $format;
                }


                $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                if (!empty($data[$_row][$cellName])) {
                    $isNull = false;
                }
            }

            /* 判斷是否整行資料為空，是的話刪除該行資料 */
            if ($isNull) {
                unset($data[$_row]);
            }
        }

        return $data;
    } catch (\Exception $e) {
        throw $e;
    }
}
?>


