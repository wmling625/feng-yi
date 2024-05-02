<?php
include_once(dirname(__FILE__) . "/./root.php");
include_once(dirname(__FILE__) . "/./root_global.php");
include_once(dirname(__FILE__) . "/./SimpleImage_areafill.php");
include_once(dirname(__FILE__) . "/./PHPMailer-master/mail_config.php");
include_once(dirname(__FILE__) . "/./php7_excel/PhpSpreadsheet/autoload.php");
include_once(dirname(__FILE__) . "/./page.class.php");
include_once(dirname(__FILE__) . "/./backend_left_array.php");
include_once(dirname(__FILE__) . "/./backend_custom.php");
include_once(dirname(__FILE__) . '/./phpqrcode/vendor/autoload.php');
error_reporting(0);


$filename = basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']); //取得目前檔名


/*下面區塊是[目前檔名]比對選單陣列，取得版型標題、副標題*/
$GLOBALS['template_title'] = "";
$GLOBALS['template_sub'] = "";

foreach ($menu_array as $key1 => $value1) {
    foreach ($menu_array[$key1] as $key2 => $value2) {
        if ($value2["link"] == $filename) {
            //拿瀏覽器檔名去比對選單, 找出主副標題
            $GLOBALS['template_title'] = $value2["title"];
            $GLOBALS['template_sub'] = $key1;
        } else {
            // 如果沒有找到，就先將檔名替換成_list.php，再找標題
            $filename_1 = str_replace("_mang_end.php", "_list.php", $filename);
            //變更瀏覽器檔名去比對選單, 找出主副標題
            if ($value2["link"] == $filename_1) {
                $GLOBALS['template_title'] = $value2["title"];
                $GLOBALS['template_sub'] = $key1;
            } else {
                $filename_2 = str_replace("_mang.php", "_list.php", $filename);
                //變更瀏覽器檔名去比對選單, 找出主副標題
                if ($value2["link"] == $filename_2) {
                    $GLOBALS['template_title'] = $value2["title"];
                    $GLOBALS['template_sub'] = $key1;
                }
            }
        }
    }
}

/*下面是控管管理員是否能進入某幾頁 */


if (strpos($_SERVER['PHP_SELF'], "index") !== false or strpos($_SERVER['PHP_SELF'], "init") !== false or strpos($_SERVER['PHP_SELF'], "logout") !== false or strpos($_SERVER['PHP_SELF'], "sp_") !== false or (strpos($_SERVER['PHP_SELF'], "forgot") !== false)) {
    //只要是 首頁/登入/登出/初始/特殊頁 任一頁都不需要驗證權限

} else {
    // 如果$_SESSION['admin']不存在
    if (!array_key_exists('admin', $_SESSION)) {
        if (basename($_SERVER['PHP_SELF']) != "index.php") {
            echo "<script>alert('停留時間過久，請重新登入')</script>";
            echo "<script>document.location.href = 'logout.php'</script>";
            exit;
        }
    } else {
        if (isset($_SESSION['admin']['admin_id'])) {
            // isValidAccess(); // 檢查是否有其他裝置登入此帳號
            $permission_arr = isset($_SESSION['admin']['permission']) ? json_decode($_SESSION['admin']['permission'], true) : array();
            $special_permission_arr = isset($_SESSION['admin']['special_permission']) ? json_decode($_SESSION['admin']['special_permission'], true) : array();

            /*下面區塊是控管有沒有權限能進入*/
            if (count($permission_arr) == 0) {
                // 陣列長度為0代表不作控管(最大管理員admin)
            } else {
                $passed_array = array(); // 預設就可以適用的頁面，直接跳過
                foreach ($menu_array as $key1 => $value1) {
                    foreach ($menu_array[$key1] as $key2 => $value2) {
                        if ($value2['checked']) {
                            $passed_array[] = $value2["link"];
                        }
                    }
                }

                if (!checkAdminPermission($filename, $passed_array, $permission_arr) && !checkAdminPermission($filename, $passed_array, $special_permission_arr)) {
                    echo "<script>alert('您無權限觀看此頁');</script>";
                    echo "<script> history.go(-1) </script>";
                    exit;
                }
            }

        } else {
            echo "<script>alert('停留時間過久，請重新登入');</script>";
            echo "<script> document.location.href = 'logout.php' </script>";
            exit;
        }
    }
}

function checkAdminPermission($filename = "", $pass_arr = array(), $permission_arr = array())
{
    $passed = true;

    if (!in_array($filename, $pass_arr) && !in_array($filename, $permission_arr)) { // 如果不存在於預設頁、如果目前檔名有在權限value內
        $filename_1 = str_replace("_mang_end.php", "_list.php", $filename);
        if (!in_array($filename_1, $pass_arr) && !in_array($filename_1, $permission_arr)) {
            $filename_2 = str_replace("_mang.php", "_list.php", $filename);
            if (!in_array($filename_2, $pass_arr) && !in_array($filename_2, $permission_arr)) {
                // 這樣好像會有問題 因為如果權限頁面沒有列表頁，就會無法替代，例如: me_mang.php
                $filename_3 = str_replace("_mang_end.php", "_mang.php", $filename);
                if (!in_array($filename_3, $pass_arr) && !in_array($filename_3, $permission_arr)) {
                    $filename_4 = str_replace("_show.php", "_list.php", $filename); // 叫號顯示
                    if (!in_array($filename_4, $pass_arr) && !in_array($filename_4, $permission_arr)) {
                        $passed = false;
                    }
                }
            }
        }
    }

    return $passed;
}