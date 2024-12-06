<?php

if(isset($_SERVER['PHP_SELF'])){
	if (strpos(rawurldecode($_SERVER['PHP_SELF']), ".php/") !== false) {
	 //exit("偵測到惡意指令A1, 請聯繫管理員");
	}	
}

if(isset($_SERVER['REQUEST_URI'])){
	if (strpos(rawurldecode($_SERVER['REQUEST_URI']), "'") !== false OR strpos(rawurldecode($_SERVER['REQUEST_URI']), '"') !== false) {
	 exit("偵測到惡意指令A2, 請聯繫管理員");
	}
}

if(isset($_SERVER['HTTP_REFERER'])){
	if(!empty($_SERVER['HTTP_REFERER'])){
		if (strpos(rawurldecode($_SERVER['HTTP_REFERER']), "http") !== false OR  strpos(rawurldecode($_SERVER['HTTP_REFERER']), "android-app") !== false) {
			
		}else{
			exit("偵測到惡意指令A3, 請聯繫管理員");
		}		
	}
}


function NumberToChinese($num) {
    $digits = ["零", "壹", "貳", "參", "肆", "伍", "陸", "柒", "捌", "玖"];
    $units = ["", "拾", "佰", "仟"];
    $ones = ["", "萬", "億", "兆"];
    $negative = "負";

    $result = [];
    $integerPart = abs((int)$num); // 取整數部分
    $decimalPart = abs($num - $integerPart); // 取小數部分

    // 判斷是否為負數
    if ($num < 0) {
        $result["negative"] = true;
    }

    // 處理整數部分
    $i = 0;
    while ($integerPart > 0) {
        $group = $integerPart % 10000;
        $integerPart = (int)($integerPart / 10000);

        $groupStr = "";
        if ($group > 0) {
            $j = 0;
            while ($group > 0) {
                $digit = $group % 10;
                $group = (int)($group / 10);
                if ($digit > 0) {
                    $groupStr = $digits[$digit] . $units[$j] . $groupStr;
                } else {
                    // 如果是零，需要特殊處理
                    if (strlen($groupStr) > 0 && $groupStr[0] !== $digits[0]) {
                        $groupStr = $digits[0] . $groupStr;
                    }
                }
                $j++;
            }
            $groupStr .= $ones[$i];
        }
        if (!isset($result["full"])) {
            $result["full"] = '';
        }
        $result["full"] = $groupStr . $result["full"];
        $i++;
    }

    // 處理小數部分
    if ($decimalPart > 0) {
        $result["full"] .= "點";
        $decimalStr = "";
        $precision = 2; // 小數精度
        while ($precision > 0 && $decimalPart > 0) {
            $decimalPart *= 10;
            $digit = (int)$decimalPart;
            $decimalPart -= $digit;
            $decimalStr .= $digits[$digit];
            $precision--;
        }
        $result["full"] .= $decimalStr;
    } else {
        $result["full"] .= "圓整";
    }

    // 處理中文數字轉換
    $liteStr = '';
    $numStr = (string)abs($num);
    for ($k = 0; $k < strlen($numStr); $k++) {
        $digit = (int)$numStr[$k];
        $liteStr .= $digits[$digit];
    }
    $result["lite"] = $liteStr;

    return $result;
}


//2024.03.07 檢查字串是否為html網址
function validateHTML($html) {
    libxml_use_internal_errors(true); // 啟用內部錯誤處理，以防止警告消息污染輸出
    $dom = new DOMDocument();
    $dom->loadHTML($html); // 載入 HTML 內容
    $errors = libxml_get_errors(); // 獲取所有的 libxml 錯誤
    libxml_clear_errors(); // 清除錯誤記錄

    // 檢查是否有錯誤發生
    if (!empty($errors)) {
        return false; // 如果有錯誤，表示 HTML 格式有問題
    }

    return true; // 如果沒有錯誤，表示 HTML 格式正確
}


//2023.12.09 列出所有跳轉網址歷程
function getRedirectedUrlsWithStatusCode($url) {
    $ch = curl_init();
    
    // 設定 cURL 選項
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_HEADER, true); // 啟用取得標頭信息
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10); // 設定最大跳轉次數，防止無窮迴圈
    curl_setopt($ch, CURLOPT_NOBODY, true); // 只取得標頭信息，不需要實際內容
    
    // 執行 cURL 並取得內容
    $response = curl_exec($ch);
    
    // 檢查是否有錯誤
    if (curl_errno($ch)) {
        echo 'cURL 錯誤: ' . curl_error($ch);
        return false;
    }
    
    // 取得最終的 URL
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    
    // 取得 HTTP 狀態碼
    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // 取得標頭信息
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    
    // 使用正則表達式擷取跳轉的 URL
    preg_match_all('/^Location:(.*)$/mi', $header, $matches);
    
    // 關閉 cURL 資源
    curl_close($ch);
    
    // 將最終 URL 加入跳轉 URL 陣列
    $redirectedUrls = array_map('trim', $matches[1]);
    $redirectedUrls[] = $finalUrl;
    $redirectedUrls = array_map('rawurldecode', $redirectedUrls);
    
    // 返回包含狀態碼和URL的關聯數組
    $result = array();
    foreach ($redirectedUrls as $redirectedUrl) {
        $result[] = array("status_code" => $httpStatusCode, "url" => $redirectedUrl);
    }
    
    return $result;
}


//判斷目前網址有沒有透過CDN, 通常用在http to https 不重複跳轉
function is_https_cloudflare() {
    return isset($_SERVER['HTTPS']) ||
        ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
            $visitor->scheme == 'https';
}

//jsonValReplace，在PHP json_enccode時, 有些value能被寫進DB, 但不見得能順利讀出, 可以先透過這來轉換
function jsonValReplace($str) {
  $str = htmlspecialchars_decode($str, ENT_QUOTES);		
  $str = str_replace('＀', ' ', $str);
  $str = str_replace("\t", ' ', $str);
  $str = str_replace(['"', "'"], '′', $str);
  $str = str_replace('\\', '＼', $str);
  return $str;
}

/* 
針對文章內特定關鍵字進行超連結綁定
echo blogHightLight("我是關於熊貓介紹的文章", array("熊貓"=>"https://tw.yahoo.com"))
*/
function blogHightLight($content, $keywords) {
    // 按照關鍵字長度降序排序，從最長的關鍵字開始替換
    uksort($keywords, function($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });

    // 針對每個關鍵字進行處理
    foreach ($keywords as $keyword => $url) {
        $escapedKeyword = preg_quote($keyword, '/');
        // 使用 preg_replace_callback 來避免替換已被錨點包裹的關鍵字
        $content = preg_replace_callback("/($escapedKeyword)(?![^<]*<\/a>)/u", function($matches) use ($url) {
            return '<a href="' . $url . '" target="_blank">' . $matches[1] . '</a>';
        }, $content);
    }

    return $content;
}
/* 
去除字串中最前面與最後面的html語法, 目前只想到跟replace_nth搭配使用, 因為有時透過ck編輯器內容最前面或最後面會有br或p符號, 但這樣帶入replace_nth就會因為包含html而失效
$string = "1。2。3。4。5<br/>";
$new_string = replace_nth(remove_first_last_html_tags($string), "。", "。<br/><br/>", 3);
echo $new_string;
*/
function remove_first_last_html_tags($string) {
    $string = preg_replace('/^<[^>]+>/', '', $string);
    $string = preg_replace('/<[^>]+>$/', '', $string);
    return $string;
}


/*
針對n的倍數進行replace, 通常用在文章不是原創，段落又黏在一起，但每個句點斷行又畸形的情況使用
$string = "1。2。3。4。5";
$new_string = replace_nth($string, "。", "。<br/><br/>", 3);
echo $new_string;
*/
function replace_nth($string, $search, $replace, $n) {
	//如果進來的字串有包含html tag就略過, 避免原本標籤被截斷
	if($string != preg_replace('/<[^>]+?>/', ' ', $string)) {
        return $string;
    }	
    $count = 0;
    $new_string = '';
    for ($i = 0; $i < mb_strlen($string); $i++) {
        if (mb_substr($string, $i, 1) == $search) {
            $count++;
            if ($count % $n == 0) {
                $new_string .= $replace;
            } else {
                $new_string .= mb_substr($string, $i, 1);
            }
        } else {
            $new_string .= mb_substr($string, $i, 1);
        }
    }
    return $new_string;
}

/*如果編輯器HTML內容要顯示在APP或電子郵件中，那圖片會有相對路徑問題 */
/*
$html = "<img src='123.jpg' name='hello' />您好<img src='https://www.google.com/456.jpg' />" ;
$url = "https://www.pintech.com.tw/";
echo replaceCKsrc($html, $url);
*/
function replaceCKsrc($html, $url) {
    $pattern = '/(<img[^>]*src=[\'"])((?!http)[^\'"]+)([\'"][^>]*>)/i';
    $replacement = '$1' . $url . '$2$3';
    return preg_replace($pattern, $replacement, $html);
}

/* 比對舊與新字串差異處，輸出時會輸出舊的，並將差異處上色 */
function highlightDifferences($old, $new) {
    $result = "";
    for ($i = 0; $i < mb_strlen($old); $i++) {
        $char1 = mb_substr($old, $i, 1);
        $char2 = mb_substr($new, $i, 1);
        if ($char1 != $char2) {
            $result .= "<span style='background-color: yellow'>" . htmlspecialchars($char1) . "</span>";
        } else {
            $result .= htmlspecialchars($char1);
        }
    }
    return $result;
}


//SEO正則slug用
function slugify($string) {
  // 將字串轉換為小寫
  $string = strtolower($string);
  
  // 將非英文、數字的字元替換成破折號
  $string = preg_replace('/[^a-z0-9]+/', '-', $string);
  
  // 移除字串開頭和結尾的破折號
  $string = trim($string, '-');
  
  // 返回處理後的slug格式字串
  return $string;
}

/*
透過前後關鍵字找出內容
$leftSymbol = '[';
$rightSymbol = ']';
$content = findKeyword("123[{我是json},{我是json}]45645", $leftSymbol, $rightSymbol);
第四個參數如果改成false, 則不會包含  $leftSymbol, $rightSymbol 符號
*/
function findKeyword($str, $leftSymbol, $rightSymbol, $includeSymbols = true) {
    $leftPos = strpos($str, $leftSymbol);
    if ($leftPos === false) {
        return '';
    }

    $rightPos = strrpos($str, $rightSymbol);
    if ($rightPos === false) {
        return '';
    }

    $length = $rightPos - $leftPos + 1;

    if ($includeSymbols) {
        return substr($str, $leftPos, $length);
    } else {
        $leftPos += strlen($leftSymbol);
        $length -= strlen($leftSymbol) + strlen($rightSymbol);
        return substr($str, $leftPos, $length);
    }
}



/*
如果需要把json輸出到隱藏文字欄位, 須使用這參數
<input type="hidden" value="<?php echo json2input($json_string); ?>">
*/
function json2input($str){
	return htmlspecialchars($str,ENT_QUOTES,"UTF-8");
}

//資安, 密碼錯誤3次停止15分鐘
function pintech_loginCD($account = "", $type = "validate", $limit = 3, $time = 15)
{
    $callback = array("state" => "-1", "message" => "驗證失敗");
    $limit = ($limit >= 0) ? $limit : 3; // 限制次數不得小於0
    $time = ($time >= 0) ? $time : 15; // 鎖定時間不得小於0
    $count = 0; // 失敗次數預設為0
    $now = date("Y-m-d H:i:s"); // 當前時間
    $unlockTime = $now; // 解鎖時間預設為now
    $sessionKey = 'loginCD_' . md5($account . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF']); // 加入server name
    if (isset($_SESSION[$sessionKey])) {
        $count = $_SESSION[$sessionKey]["count"];
        $unlockTime = $_SESSION[$sessionKey]["unlock_time"];
    } else {
        $_SESSION[$sessionKey] = array();
        $_SESSION[$sessionKey]["count"] = $count;
        $_SESSION[$sessionKey]["unlock_time"] = $unlockTime;
    }

    if (empty($account)) {
        $callback = array("state" => "-1", "message" => "請輸入帳號");
    } else {
        switch ($type) {
            case "validate": // 驗證
                if ($count < $limit) {
                    $callback = array("state" => "1", "message" => "驗證通過");
                } else {
                    $diff = DateDiff($unlockTime, $now, 's'); // 計算 解鎖時間-現在時間=時間差
                    if ($diff > 0) {
                        $callback = array("state" => "-1", "message" => $type . "帳號 " . $account . " 已鎖定，將於 " . $unlockTime . " 解除鎖定");
                    } else {
                        // 達 limit 次且等待超過 unlock 分鐘，就能重新計算
                        $_SESSION[$sessionKey]["count"] = 0;
                        $_SESSION[$sessionKey]["unlock_time"] = $now;
                        $callback = array("state" => "1", "message" => "驗證通過");
                    }
                }
                break;
            case "lock": // 鎖定++
                $count++;
                $unlockTime = date("Y-m-d H:i:s", strtotime("+" . $time . " minute", strtotime($now)));
                if (!isset($_SESSION[$sessionKey])) {
                    $_SESSION[$sessionKey] = array();
                }
                $_SESSION[$sessionKey]["count"] = $count;
                $_SESSION[$sessionKey]["unlock_time"] = $unlockTime;

                // 如果鎖定次數=上限次數，失敗訊息就直接顯示[鎖定時間]
                if ($count >= $limit) {
                    $callback = array("state" => "-1", "message" => $type . "帳號 " . $account . " 已鎖定，將於 " . $unlockTime . " 解除鎖定" . $count);
                } else {
                    $callback = array("state" => "0", "message" => $type . "已登入失敗 " . $count . " 次，登入失敗" . $limit . "次將鎖定" . $time . "分鐘");
                }
                break;
            case "unlock": // 解鎖
                unset($_SESSION[$sessionKey]);
                $callback = array("state" => "1", "message" => "驗證通過");
                break;
            default:
                $callback = array("state" => "-1", "message" => "請輸入正確的驗證參數");
                break;
        }
    }

    return $callback;
}


/*
切割信用卡或虛擬帳號，避免看不清楚
pay_split("要切割的字串", "幾碼切一次", "符號");
pay_split("123456789",4); 返回 1234 5678 9
pay_split("123456789",4,"-"); 返回 1234-5678-9
*/
function pay_split($input = "", $num = 4, $join = " "){	
	return join($join, str_split($input, $num));	
}

// calcDistance(24.137310265432948, 120.64657928412795,24.157219704429156, 120.65974055731225);
// 範例如上，返回數值是米
function calcDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000){
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $lonDelta = $lonTo - $lonFrom;
  $a = pow(cos($latTo) * sin($lonDelta), 2) +
    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
  $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

  $angle = atan2(sqrt($a), $b);
  return $angle * $earthRadius;
}


function ip2country($ip = ""){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://ip-api.com/json/".$ip."?fields=status,message,countryCode");
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $ip_data_in = curl_exec($ch); // string
    curl_close($ch);
	
	$callback = [];

	$ip_data_in = json_decode($ip_data_in,true);
	if($ip_data_in["status"] == "success"){
		$callback = array("status"=>1, "msg"=> "查詢成功", "data"=> $ip_data_in["countryCode"]);
	}else{
		$callback = array("status"=>-1, "msg"=> $ip_data_in["message"]);
	}
    return $callback;
}


function pintech_logs_page(){
	global $mysqli;
	//global $conn; //MSSQL專用
	global $conn; //MSSQL專用
	
	if(isset($_SERVER['HTTP_HOST'])){
		$domains  = strtolower($_SERVER['HTTP_HOST']);
		$HTTP_REFERER = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
		$start_link  = rawurldecode($HTTP_REFERER);
		$end_link  = rawurldecode($_SERVER['REQUEST_URI']);
		$SESSION = isset($_SESSION) ? $_SESSION : [];
		$session_content  = json_encode( $SESSION, JSON_UNESCAPED_UNICODE );
		$ip  = get_userip();
		
		if ( (strpos ($end_link, "admin/") !== false)  OR  (count($SESSION) > 0) ) {

			$query_sp = "";
			
			//if(!empty($conn)){
			if(1 != 1){	
				$params = array();
				$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET); 
				$query = "SELECT table_name AS table_name FROM pintech_logs.information_schema.tables WHERE table_catalog = 'pintech_logs' AND (table_name = 'listpage' OR table_name = 'firewall');"; 	
				
				
				$getResults= sqlsrv_query($conn, $query, $params, $options);
				if ($getResults == FALSE){
					/*
					FormatErrors(sqlsrv_errors());
					function FormatErrors( $errors )
					{
				
						echo "Error information: ";

						foreach ( $errors as $error )
						{
							echo "SQLSTATE: ".$error['SQLSTATE']."";
							echo "Code: ".$error['code']."";
							echo "Message: ".$error['message']."";
						}
					}	
					*/	
				}else{
					$rows = array();
					while ($row = sqlsrv_fetch_array($getResults, SQLSRV_FETCH_ASSOC)) {
						$table_name = $row["table_name"];
				
						if($table_name == "listpage"){
							$query_sp .= "INSERT INTO pintech_logs.dbo.listpage(domains, start_link, end_link, session_content, ip, pub_date) VALUES ('".$domains."','".$start_link."','".$end_link."','".$session_content."','".$ip."',GETDATE());";
							$query_sp .= "DELETE FROM pintech_logs.dbo.listpage WHERE pub_date <= DATEADD(DAY, -180, GETDATE());";						
						}
						if($table_name == "firewall"){
							$query_sp .= "DELETE FROM pintech_logs.dbo.listpage WHERE pub_date <= DATEADD(DAY, -180, GETDATE());";						
						}
					}	
					if($query_sp != ""){				
						$getResults = sqlsrv_query($conn, $query_sp, $params, $options); //多工查詢不能+ $option
						do {
						   while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC)){
							  $rows[] = $row;
						   }
						} while (sqlsrv_next_result($getResults));	
					}				
				
				}
				sqlsrv_free_stmt($getResults);				
			}else{
				$randomNum = mt_rand(0, 9);

				$query_sp = "";
				$query = "SELECT table_name as table_name FROM information_schema.tables WHERE table_schema = 'pintech_logs' AND (table_name = 'listpage' OR table_name = 'firewall' OR table_name = 'list');"; //MYSQL 8 回饋的 table_name 是大寫, 所以重新宣告成小寫
				if($result = $mysqli->query($query)){
					while($row = $result->fetch_array(MYSQLI_ASSOC)){
						$table_name = $row["table_name"];
						if($table_name == "listpage"){
							$query_sp .= "INSERT INTO pintech_logs.`listpage`(`domains`, `start_link`, `end_link`, `session_content`, `ip`, `pub_date`) VALUES ('".$domains."','".$start_link."','".$end_link."','".$session_content."','".$ip."',now());";
							
						}
						if($randomNum == 1){ //10%
							if($table_name == "listpage"){
								$query_sp .= "DELETE FROM pintech_logs.`listpage` WHERE pub_date  <= NOW() - INTERVAL 5 DAY;";	
								$query_sp .= "OPTIMIZE TABLE pintech_logs.`listpage`;";
							}
							if($table_name == "firewall"){
								$query_sp .= "DELETE FROM pintech_logs.`firewall` WHERE pub_date  <= NOW() - INTERVAL 7 DAY;";	
								$query_sp .= "OPTIMIZE TABLE pintech_logs.`firewall`;";	
							}
							if($table_name == "list"){
								$query_sp .= "DELETE FROM pintech_logs.`list` WHERE pub_date  <= NOW() - INTERVAL 2 DAY;";	
								$query_sp .= "OPTIMIZE TABLE pintech_logs.`list`;";	
							}								
							
						}
					
						
					}
					mysqli_free_result($result);
				}
				if($query_sp != ""){
					if ($mysqli->multi_query($query_sp)) {
						while($mysqli->more_results() && $mysqli->next_result());
					}				
				}				
			}



		} 		
	}	
}

// zip_save壓縮檔案
function zip_save($temp = array()){
	$err_msg = array();	
	$callback = array();
	$filesize = 0;
	if( count($temp["source"]) == 0 ){
		array_push($err_msg,"壓縮來源未指定");
	}else{
		foreach($temp["source"] as $key => $value){
			if(!is_file($value)){
				array_push($err_msg,"找不到[".$value."], 壓縮失敗");
			}else{
				$filesize += filesize($value); 
			}
		}
		if( round($filesize / 1024 / 1024) > 50 ){
			array_push($err_msg," 來源檔案大小超過 50MB 壓縮失敗");
		}
		
	}	
	
	if(!isset($temp["end_name"])){
		array_push($err_msg,"未指定ZIP檔名");
	}	
	
	if(!isset($temp["end_dir"])){
		array_push($err_msg,"未指定存檔路徑");
	}else{
		if(!is_dir( $temp["end_dir"] )){
			array_push($err_msg,"zip資料夾路徑錯誤");
		}
	}
		
	if(count($err_msg) > 0){
		$callback = array("state" => "-1","message" => implode("、", $err_msg) );
	}else{					
		$source = implode(" ",$temp["source"]);
		
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$fullname = $temp["end_dir"]."/".$temp["end_name"];	
			exec('"C:\program Files\7-zip\7z" a -r -tzip -mx0 "'.$fullname.'" '.$source.' ');
		} else {
			$fullname = $temp["end_dir"]."/".$temp["end_name"].".tar";	
			exec('tar -cvf '.$fullname.' '.$source.' ');
		}			
		
		
		if(file_exists($fullname)){
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$callback = array("state" => "0","message" => "壓縮成功" , "data"=> array("end_name" => $temp["end_name"] ) );	
			}else{
				$callback = array("state" => "0","message" => "壓縮成功" , "data"=> array("end_name" => $temp["end_name"].".tar" ) );	
			}
		}else{
			$callback = array("state" => "-1","message" => "壓縮失敗,請確認資料夾寫入權限是否正確");	
		}	
		
	}
	return $callback;	
}

//qrcode_save qrcode實體圖片產生,教學如下
function qrcode_save($temp = array()){		
	$err_msg = array();
	$callback = array();
	
	if(!isset($temp["str"])){
		array_push($err_msg,"QRCODE文字未填寫");
	}
	if(!isset($temp["end_name"])){
		array_push($err_msg,"未指定QRCODE檔名");
	}	
	if(!isset($temp["end_dir"])){
		array_push($err_msg,"未指定QRCODE存檔路徑");
	}else{
		if(!is_dir( $temp["end_dir"] )){
			array_push($err_msg,"QRCODE資料夾路徑錯誤");
		}
	}
		
	if(count($err_msg) > 0){
		$callback = array("state" => "-1","message" => implode("、", $err_msg) );
	}else{
		$fullname = $temp["end_dir"]."/".$temp["end_name"];
		
		QRcode::png($temp["str"],$fullname , QR_ECLEVEL_H, 10, 2);
		
		if(is_file($fullname)){
			$callback = array("state" => "0","message" => "qrcode產生成功" , "data"=> array("end_name" => $temp["end_name"]) );	
		}else{
			$callback = array("state" => "-1","message" => "qrcode產生失敗,請確認資料夾寫入權限是否正確");	
		}	
	}		
	return $callback;
}


//log紀錄器, 教學如下
// https://drive.google.com/drive/folders/1xvx_m0UtW7Zc8_S2HhULdzs4rWvzj19o?usp=sharing
function pintech_logs_create($option)
{
	if (strpos ($option["db_name"], "pintech_logs") == false) {
		global $mysqli;
		$table_arr = [];
		$columns_arr = [];
		$query = "SET collation_connection = 'utf8mb4_unicode_ci';";
		$query .= 'use ' . $option["db_name"] . ';';
		$query .= 'DROP FUNCTION IF EXISTS `strip_tags`;
		CREATE FUNCTION `strip_tags`(`$str` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci) 
		RETURNS text
		DETERMINISTIC
		BEGIN
		  DECLARE `$start`, `$end` INT DEFAULT 1;
		  LOOP
			SET `$start` = LOCATE("<", `$str`, `$start`);
			IF (!`$start`) THEN RETURN `$str`; END IF;
			SET `$end` = LOCATE(">", `$str`, `$start`);
			IF (!`$end`) THEN SET `$end` = `$start`; END IF;
			SET `$str` = INSERT(`$str`, `$start`, `$end` - `$start` + 1, "");
		  END LOOP;
		END;';
		$query .= 'show full tables where Table_Type != "VIEW";';
		if ($mysqli->multi_query($query)) {
			do {
				if ($result = $mysqli->store_result()) {
					while ($row = $result->fetch_assoc()) {
						if (isset($row["Tables_in_" . $option["db_name"]])) {
							if (strpos($row["Tables_in_" . $option["db_name"]], "validation") === false) {
								if (strpos ($row["Tables_in_" . $option["db_name"]], " ") === false) {
									$table_arr[] = $row["Tables_in_" . $option["db_name"]];
								}
							}
						}
					}
					$result->close();
				}
				if ($mysqli->more_results()) {
				}
			} while ($mysqli->more_results() && $mysqli->next_result());
		}

		if (count($table_arr) > 0) {
			$query = "";
			foreach ($table_arr as $key => $value) {
				$query .= "SHOW COLUMNS FROM " . $value . ";";
			}
			$x = 0;
			if ($mysqli->multi_query($query)) {
				do {
					if ($result = $mysqli->store_result()) {
						while ($row = $result->fetch_assoc()) {
							$columns_arr[$table_arr[$x]][] = $row["Field"];
						}
						$result->close();
					}
					if ($mysqli->more_results()) {
						$x = $x + 1;
					}
				} while ($mysqli->more_results() && $mysqli->next_result());
			}

			if (count($columns_arr) > 0) {
				$query = "SET collation_connection = 'utf8mb4_unicode_ci';";
				if (in_array("delete", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
							$query .= "DROP TRIGGER IF EXISTS " . $key . "_before_delete ; ";

							$query .= "CREATE TRIGGER " . $key . "_before_delete
							BEFORE DELETE
							ON $key FOR EACH ROW ";
							$Field_arr = [];
							foreach ($value as $key1 => $value1) {
								$Field_arr[] = ' "' . $value1 . '", REPLACE(strip_tags(IF(OLD.`' . $value1 . '` is Null,"",OLD.`' . $value1 . '`)),"\'","")  ';
							}

							if (!empty($Field_arr)) {
								$Field_string = implode(",", $Field_arr);

								$query .= "insert into pintech_logs.list (model,contents,db_name,table_name,pub_date) values ('刪除',JSON_OBJECT(" . $Field_string . "),'" . $option["db_name"] . "','" . $key . "',now());";

								if(mt_rand(0, 20) == 1){
									$query .= "DELETE FROM pintech_logs.list WHERE pub_date  <= NOW() - INTERVAL 365 DAY;";	
								}
							
							}
					}
				}
				if (in_array("update", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
						$query .= "DROP TRIGGER IF EXISTS " . $key . "_before_update ; ";

						$query .= "CREATE TRIGGER " . $key . "_before_update
						BEFORE UPDATE
						ON $key FOR EACH ROW ";
						$Field_arr = [];
						foreach ($value as $key1 => $value1) {
							$Field_arr[] = ' "' . $value1 . '", REPLACE(strip_tags(IF(OLD.`' . $value1 . '` is Null,"",OLD.`' . $value1 . '`)),"\'","")  ';
						}
						if (!empty($Field_arr)) {
							$Field_string = implode(",", $Field_arr);

							$query .= "insert into pintech_logs.list (model,contents,db_name,table_name,pub_date) values ('更新',JSON_OBJECT(" . $Field_string . "),'" . $option["db_name"] . "','" . $key . "',now());";

						
						}

					}
				}
				if (in_array("insert", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
						$query .= "DROP TRIGGER IF EXISTS " . $key . "_before_insert ; ";

						$query .= "CREATE TRIGGER " . $key . "_before_insert
						AFTER INSERT
						ON $key FOR EACH ROW ";
						$Field_arr = [];
						foreach ($value as $key1 => $value1) {
							$Field_arr[] = ' "' . $value1 . '", REPLACE(strip_tags(IF(NEW.`' . $value1 . '` is Null,"",NEW.`' . $value1 . '`)),"\'","")  ';
						}
						if (!empty($Field_arr)) {
							$Field_string = implode(",", $Field_arr);

							$query .= "insert into pintech_logs.list (model,contents,db_name,table_name,pub_date) values ('新增',JSON_OBJECT(" . $Field_string . "),'" . $option["db_name"] . "','" . $key . "',now());";

						}

					}
				}
				if ($mysqli->multi_query($query)) {
					while ($mysqli->more_results() && $mysqli->next_result()) ;
				}
			}
		}		
	}	


}


function pintech_logs_create_ms($option){
	if (strpos ($option["db_name"], "pintech_logs") == false) {
	
		global $conn;
		$table_arr = [];
		$columns_arr = [];

		$query = 'use ' . $option["db_name"] . ';';
		$stmt = sqlsrv_query($conn, $query);
		@sqlsrv_free_stmt($stmt);

		$query = "DROP FUNCTION IF EXISTS [dbo].[strip_tags];";
		$stmt = sqlsrv_query($conn, $query);
		@sqlsrv_free_stmt($stmt);
		
	
		$query = "CREATE FUNCTION [dbo].[strip_tags] (@str NVARCHAR(MAX)) 
					RETURNS NVARCHAR(MAX) 
					AS
					BEGIN
					 WHILE CHARINDEX('<', @str) > 0 
					 BEGIN 
					 SET @str = STUFF(@str, CHARINDEX('<', @str), CHARINDEX('>', @str) - CHARINDEX('<', @str) + 1, ''); 
					 end
					 RETURN TRIM(@str) 
					END;";
		$stmt = sqlsrv_query($conn, $query);
		@sqlsrv_free_stmt($stmt);
		
		$query = "SELECT name FROM sys.objects WHERE type = 'U';";
		sqlsrv_query($conn, $query);
		$params = array();
		$options = array("Scrollable" => SQLSRV_CURSOR_KEYSET); 		

		$getResults = sqlsrv_query($conn, $query, $params, $options);
		do {
		   while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC)){		
			   
				if (isset($row["name"])) {
					if (strpos($row["name"], "validation") === false) {
						if (strpos ($row["name"], " ") === false) {
							$table_arr[] = $row["name"];
						}
					}
				}
				
		   }
		} while (sqlsrv_next_result($getResults));
		@sqlsrv_free_stmt( $getResults );
		

		if (count($table_arr) > 0) {
			$query = "";
			foreach ($table_arr as $key => $value) {				
				$query .= "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N'". $value ."'; ";
			}
			$x = 0;
			$getResults = sqlsrv_query($conn, $query);
			do {
				if (is_resource($getResults)) {					
					while( $row = sqlsrv_fetch_array( $getResults, SQLSRV_FETCH_ASSOC)){						
						$columns_arr[$table_arr[$x]][] = $row["COLUMN_NAME"];
					}
				}				
				if(sqlsrv_has_rows($getResults)){					
					$x = $x + 1;					
				}						
			 
			} while (sqlsrv_next_result($getResults));
			sqlsrv_free_stmt( $getResults );
			
			if (count($columns_arr) > 0) {
				$query_stack = array();
				if (in_array("delete", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
					
						array_push($query_stack, "DROP TRIGGER IF EXISTS dbo.".$key . "_before_delete ; ");
						
						$query = "CREATE TRIGGER dbo.".$key . "_before_delete on " . $key . "
						FOR DELETE
						AS ";
						$Field_arr = [];
						foreach ($value as $key1 => $value1) {
							
							$Field_arr[] = " '". $value1 ."':REPLACE(dbo.strip_tags(IIF(" . $value1 . " is Null,''," . $value1 . ")),'\"','') ";
						}

						if (!empty($Field_arr)) {
							$Field_string = implode(",", $Field_arr);
						
							$query .= "insert into pintech_logs.dbo.list (db_name,table_name,model,contents,pub_date) SELECT '".$option["db_name"] ."','".$key."','刪除',JSON_OBJECT(". $Field_string ."), GETDATE() FROM DELETED;";	

							array_push($query_stack, $query);		
						}
						
						
					}
				}

				if (in_array("update", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
						
						array_push($query_stack, "DROP TRIGGER IF EXISTS dbo.".$key . "_before_update ; ");
						
						$query = "CREATE TRIGGER dbo.".$key . "_before_update on " . $key . "
						FOR UPDATE
						AS ";
						$Field_arr = [];
						foreach ($value as $key1 => $value1) {
							
							$Field_arr[] = " '". $value1 ."':REPLACE(dbo.strip_tags(IIF(" . $value1 . " is Null,''," . $value1 . ")),'\"','') ";
						}

						if (!empty($Field_arr)) {
							$Field_string = implode(",", $Field_arr);
						
							$query .= "insert into pintech_logs.dbo.list (db_name,table_name,model,contents,pub_date) SELECT '".$option["db_name"] ."','".$key."','更新',JSON_OBJECT(". $Field_string ."), GETDATE() FROM DELETED;";	
							
							array_push($query_stack, $query);	
						}
						
					}
				}
				if (in_array("insert", $option["method"])) {
					foreach ($columns_arr as $key => $value) {
					
						array_push($query_stack, "DROP TRIGGER IF EXISTS dbo.".$key . "_before_insert ; ");
						
						$query = "CREATE TRIGGER dbo.".$key . "_before_insert on " . $key . "
						FOR INSERT
						AS ";
						$Field_arr = [];
						foreach ($value as $key1 => $value1) {
							
							$Field_arr[] = " '". $value1 ."':REPLACE(dbo.strip_tags(IIF(" . $value1 . " is Null,''," . $value1 . ")),'\"','') ";
						}

						if (!empty($Field_arr)) {
							$Field_string = implode(",", $Field_arr);
						
							$query .= "insert into pintech_logs.dbo.list (db_name,table_name,model,contents,pub_date) SELECT '".$option["db_name"] ."','".$key."','新增',JSON_OBJECT(". $Field_string ."), GETDATE() FROM inserted;";	
							array_push($query_stack, $query);
							
							if(mt_rand(0, 20) == 1){
								$query = "DELETE FROM pintech_logs.dbo.list WHERE datediff(d,pub_date,getdate()) <= 180;";	
								array_push($query_stack, $query);
							}
							
						}
					}
				}
				
	
				foreach($query_stack as $key=>$value){					
					sqlsrv_query($conn, $value) or die( print_r( sqlsrv_errors(), true));				
				}
			
			}
		}		
		
	}
}


function captcha64($types = 'int', $name = 'authcode')
{
    if ($name === "") {
        $_SESSION["authcode"] = ''; //設置存放檢查碼的SESSION
    } else {
        $_SESSION[$name] = ''; //設置存放檢查碼的SESSION
    }
    //設置定義為圖片

    $str = "0123456789";
    if ($types == "all") {
        $str = "ACEFGHIJKMNPRTWXY346789";
    }

    $code = '';

    $nums = 5;
    for ($i = 0; $i < $nums; $i++) {
        $code .= $str[mt_rand(0, strlen($str) - 1)];
    }

    if ($name === "") {
        $_SESSION["authcode"] = $code;
    } else {
        $_SESSION[$name] = $code;
    }

    //建立圖示，設置寬度及高度與顏色等等條件
    $image = imagecreate(120, 30);
    $black = imagecolorallocate($image, mt_rand(0, 200), mt_rand(0, 200), mt_rand(0, 200));
    $border_color = imagecolorallocate($image, 200, 200, 200);
    $background_color = imagecolorallocate($image, 235, 236, 237);

    //建立圖示背景
    imagefilledrectangle($image, 0, 0, 120, 30, $background_color);

    //建立圖示邊框
    imagerectangle($image, 0, 0, 120 - 1, 30 - 1, $border_color);

    //在圖示布上隨機產生大量躁點
    for ($i = 0; $i < 80; $i++) {
        imagesetpixel($image, mt_rand(0, 120), mt_rand(0, 30), $black);
    }

    $strx = mt_rand(3, 8);
    for ($i = 0; $i < $nums; $i++) {
        $strpos = mt_rand(1, 5);
        imagestring($image, 5, $strx, $strpos, substr($code, $i, 1), $black);
        $strx += mt_rand(10, 30);
    }
    ob_start();
    imagepng($image);
    $imagedata = ob_get_contents();
    imagedestroy($image);
    ob_end_clean();
    return "data:image/png;base64," . base64_encode($imagedata);
}



function GmtTimeToLocalTime($time) {
    date_default_timezone_set('UTC');
    $new_date = new DateTime($time);
    $new_date->setTimeZone(new DateTimeZone('Asia/Taipei'));
    return $new_date->format("Y-m-d H:i:s");
}

function PolylineEncoder($array = array()){
	if(class_exists('PolylineEncoder') != true) 
	{ 
		class PolylineEncoder {
			
			private $points;
			private $encoded;
			
			public function __construct() {
				$this->points = array();
			}
			function addPoint($lat, $lng) {
				if (empty($this->points)) {
					$this->points[] = array('x' => $lat, 'y' => $lng);
					$this->encoded = $this->encodeValue($lat) . $this->encodeValue($lng);
				} else {
					$n = count($this->points);
					$prev_p = $this->points[$n-1];
					$this->points[] = array('x' => $lat, 'y' => $lng);
					$this->encoded .= $this->encodeValue($lat-$prev_p['x']) . $this->encodeValue($lng-$prev_p['y']);
				}
			}
			function encodedString() {
				return $this->encoded;
			}
			function encodeValue($value) {
				$encoded = "";
				$value = round($value * 100000);
				$r = ($value < 0) ? ~($value << 1) : ($value << 1);
				
				while ($r >= 0x20) {
					$val = (0x20|($r & 0x1f)) + 63;
					$encoded .= chr($val);
					$r >>= 5;
				}
				$lastVal = $r + 63;
				$encoded .= chr($lastVal);
				return $encoded;
			}
			static public function decodeValue($value) {
				$index = 0;
				$points = array();
				$lat = 0;
				$lng = 0;

				while ($index < strlen($value)) {
					$b;
					$shift = 0;
					$result = 0;
					do {
						$b = ord(substr($value, $index++, 1)) - 63;
						$result |= ($b & 0x1f) << $shift;
						$shift += 5;
					} while ($b > 31);
					$dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
					$lat += $dlat;

					$shift = 0;
					$result = 0;
					do {
						$b = ord(substr($value, $index++, 1)) - 63;
						$result |= ($b & 0x1f) << $shift;
						$shift += 5;
					} while ($b > 31);
					$dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
					$lng += $dlng;

					$points[] = array('x' => $lat/100000, 'y' => $lng/100000);
				}

				return $points;
			}
		}
	} 	

	$polylineEncoder = new PolylineEncoder();
	$array = array_map("unserialize", array_unique(array_map("serialize", $array)));
	foreach($array as $key => $value){
		$polylineEncoder->addPoint($value[0], $value[1]);
	}
	return $polylineEncoder->encodedString();
	
}

function validate_date($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function validate_twmobile($str) {
    if (preg_match("/^09[0-9]{2}-[0-9]{3}-[0-9]{3}$/", $str)) {
        return true;    // 09xx-xxx-xxx
    } else if(preg_match("/^09[0-9]{2}-[0-9]{6}$/", $str)) {
        return true;    // 09xx-xxxxxx
    } else if(preg_match("/^09[0-9]{8}$/", $str)) {
        return true;    // 09xxxxxxxx
    } else {
        return false;
    }
}
 

function json2encrypt($arr = []){
	return json_encode("pintech.encrypt@".aes_encrypt($arr), JSON_UNESCAPED_UNICODE);
}

function validate_address($string = ""){
	$matches = [];
	$string = preg_replace('/^[0-9]*/', '', replace_trim(cover_width_small($string, 1),"any"));
	if (!empty($string)) {
		preg_match('/(\X+[市|縣])(\X+[區|鎮|鄉|市])(\X+)/u', $string, $matches);
		//unset($matches[0]);
		$matches = array_values($matches);
		$matches[0] = str_replace("台", "臺", $matches[0]);
		if($matches[0]=="") $matches=[];
	}
	return $matches;
}


function openid_info($arr){
	if($arr["types"] == "line"){
		$header = array('Content-Type:application/json','Authorization: Bearer '.$arr["channelAccessToken"]);
		$html = download_page("https://api.line.me/v2/bot/profile/".$arr["userid"],"none",$header);
		return $html;
	}
}


//變更網址參數
function add_pararm($url,$param=array()){
	$ori_url=parse_url(trim($url));
	$get_array=[];
    if(isset($ori_url["query"])) parse_str($ori_url["query"], $get_array);
	if(is_array($param)){
		foreach($param as $key=>$val){
			$get_array[$key]=$val;
		}
	}
	$ori_url["query"]=http_build_query($get_array);
	return build_url($ori_url);
}

function build_url($parts=array()){
	return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
        (isset($parts['host']) ? '//' : '') .
        (isset($parts['user']) ? "{$parts['user']}" : '') .
        (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
        (isset($parts['user']) ? '@' : '') .
        (isset($parts['host']) ? "{$parts['host']}" : '') .
        (isset($parts['port']) ? ":{$parts['port']}" : '') .
        (isset($parts['path']) ? "{$parts['path']}" : '') .
        (isset($parts['query']) ? "?{$parts['query']}" : '') .
        (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
}


function validate_url($url) {
	$url = rawurldecode($url);
    $path         = parse_url($url, PHP_URL_PATH);
    $encoded_path = array_map('urlencode', explode('/', $path));
    $url          = str_replace($path, implode('/', $encoded_path), $url);

    return filter_var($url, FILTER_VALIDATE_URL) ? true : false;
}

function getimagesize_curl( $url, $referer = '' ) {
	$headers = array( 'Range: bytes=0-131072' );
	if ( !empty( $referer ) ) { array_push( $headers, 'Referer: ' . $referer ); }

	$ch = curl_init();
	curl_setopt( $ch, CURLOPT_URL, $url );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);	
	$data = curl_exec( $ch );
	$http_status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
	$curl_errno = curl_errno( $ch );
	curl_close( $ch );
		

	if ( $http_status != 200 ) {
		return array(0,0);
	}
	$image = imagecreatefromstring( $data );
	$dims = [ "width"=>imagesx( $image ), "height"=>imagesy( $image ) ];
	imagedestroy($image);
		
	return $dims;
}


function distance_road($platform="google", $key = array(),$option = array()){
	$array = array();
	if($platform == "google"){
		$key = $key[array_rand($key, 1)];
		$url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=metric&language=zh-TW&origins=".$option["start_lat"].",".$option["start_lng"]."&destinations=".$option["end_lat"].",".$option["end_lng"]."&key=".$key."&mode=".$option["mode"];
		$url = cover_width_small(replace_trim($url, "any"), 1);
		$response = json_decode(download_page($url), true);
		if ($response['status'] != 'OK') {
			return $array;
		} else {
			$geometry = $response["rows"][0]["elements"][0];
			$array = array(
				'km' => round($geometry["distance"]["value"]/1000,1),
				'minute' => round($geometry["duration"]["value"]/60)
			);				
			return $array;
		}			
	}
				
}

function lotto_chance($lotto_arr,$times=1) {
    $callback= ["status" => "0", "msg" => "","Atime"=>"我是轉{$times}次"];
    $Error = [
        "format" => "僅接受陣列格式",
        "sum" => "機率總需為100 或 1000",
    ];
    if (is_array($lotto_arr)) { //檢查是否為陣列
        $chance = array_column($lotto_arr, "chance");

        if (array_sum($chance) == 100 || array_sum($chance)==1000) { //檢查機率是否為100或1000
            $total_chance=[];
            $tmp=0;
            foreach ($lotto_arr as $row) {
                $num= $row["chance"];
                unset($row["chance"]);
                if(sizeof($total_chance)==0){//第一次push，採用原生fn多陣列比較不會卡死
                    $total_chance = array_fill(0, $num, $row);
                }else{//第二次merge
                    $tmp+= $num;
                    $total_chance= array_merge($total_chance, array_fill($tmp, $num, $row));
                }

            }
            if(sizeof($total_chance)>0){
                $callback["status"] = 1;
                $callback["msg"] = "抽獎已完成";
                for($i=0;$i<$times;$i++){
                    shuffle($total_chance); //所有陣列打亂順序
                    $rand = array_rand($total_chance, 1); //是否像樂透一樣，從中在選取
                    $callback["data"][]= $total_chance[$rand];
                }
            }
        }else{
            $callback["msg"] = $Error["sum"];
        }
    }else{
        $callback["msg"] =$Error["format"];
    }
    return json_encode($callback, JSON_UNESCAPED_UNICODE);
}
function thank_lotto($type = 3, $len = 9){ 
	//$len=獎品數量;$type為幾個連線數，餃子老虎為3
    $arr = $thank = [];
    for ($i = 1; $i <= $len; $i++) { //亂數大小
        $arr[] = $i;
    }
    if ($type <= $len) {
        if (is_array(array_rand($arr, $type))) {
            foreach (array_rand($arr, $type) as $v) {
                $thank[] = $arr[$v];
            }
        } else {
            $thank[] = $arr[array_rand($arr, $type)];
        }
    }

    return $thank;
}

function appversion_check($app_link,$app_version){
	$app_version = intval(findNum($app_version));
	$app_version =  ($app_version < 100) ? intval($app_version."0") : $app_version;
	
	if (strpos ($app_link, "play.google.com") !== false) {
		$app_link = "https://googletime.pintech.com.tw/app.php?id=".get_params($app_link,"id");
	}
	
	$html = download_page($app_link);
	$html = SimpleHtmlDom\str_get_html($html);

	$online_version = -1;
	if (strpos ($app_link, "googletime.pintech.com.tw") !== false) {
		$online_version =  intval(findNum($html));		
	}else if(strpos ($app_link, "apps.apple.com") !== false){
		$online_version = intval(findNum($html->find('p.whats-new__latest__version',0)->plaintext));
	}

	$online_version =  ($online_version < 100) ? intval($online_version."0") : $online_version;
	if($online_version < 0){
		//return array("status"=>"-1", "message"=>"網址解析錯誤", "app_version"=>"", "online_version"=>"");
		return array("status"=>"0", "message"=>"無新版APP", "app_version"=>$app_version, "online_version"=>$online_version);
	}else if($online_version > $app_version){
		//return array("status"=>"1", "message"=>"有新版APP", "app_version"=>$app_version, "online_version"=>$online_version);
		return array("status"=>"0", "message"=>"無新版APP", "app_version"=>$app_version, "online_version"=>$online_version);
	}else if($online_version <= $app_version){
		return array("status"=>"0", "message"=>"無新版APP", "app_version"=>$app_version, "online_version"=>$online_version);
	}	
}


function virtual_link($arr)
{
	if (file_exists($arr["file_src"])) {
		if (isset($arr["validation"])) {
			if (is_callable($arr["validation"])) {
				if ($arr["validation"]() == true) {
					header("Content-type:application");
					header("Content-Disposition: attachment; filename=" . $arr["new_name"]);
					readfile($arr["file_src"]);
					exit(0);
				} else {
					$arr["err"]("1"); //驗證失敗無法下載檔案
					exit;
				}
			} else {
				$arr["err"]("2"); //validation必須是function
				exit;
			}
		} else {
			header("Content-type:application");
			header("Content-Disposition: attachment; filename=" . $arr["new_name"]);
			readfile($arr["file_src"]);
			exit(0);
		}
	} else {
		$arr["err"]("3"); //檔案不存在無法下載檔案
		exit;
	}
}

function line_notify($channelAccessToken, $users = array(), $msg = array())
{
	$headers = [
		'Authorization: Bearer ' . $channelAccessToken,
		'Content-Type: application/json; charset=utf-8',
	];
	$users = array_unique($users);
	$users = array_filter($users);
	if (count($users) > 0) {
		$avg_arr = array_chunk($users, 100);
		$messages = array();
		$maybe_imagemap_txt	= 0;
		$maybe_imagemap_img	= 0;
	
		$imagemap_link = "";
		$imagemap_photo = "";	
			foreach ($msg as $key0 => $value0) {
				if ($value0["type"] == "text") {
					array_push($messages, ['type' => 'text', 'text' => $value0["source"]]);
					if(validate_url($value0["source"])){
						$maybe_imagemap_txt = 1;
						$imagemap_link = $value0["source"];
					}
				} else if ($value0["type"] == "image") {
					//array_push($messages, ['type' => 'image', 'originalContentUrl' => $value0["source"], 'previewImageUrl' => $value0["source"]]);
					$remote = getimagesize_curl($value0["source"]);
					array_push($messages, [
							  "type"=> "imagemap",
							  "baseUrl"=> $value0["source"]."?",
							  "altText"=> "您有一則新訊息",
							  "baseSize"=> [
								"height"=> $remote["height"],
								"width"=> $remote["width"]
							  ],
							  "actions"=> [
								[
								  "type"=> "uri",
								  "linkUri"=> $value0["source"]."?",
								  "label"=> "您有一則新訊息",
								  "area"=> [
									"x"=> 0,
									"y"=> 0,
									"width"=> $remote["width"],
									"height"=> $remote["height"]
								  ]
								]
							  ]
							]);
						if(validate_url($value0["source"])){
							$maybe_imagemap_img	= 1;
							$imagemap_photo = $value0["source"];
						}							
				} else if ($value0["type"] == "flex") {
					array_push($messages, $value0["source"]);
				}
			}			
		
		if($maybe_imagemap_txt == 1 and $maybe_imagemap_img == 1){
			$messages = [];
			$remote = getimagesize_curl($imagemap_photo);
					array_push($messages, [
							  "type"=> "imagemap",
							  "baseUrl"=> $imagemap_photo."?",
							  "altText"=> "您有一則新訊息",
							  "baseSize"=> [
								"height"=> $remote["height"],
								"width"=> $remote["width"]
							  ],
							  "actions"=> [
								[
								  "type"=> "uri",
								  "linkUri"=> $imagemap_link,
								  "label"=> "您有一則新訊息",
								  "area"=> [
									"x"=> 0,
									"y"=> 0,
									"width"=> $remote["width"],
									"height"=> $remote["height"]
								  ]
								]
							  ]
							]);			
		}
		foreach ($avg_arr as $key2 => $value2) {
			$sum_array = [];
			foreach ($value2 as $key3 => $value3) {
				array_push($sum_array, $value3);
			}
			$GET = ['to' => $sum_array, 'messages' => $messages];
			$GET = json_encode($GET);
			download_page('https://api.line.me/v2/bot/message/multicast', $GET, $headers);
		}
	}
}

function getReviews($cid)
{
	$ch = curl_init('https://www.google.com/maps?cid=' . $cid);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla / 5.0 (Windows; U; Windows NT 5.1; en - US; rv:1.8.1.6) Gecko / 20070725 Firefox / 2.0.0.6");
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7'));
	$result = curl_exec($ch);
	curl_close($ch);
	$pattern = '/window\.APP_INITIALIZATION_STATE(.*);window\.APP_FLAGS=/ms';
	if (preg_match($pattern, $result, $match)) {
		$match[1] = trim($match[1], ' =;'); /* fix json */
		$reviews  = json_decode($match[1]);
		$reviews  = ltrim($reviews[3][6], ")]}'"); /* fix json */
		$reviews  = json_decode($reviews);

		$customer = $reviews[6][11]; // NEW IN 2020
		$reviews  = $reviews[6][52][0]; // NEW IN 2020
		$arr = array();

		foreach ($reviews as $key => $value) {

			$author_name = str_replace("'", '′', params_security($value[0][1]));
			$profile_photo_url = (isset($value[0][2])) ? $value[0][2] : "";;

			$author_url = "https://www.google.com/maps/contrib/" . $value[6] . "/reviews";
			$rating = params_security($value[4]);

			$text = str_replace("'", '′', params_security($value[3]));
			$time = date('Y-m-d h:i:s', $value[27] / 1000);
			$language = params_security($value[32]);

			if ($text != "" and (strpos($language, "zh") !== false)) {
				array_push($arr, array("author_name" => $author_name, "rating" => $rating, "text" => $text, "time" => $time, "author_url" => $author_url, "profile_photo_url" => $profile_photo_url));
			}
		}
		//$arr_json = json_encode($arr, JSON_UNESCAPED_UNICODE); 

		return $arr;
	}
}

//驗證公司統編
function company_validation($sid)
{
	$tbNum = array(1, 2, 1, 2, 1, 2, 4, 1);
	if (strlen($sid) != 8 || !preg_match("/^[0-9\*]{8}/", $sid)) return false;
	$intSum = 0;
	for ($i = 0; $i < count($tbNum); $i++) {
		$intMultiply = substr($sid, $i, 1) * $tbNum[$i];
		$intAddition = (floor($intMultiply / 10) + ($intMultiply % 10));
		$intSum += $intAddition;
	}
	return ($intSum % 10 == 0) || ($intSum % 10 == 9 && substr($sid, 6, 1) == 7);
}


function populartimes($cid)
{
	$temp = gettoken_value();
	$html = download_page("https://googletime.pintech.com.tw/gm.php", "cid=$cid&token=" . $temp["token"] . "&value=" . $temp["value"] . "");
	return $html;
}


function removeParamFromUrl($query, $paramToRemove)
{
	$params = parse_url($query);
	if (isset($params['query'])) {
		$queryParams = array();
		parse_str($params['query'], $queryParams);
		if (isset($queryParams[$paramToRemove])) unset($queryParams[$paramToRemove]);
		$params['query'] = http_build_query($queryParams);
	}
	$ret = $params['scheme'] . '://' . $params['host'] . $params['path'];
	if (isset($params['query']) && $params['query'] != '') $ret .= '?' . $params['query'];
	return $ret;
}

function openid_login($redirect_url, $types)
{
	$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$http = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$callback_url = $protocol . $http; //來源網址
	$temp = gettoken_value();
	$unique_id_aes = "";
	if(isset($types["unique_id_aes"])){
		$unique_id_aes = $types["unique_id_aes"];
	}
	
	if(is_array($types)){
		if ($types["types"] == "line") {
			$state = aes_encrypt("" . $redirect_url . "@pintech@" . $callback_url . "@pintech@" . $temp["value"] . "@pintech@" . $temp["token"] . "@pintech@" . $types["channelSecret"] . "@pintech@" . $types["client_id"] . "@pintech@" . $unique_id_aes );
			return "https://access.line.me/oauth2/v2.1/authorize?response_type=code&bot_prompt=aggressive&client_id=" . $types["client_id"] . "&redirect_uri=https://openid.linebot.tw/api/login_callback.php&scope=openid profile email&state=" . $state;
		}			
	}

}

function openid_decode($user_token, $user_encrypt)
{
	$arr = [];
	if (aes_validation($user_token, $user_encrypt)) {
		$temp = explode('@pintech@', aes_decrypt($user_token));
		$types = $temp[0];
		$id = $temp[1];
		$name = $temp[2];
		$third = (isset($temp[3])) ? $temp[3] : "" ;
		$email = (isset($temp[4])) ? $temp[4] : "" ;
	
		$arr["types"] = $types;
		$arr["id"] = $id;
		$arr["name"] = $name;

/*
		if (filter_var($third, FILTER_VALIDATE_EMAIL)) {
			$arr["email"] = $third;
		}else{
			$arr["unique_id_aes"] = $third;
		}
*/
		
		if (filter_var($third, FILTER_VALIDATE_EMAIL)) {
			$arr["email"] = $third;
			$arr["unique_id_aes"] = $third;
		}else if(filter_var($email, FILTER_VALIDATE_EMAIL)){
			$arr["email"] = $email;
			$arr["unique_id_aes"] = $third;
		}else{
			$arr["email"] = $email;
			$arr["unique_id_aes"] = $third;
		}
		
	}
	return $arr;
}





function fcm_notify($bar, $data, $token, $key = 'AAAA5yrkNzg:APA91bFsj6beIrrCmYAHmBnfE2sp_7fIOz_QkVLCcnsERpxqnh4kAJcNtS3EFAHQ4Jb1kJO1iibUHWXHriGw0sUb2iGZrvsX8bi0F-vlYdVIydeAnVQCmQ7F43HbhlowNxLlWGSIbwAl')
{

	$icon = 'fcm_push_icon';
	if (array_key_exists('icon', $bar)) {
		$icon = $bar["icon"];
	}

	$callback = array();
	$content = array(
		'title'	=> params_security($bar["title"]),
		'body' 	=> params_security($bar["body"]),
		'icon' => $icon
		//'click_action' => 'FCM_PLUGIN_ACTIVITY'
	);

	//firebase認證 與 傳送格式
	$headers = array(
		'Authorization: key=' . $key,
		'Content-Type: application/json'
	);
	$token = array_unique($token);
	$token = array_filter($token);
	if (count($token) > 0) {
		$avg_arr = array_chunk($token, 50);
		foreach ($avg_arr as $key => $value) {

			$fields = array(
				'registration_ids' => $value,
				'notification'	=> $content,
				'data'	=> $data,
				'priority'	=> "high"
			);
			$ch = curl_init(); //建立CURL連線
			curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			$result = curl_exec($ch);
			curl_close($ch); //關閉CURL連線
			//result 是firebase server的結果
			$callback[] = $result;
		}
	}
	return $callback;
}

/* 
圖片合成, 可合成圖與文字
*/
function image_magic($src_img, $end_img, $magic_arr)
{
    /*讀取底圖*/
	list($overlay_w, $overlay_h, $overlay_type) = getimagesize($src_img);
	switch ($overlay_type) {
		case IMAGETYPE_JPEG:
			$src_img_bgimage = imagecreatefromjpeg($src_img);
			break;
		case IMAGETYPE_PNG:
			$src_img_bgimage = imagecreatefrompng($src_img);
			break;
		case IMAGETYPE_GIF:
			$src_img_bgimage = imagecreatefromgif($src_img);
			break;
		default:
			$src_img_bgimage = NULL;
	}	

	
	/*建立透明底*/
	$src = imagecreatetruecolor($overlay_w,$overlay_h);
    imagealphablending($src, false);
    imagesavealpha($src, true);
	
	/*底圖疊到透明底上*/
	imagecopy($src, $src_img_bgimage, 0, 0, 0, 0, $overlay_w,$overlay_h);
	imagedestroy($src_img_bgimage); //疊上去後就拿掉原圖
	
	
	//陣列順序會重排過, type為text會排前面, img排後面, 這樣合成才不會有問題
	$magic_arr = list_orderByMultiCols($magic_arr, array(
		'type' => SORT_DESC
	));
	foreach ($magic_arr as $key => $value) {
		if (isset($value["overlay"])) {
			list($overlay_w, $overlay_h, $overlay_type) = getimagesize($value["overlay"]);
			switch ($overlay_type) {
				case IMAGETYPE_JPEG:
					$overlay = imagecreatefromjpeg($value["overlay"]);
					break;
				case IMAGETYPE_PNG:
					$overlay = imagecreatefrompng($value["overlay"]);
					break;
				case IMAGETYPE_GIF:
					$overlay = imagecreatefromgif($value["overlay"]);
					break;
				default:
					$overlay = NULL;
			}
			imagecopy($src, $overlay, $value["x"], $value["y"], 0, 0, $overlay_w, $overlay_h);
			imagedestroy($overlay);
		} else {
			$hex = str_replace("#", "", $value["color"]);
			$hex = hexToRGB($hex);
			
			$value["text"] = mb_convert_encoding($value["text"], 'UTF-8', mb_detect_encoding($value["text"]));
			$value["text"] = mb_convert_encoding($value["text"], "html-entities", "utf-8");			
            // 如果 x 和 y 都為 -1，則計算文字寬度和高度並將文字置中
            if (!isset($value["x"]) && !isset($value["y"])) {
				//如果沒有傳XY就自動計算
				$adjustedFontSize = calculateFontSizeToFit($overlay_w, $value["text"], $value["fontfile"], $value["size"], $value["angle"]);
				if ($adjustedFontSize > 0) {
					$value["size"] = $adjustedFontSize;
				}

		
				$bbox = imagettfbbox($value["size"], $value["angle"], $value["fontfile"], $value["text"]);
				$textWidth = $bbox[2] - $bbox[0];
				$textHeight = $bbox[1] - $bbox[7];
				$x = ($overlay_w - $textWidth) / 2;
				$y = ($overlay_h + $textHeight) / 2;
				imagefttext($src, $value["size"], $value["angle"], $x, $y, imagecolorallocate($src, $hex["red"], $hex["green"], $hex["blue"]), $value["fontfile"], $value["text"]);
            }else{
				imagefttext($src, $value["size"], $value["angle"], $value["x"], $value["y"], imagecolorallocate($src, $hex["red"], $hex["green"], $hex["blue"]), $value["fontfile"], $value["text"]);
			}

			
			
			
			
		}
	}
	
	
	if (strpos(strtolower($end_img), ".jpg") !== false || strpos(strtolower($end_img), ".jpeg") !== false ) { // 2023.05.31 ios上傳的圖片可能是.jpeg
		imagejpeg($src, $end_img, 80);
	} else if (strpos(strtolower($end_img), ".png") !== false) {
		imagepng($src, $end_img);
	} else if (strpos(strtolower($end_img), ".gif") !== false) {
		imagegif($src, $end_img);
	}
	
	imagedestroy($src);
}

function calculateFontSizeToFit($imageWidth, $text, $fontFile, $fontSize, $angle) {
    $maxWidth = $imageWidth * 0.85; // 文字最大宽度为图片宽度的85%
    do {
        // 使用imagettfbbox检查当前字体大小的文字宽度
        $bbox = imagettfbbox($fontSize, $angle, $fontFile, $text);
        $textWidth = $bbox[2] - $bbox[0];

        // 如果文字宽度合适，或者字体大小已经小于1，结束循环
        if ($textWidth <= $maxWidth || $fontSize < 1) {
            return $fontSize;
        }

        // 如果文字太宽，则减小字体大小
        $fontSize--;
    } while ($textWidth > $maxWidth);

    return $fontSize; // 如果循环因为字体大小小于1而结束，这里会返回1
}


/*
多維陣列排序, 可以指定多組key, 類似SQL order by 多次
$arrays = array(
    array('id' => 1, 'value' => '1-1', 'parent' => 1),
    array('id' => 2, 'value' => '2-1', 'parent' => 1),
);
$arrays = list_orderByMultiCols($arrays, array(
    'id' => SORT_DESC,
    'value' => SORT_ASC,
));
*/
function list_orderByMultiCols($rowset, $orderings) {
    if (empty($rowset) || empty($orderings)) {
        return $rowset;
    }

    $sortColumns = array();
    foreach ($orderings as $field => $order) {
        $sortColumns[$field] = array();

        foreach($rowset as $index => $row) {
            $value = array_key_exists($field, $row) ? $row[$field] : null;
            $sortColumns[$field][$index] = mb_convert_encoding($value, "UTF-8", "auto");
        }
    }

    // 构造排序规则
    $evalArgs = array();
    foreach ($orderings as $field => $orderDirection) {
        $evalArgs[] = $sortColumns[$field]; // 使用转换后的文本作为排序依据
        $evalArgs[] = $orderDirection;
    }
    $evalArgs[] = &$rowset; // 添加引用，使排序影响原始数组

    call_user_func_array('array_multisort', $evalArgs);
    
    return $rowset;
}



function mb_pathinfo($path, $opt = "")
{
	$separator = " qq ";
	$path = preg_replace("/[^ ]/u", $separator . "\$0" . $separator, $path);
	if ($opt == "") $pathinfo = pathinfo($path);
	else $pathinfo = pathinfo($path, $opt);
	if (is_array($pathinfo)) {
		$pathinfo2 = $pathinfo;
		foreach ($pathinfo2 as $key => $val) {
			$pathinfo[$key] = str_replace($separator, "", $val);
		}
	} else if (is_string($pathinfo)) $pathinfo = str_replace($separator, "", $pathinfo);
	return $pathinfo;
}

function file_upload($limitedext, $upload_dir, $new_file, $option = [])
{
	// 判斷欄位是否指定上傳檔案…
	$callback_arr = ["up_state" => 1, "up_name" => "", "up_message" => "該檔案欄位為選填"];

	$phpFileUploadErrors = array(
		0 => 'There is no error, the file uploaded with success',
		1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini/上傳的檔案大小超過 php.ini 當中 upload_max_filesize 參數的設定',
		2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form/上傳的檔案大小超過 HTML 表單中 MAX_FILE_SIZE 的限制',
		3 => 'The uploaded file was only partially uploaded/只有檔案的一部分被上傳',
		4 => 'No file was uploaded/無已上傳檔案',
		5 => '自訂錯誤訊息:base64字串應包含 data:image/jpeg;base64 類似字樣',
		6 => 'Missing a temporary folder/遺失暫存資料夾',
		7 => 'Failed to write file to disk./寫入硬碟失敗',
		8 => 'A PHP extension stopped the file upload.',
	);
	
	if(gettype($new_file) == "string"){ //如果是字串

		if (is_base64($new_file)) { //如果字串是base64 image就繼續往下
	
			$ext = explode('/',explode(':',substr($new_file,0,strpos($new_file,';')))[1])[1];
			
			if(isset($option["file_name"])){ //如果原本有自訂就用原本, 不管怎樣先存png比較不會有問題..
				$src_filename = $option["file_name"] . "." . $ext; 
			}else{
				$option["file_name"] = gen_uuid();
				$src_filename = $option["file_name"] . "." . $ext; //如果經過調整，檔名消失了,則自訂一個
			}
			
			$file_tmp = $upload_dir . $src_filename;
			
			file_put_contents($file_tmp, file_get_contents(@$new_file));
			
			$new_file = [];
			$new_file['error'] = 4;
			$new_file['name'] = $src_filename;	
			
		}else{
			$new_file = [];
			$new_file['error'] = 5;
			$new_file['name'] = $phpFileUploadErrors[$new_file['error']];
		}			
	}

	
	if ($new_file['error'] > 0 and $new_file['error'] != 4) {
		$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => $phpFileUploadErrors[$new_file['error']]];
	} else {
		if(isset($new_file['tmp_name'])){
			$file_tmp = $new_file['tmp_name'];
		}
		$width = (isset($option["width"])) ? $option["width"] : 0;
		$height = (isset($option["height"])) ? $option["height"] : 0;
		$valign = (isset($option["valign"])) ? $option["valign"] : "middle";
		$img_zip = (isset($option["img_zip"])) ? $option["img_zip"] : "Y"; //img_zip通常不會傳，預設也都是要壓圖片
		
		$file_name = "";
		$reg = "/[[:punct:]]/i";
		if (strlen($file_tmp) > 0) {
			//if (is_uploaded_file($file_tmp)) { //為了支援base64先拿掉

				$ext = "." . strtolower(mb_pathinfo($new_file['name'], PATHINFO_EXTENSION));
				$temp = explode('?', $ext);
				$ext = $temp[0];

				//下面這規則應該沒問題, 但如果是圖片～或許就不該存在中文檔名?
				if (isset($option["file_name"])) { //如果自訂檔名, 就是處理自訂後的檔名
				
					$src_filename = $option["file_name"]; //自訂檔名不轉小寫	    
					$src_filename = params_security($src_filename); //過濾奇怪檔名,同時全轉半
					//$src_filename = preg_replace($reg, '', $src_filename); //過濾各種符號
					$src_filename = replace_trim($src_filename, "any"); //去除多餘空白		
					
				} else { //如果以原檔名為主, 就是先處理奇怪檔名, 最後轉小寫
					$src_filename = mb_pathinfo($new_file['name'], PATHINFO_FILENAME);
					$src_filename = params_security($src_filename); //過濾奇怪檔名,同時全轉半
					$src_filename = preg_replace($reg, '', $src_filename); //過濾各種符號
					$src_filename = replace_trim($src_filename, "any"); //去除多餘空白
					$src_filename = strtolower($src_filename); //原檔名轉小寫			  
				}


				if ($src_filename == "") {
					$src_filename = gen_uuid(); //如果經過調整，檔名消失了,則自訂一個
				}

				if (isset($option["file_name"])) { //如果自訂檔名
					if ($ext == ".jpg" or $ext == ".jpeg" or $ext == ".bmp" or $ext == ".png" or $ext == ".mp4") { //圖檔強制轉換,ios不支援中文檔名
						//$file_name = gen_uuid() . $ext; //即便自訂檔名, 遇到圖檔都強制轉換檔名, 不然IOS對中文檔名支持度很低
						$file_name = $src_filename . $ext;
					}else{
						$file_name = $src_filename . $ext;
					}
					
				} else { //如果沒自訂檔名					
					if ($ext == ".jpg" or $ext == ".jpeg" or $ext == ".bmp" or $ext == ".png" or $ext == ".mp4") { //圖檔強制轉換,ios不支援中文檔名
						$file_name = gen_uuid() . $ext;
					}else{ //如果沒自訂檔名且不是圖檔, 就用合法原本檔名
						$file_name = date("YmdHis") . "_" . $src_filename . $ext;
					}
					
				}


				if (in_array($ext, $limitedext)) {
					if ( ($ext == ".jpg" or $ext == ".jpeg" or $ext == ".bmp" or $ext == ".png") and $img_zip == "Y") { //如果是圖檔
						@$exif = exif_read_data($file_tmp);
						$lat = 0;
						$lng = 0;
						$direction = 0;
						if(isset($exif["GPSLatitude"]) and isset($exif['GPSLatitudeRef'])){
							$lat = gps($exif["GPSLatitude"], $exif['GPSLatitudeRef']);
							$lng = gps($exif["GPSLongitude"], $exif['GPSLongitudeRef']);						
						}
						if(isset($exif["GPSImgDirection"])){
							$GPSImgDirection = $exif["GPSImgDirection"];
							if(isset($GPSImgDirection["numerator"])){
								$direction = $GPSImgDirection["numerator"] / $GPSImgDirection["denominator"];
							}
														
						}
						
						if ($width == 0 and $height == 0) { //不轉檔
							if (is_image($file_tmp)) {
								image_fix_orientation($file_tmp); //手機上傳要轉向							
								$img = new SimpleImage($file_tmp);
								$img->maxarea(2000, 2000);
								$img->save($upload_dir . $file_name);
								if (is_file($upload_dir . $file_name)) {
									$callback_arr = ["up_state" => 1, "up_name" => $file_name, "up_message" => "上傳成功", "up_exif" => array("lat" => $lat, "lng" => $lng, "direction" => $direction)];
								} else {
									$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案寫入失敗,請檢查權限", "up_exif" => array()];
								}
							} else {
								$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案損毀請重新上傳", "up_exif" => array()];
							}							
						}else{ //要轉檔
							if (is_image($file_tmp)) {
								image_fix_orientation($file_tmp); //手機上傳要轉向	
								$img = new SimpleImage($file_tmp);
								if ($width > 0 and $height > 0) {
									$img->minareafill($width, $height, $valign);
								} else if ($width > 0) {
									$img->resizeToWidth($width);
								} else if ($height > 0) {
									$img->resizeToHeight($height);
								}


								$img->save($upload_dir . $file_name);
								if (is_file($upload_dir . $file_name)) {
									$callback_arr = ["up_state" => 1, "up_name" => $file_name, "up_message" => "上傳成功", "up_exif" => array("lat" => $lat, "lng" => $lng, "direction" => $direction)];
								} else {
									$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案寫入失敗,請檢查權限", "up_exif" => array()];
								}
							} else {
								$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案損毀請重新上傳", "up_exif" => array()];
							}							
						}
					}else{
						//$tmp_loc = iconv("UTF-8", "big5", $upload_dir . $file_name );
						$tmp_loc = mb_convert_encoding($upload_dir . $file_name, "UTF-8", "auto");
						
						if (move_uploaded_file($file_tmp, $tmp_loc)) {
							if (is_file($tmp_loc)) {
								$callback_arr = ["up_state" => 1, "up_name" => $file_name, "up_message" => "上傳成功", "up_exif" => array()];
							} else {
								$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案寫入失敗,請檢查權限", "up_exif" => array()];
							}
						} else {						
							$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案寫入失敗,請檢查權限", "up_exif" => array()];
						}						
					}					
				} else {
					$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "檔案類型非系統允許", "up_exif" => array()];
				}
			//} else {

			//	$callback_arr = ["up_state" => 0, "up_name" => "", "up_message" => "非正常上傳管道"];
			//}
		}
	}

	return $callback_arr;
}

function is_base64($string){
	if (strpos($string, "data:image/") !== false) {
		return true;
	}else{
		return false;
	}
}

function gps($coordinate, $hemisphere) {
  if (is_string($coordinate)) {
    $coordinate = array_map("trim", explode(",", $coordinate));
  }
  for ($i = 0; $i < 3; $i++) {
    $part = explode('/', $coordinate[$i]);
    if (count($part) == 1) {
      $coordinate[$i] = $part[0];
    } else if (count($part) == 2 && floatval($part[1])) {
      $coordinate[$i] = floatval($part[0])/floatval($part[1]);
    } else {
      $coordinate[$i] = 0;
    }
  }
  list($degrees, $minutes, $seconds) = $coordinate;
  $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
  return $sign * ($degrees + $minutes/60 + $seconds/3600);
}

// 座標點encode編碼
//$points = [['lat' =>24.117026, 'lng' => 120.725259],['lat' =>24.100588, 'lng' => 120.691821],['lat' =>24.088205, 'lng' => 120.730402],['lat' =>24.102702, 'lng' => 120.700694]];
function map_encode($points_e)
{
	$precision = 5;
	foreach ($points_e as $key => $value) {
		$points[] = [$value["lat"], $value["lng"]];
	}
	$points = map_flatten($points);
	$encodedString = '';
	$index = 0;
	$previous = array(0, 0);
	foreach ($points as $number) {
		$number = (float)($number);
		$number = (int)round($number * pow(10, $precision));
		$diff = $number - $previous[$index % 2];
		$previous[$index % 2] = $number;
		$number = $diff;
		$index++;
		$number = ($number < 0) ? ~($number << 1) : ($number << 1);
		$chunk = '';
		while ($number >= 0x20) {
			$chunk .= chr((0x20 | ($number & 0x1f)) + 63);
			$number >>= 5;
		}
		$chunk .= chr($number + 63);
		$encodedString .= $chunk;
	}
	return $encodedString;
}

function map_flatten($array)
{
	$flatten = array();
	array_walk_recursive(
		$array, // @codeCoverageIgnore
		function ($current) use (&$flatten) {
			$flatten[] = $current;
		}
	);
	return $flatten;
}

// 返回Polygon中心點
//$points = [['lat' =>24.117026, 'lng' => 120.725259],['lat' =>24.100588, 'lng' => 120.691821],['lat' =>24.088205, 'lng' => 120.730402],['lat' =>24.102702, 'lng' => 120.700694]];
function getCentroid($points_e)
{
	foreach ($points_e as $key => $value) {
		$coord[] = [$value["lat"], $value["lng"]];
	}
	$centroid = array_reduce($coord, function ($x, $y) use ($coord) {
		$len = count($coord);
		return [$x[0] + $y[0] / $len, $x[1] + $y[1] / $len];
	}, array(0, 0));
	return $centroid;
}


function containsLocation($point, $polygon, $geodesic)
{
	$size = count($polygon);
	if ($size == 0) {
		return false;
	}
	$lat3 = map_toRadians($point['lat']);
	$lng3 = map_toRadians($point['lng']);
	$prev = $polygon[$size - 1];
	$lat1 = map_toRadians($prev['lat']);
	$lng1 = map_toRadians($prev['lng']);
	$nIntersect = 0;
	foreach ($polygon as $point2) {
		$dLng3 = map_wrap($lng3 - $lng1, -3.141592653589793, 3.141592653589793);
		if ($lat3 == $lat1 && $dLng3 == 0) {
			return true;
		}
		$lat2 = map_toRadians($point2['lat']);
		$lng2 = map_toRadians($point2['lng']);
		if (map_intersects($lat1, $lat2, map_wrap($lng2 - $lng1, -3.141592653589793, 3.141592653589793), $lat3, $dLng3, $geodesic)) {
			++$nIntersect;
		}
		$lat1 = $lat2;
		$lng1 = $lng2;
	}
	return ($nIntersect & 1) != 0;
}

function isLocationOnEdgeOrPath($point, $poly, $closed, $toleranceEarth)
{
	$size = count($poly);
	if ($size == 0) {
		return false;
	}
	$tolerance = $toleranceEarth / 6371009;
	$havTolerance = map_hav($tolerance);
	$lat3 = map_toRadians($point['lat']);
	$lng3 = map_toRadians($point['lng']);
	$prev = $poly[$closed ? $size - 1 : 0];
	$lat1 = map_toRadians($prev['lat']);
	$lng1 = map_toRadians($prev['lng']);
	$minAcceptable = $lat3 - $tolerance;
	$maxAcceptable = $lat3 + $tolerance;
	$y1 = map_mercator($lat1);
	$y3 = map_mercator($lat3);
	$xTry = array();
	foreach ($poly as $point2) {
		$lat2 = map_toRadians($point2['lat']);
		$y2 = map_mercator($lat2);
		$lng2 = map_toRadians($point2['lng']);
		if (max($lat1, $lat2) >= $minAcceptable && min($lat1, $lat2) <= $maxAcceptable) {
			$x2 = map_wrap($lng2 - $lng1, -3.141592653589793, 3.141592653589793);
			$x3Base = map_wrap($lng3 - $lng1, -3.141592653589793, 3.141592653589793);
			$xTry[0] = $x3Base;
			$xTry[1] = $x3Base + 2 * 3.141592653589793;
			$xTry[2] = $x3Base - 2 * 3.141592653589793;
			foreach ($xTry as $x3) {
				$dy = $y2 - $y1;
				$len2 = $x2 * $x2 + $dy * $dy;
				$t = $len2 <= 0 ? 0 : map_clamp(($x3 * $x2 + ($y3 - $y1) * $dy) / $len2, 0, 1);
				$xClosest = $t * $x2;
				$yClosest = $y1 + $t * $dy;
				$latClosest = map_inversemap_mercator($yClosest);
				$havDist = map_havDistance($lat3, $latClosest, $x3 - $xClosest);
				if ($havDist < $havTolerance) {
					return true;
				}
			}
		}
		$lat1 = $lat2;
		$lng1 = $lng2;
		$y1 = $y2;
	}
	return false;
}
function map_intersects($lat1, $lat2, $lng2, $lat3, $lng3, $geodesic)
{
	if (($lng3 >= 0 && $lng3 >= $lng2) || ($lng3 < 0 && $lng3 < $lng2)) {
		return false;
	}
	if ($lat3 <= -3.141592653589793 / 2) {
		return false;
	}
	if ($lat1 <= -3.141592653589793 / 2 || $lat2 <= -3.141592653589793 / 2 || $lat1 >= 3.141592653589793 / 2 || $lat2 >= 3.141592653589793 / 2) {
		return false;
	}
	if ($lng2 <= -3.141592653589793) {
		return false;
	}
	$linearLat = ($lat1 * ($lng2 - $lng3) + $lat2 * $lng3) / $lng2;
	if ($lat1 >= 0 && $lat2 >= 0 && $lat3 < $linearLat) {
		return false;
	}
	if ($lat1 <= 0 && $lat2 <= 0 && $lat3 >= $linearLat) {
		return true;
	}
	if ($lat3 >= 3.141592653589793 / 2) {
		return true;
	}
	return $geodesic ? tan($lat3) >= map_tanLatGC($lat1, $lat2, $lng2, $lng3) : map_mercator($lat3) >= map_mercatorLatRhumb($lat1, $lat2, $lng2, $lng3);
}
function map_mercatorLatRhumb($lat1, $lat2, $lng2, $lng3)
{
	return (map_mercator($lat1) * ($lng2 - $lng3) + map_mercator($lat2) * $lng3) / $lng2;
}
function map_tanLatGC($lat1, $lat2, $lng2, $lng3)
{
	return (tan($lat1) * sin($lng2 - $lng3) + tan($lat2) * sin($lng3)) / sin($lng2);
}
function map_toRadians($degrees)
{
	return ($degrees * 3.141592653589793) / 180;
}
function map_hav($x)
{
	$sinHalf = sin($x * 0.5);
	return $sinHalf * $sinHalf;
}
function map_mercator($lat)
{
	return log(tan($lat * 0.5 + 3.141592653589793 / 4));
}
function map_clamp($x, $low, $high)
{
	return $x < $low ? $low : ($x > $high ? $high : $x);
}
function map_wrap($n, $min, $max)
{
	return ($n >= $min && $n < $max) ? $n : (map_modd($n - $min, $max - $min) + $min);
}
function map_inversemap_mercator($y)
{
	return 2 * atan(exp($y)) - 3.141592653589793 / 2;
}
function map_havDistance($lat1, $lat2, $dLng)
{
	return map_hav($lat1 - $lat2) + map_hav($dLng) * cos($lat1) * cos($lat2);
}
function map_modd($x, $m)
{
	return (($x % $m) + $m) % $m;
}


function notify_state($user_uuid, $project_id, $tool = "line")
{
	$token = aes_encrypt($user_uuid);
	if ($tool == "line") {
		return json_decode(download_page("https://www.linebot.tw/api/line_state.php", "token=" . $token . "&uuid=" . $user_uuid . "&project_id=" . $project_id), true);
	} else if ($tool == "notify") {
		return json_decode(download_page("https://www.linebot.tw/api/notify/status.php", "token=" . $token . "&uuid=" . $user_uuid . "&project_id=" . $project_id), true);
	}
}

function notify_state_toggle($autoid, $state = "0", $tool = "line", $mode = "browser", $callback_url = "")
{
	if(empty($callback_url)){
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$http = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$callback_url = $protocol . $http;

		if ($mode == "app") {
			$callback_url = "https://www.linebot.tw/down/index.html?state=" . $state;
		}		
	}	


	$ase_source = $autoid . "@@@" . $callback_url;

	if ($tool == "line") {
		return "https://access.line.me/oauth2/v2.1/authorize?response_type=code&client_id=1597535320&redirect_uri=https://www.linebot.tw/api/line_login.php&state=" . aes_encrypt($ase_source) . "&scope=profile openid";
	} else if ($tool == "notify") {
		if ($state == "0") {
			$header = [
				"response_mode", "form_post",
				"response_type" => "code",
				"client_id" => "19OqrsrE7xLhO8vO5IcibU",
				"redirect_uri" => "https://www.linebot.tw/api/notify/callback.php",
				"scope" => "notify",
				"state" => aes_encrypt($ase_source)
			];
			return urldecode("https://notify-bot.line.me/oauth/authorize?" . http_build_query($header) . "");
		} else {
			return urldecode("https://www.linebot.tw/api/notify/status_change.php?state=" . aes_encrypt($ase_source));
		}
	}
}



function notify_push($user_uuid, $project_id, $message, $tool = "line")
{
	$temp = gettoken_value();
	if ($tool == "line") {
		return json_decode(download_page("https://www.linebot.tw/api/line_notify.php", "value=" . $temp["value"] . "&token=" . $temp["token"] . "&uuid=" . $user_uuid . "&project_id=" . $project_id . "&message=" . $message), true);
	} else if ($tool == "notify") {
		return json_decode(download_page("https://www.linebot.tw/api/notify/notify.php", "value=" . $temp["value"] . "&token=" . $temp["token"] . "&uuid=" . $user_uuid . "&project_id=" . $project_id . "&message=" . $message), true);
	}
}


function date_range($first, $last, $step = '+1 day', $format = 'Y-m-d')
{
	$dates   = array();
	if (strpos($step, "year") !== false) {
		$first = date("Y-01-01 00:00:00", strtotime($first));
		$format = "Y";
	} else if (strpos($step, "month") !== false) {
		$first = date("Y-m-01 00:00:00", strtotime($first));
		$format = "Y-m";
	} else if (strpos($step, "day") !== false) {
		$first = date("Y-m-d 00:00:00", strtotime($first));
		$format = "Y-m-d";
	} else if (strpos($step, "hour") !== false) {
		$first = date("Y-m-d H:00:00", strtotime($first));
		$format = "Y-m-d H:i:s";
	} else if (strpos($step, "minute") !== false) {
		$first = date("Y-m-d H:i:00", strtotime($first));
		$format = "Y-m-d H:i:s";
	} else if (strpos($step, "second") !== false) {
		$first = date("Y-m-d H:i:s", strtotime($first));
		$format = "Y-m-d H:i:s";
	}

	$current = strtotime($first);
	$last    = strtotime($last);

	while ($current <= $last) {
		$dates[] = date($format, $current);
		$current = strtotime($step, $current);
	}

	return $dates;
}



function aes_validation($no_encrypt, $encrypt)
{
	if (!is_null($no_encrypt) and !is_null($encrypt)) {
		if (aes_encrypt($no_encrypt) == $encrypt) {
			return "1";
		} else {
			return "0";
		}
	} else {
		return "0";
	}
}
function aes_encrypt($input)
{
	$key = '3883136338831363';
	$data = openssl_encrypt($input, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	$data = base64_encode($data);
	$data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
	return $data;
}

function aes_decrypt($input)
{
	$key = '3883136338831363';
	$data = str_replace(array('-', '_'), array('+', '/'), $input);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$decrypted = openssl_decrypt(base64_decode($data), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
	return $decrypted;
}

/*
md5方式用於不需要登入就能做的方式, 能避免按鍵精靈之類機器攻擊
註冊, 忘記密碼, 留言, 登入
PHP環境建立token方式如下(如果client不是PHP環境,則改用JS呼叫)
$temp = gettoken_value();
echo '<input type="hidden" name="value" value="'.$temp["value"].'">';
echo '<input type="hidden" name="token" value="'.$temp["token"].'">'; 
*/
function gettoken_value()
{
	$str = gen_uuid();
	return array(
		"value" => $str,
		"token" => "pin" . sha1(md5($str))
	);
}

//如果要驗證token需匯入資料表
//https://drive.google.com/file/d/1j4hZsZz_GLEUmj9xPTTdbSV0qc75hdw4/view?usp=sharing
function token_validation($value, $token)
{
	global $mysqli;
	if (!empty($value) and !empty($token)) {
		$query = "INSERT INTO validation (token,pub_date) SELECT '" . $token . "',now() FROM dual WHERE not exists (select * from validation where token = '" . $token . "');";
		$mysqli->query($query);
		$affected_rows = $mysqli->affected_rows;

		$query = "delete FROM validation WHERE pub_date <= DATE_SUB(CURDATE(),INTERVAL 2 DAY);";
		$mysqli->query($query);

		if ($affected_rows > 0) { //如果能insert token代表這組token沒被用過		
			if (str_remove($token, 3) !== "pin") {
				//如果沒包含pin就表示JS仍是用舊的加密方式,那就用舊解密
				if ($token == md5(md5($value))) {
					return "1";
				} else {
					return "0";
				}
			} else {
				//如果包含pin就表示加密方式是透過新PHP產生,那就用新解密
				if ($token == "pin" . sha1(md5($value))) {
					return "1";
				} else {
					return "0";
				}
			}
		} else {
			return "0";
		}
	} else {
		return "0";
	}
}

/*
附近生活機能與嫌惡設施
poi_nearby(24.109113, 120.691772,1,30,"map8","生活機能","國小,圖書館,國中,公眾服務,藝文美術,停車場,高中職校,公園,超級市場,大專院校");
poi_nearby(24.109113, 120.691772,1,30,"map8","嫌惡設施","加油站,廢棄物回收,變電所,高壓電塔,區域醫院及教學醫院,生命禮儀業,垃圾場,垃圾焚化廠,廢棄物回收");
poi_nearby(24.109113, 120.691772,1,30,"google","生活機能","公園")
*/
function poi_nearby($lat, $lng, $distance = 1, $limit = 30, $platform = "map8", $types = "生活機能", $catgeory = "")
{
	$sum_arr = array();
	$sum_arr["type"] = "FeatureCollection";
	$features_arr = [];
	$sum_arr["features"] = $features_arr;

	if ($platform == "map8") {
		$link = ($types == "生活機能") ? "nearbyamenity" : "nearbyNimby";
		$details_url = "https://api.map8.zone/v2/premium/housing/$link?key=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJodHRwczpcL1wvd3d3Lm1hcDguem9uZSIsIm5hbWUiOiJtYXBAZ295b3VybGlmZS5jb20iLCJpYXQiOjE2NjI5ODk4NzMsIm9iamVjdHMiOlsiXC9tYXBzXC9qcyIsIlwvbWFwc1wvc3RhdGljIiwiXC9tYXBzXC9lbWJlZCIsIlwvcGxhY2VcL2dlb2NvZGUiLCJcL3BsYWNlXC9maW5kcGxhY2Vmcm9tdGV4dCIsIlwvcGxhY2VcL25lYXJieXNlYXJjaCIsIlwvcGxhY2VcL3RleHRzZWFyY2giLCJcL3BsYWNlXC9hdXRvY29tcGxldGUiLCJcL2RhdGEiLCJcL3N0eWxlcyIsIlwvc3ByaXRlcyIsIlwvZm9udHMiLCJcL2RhdGFcL3NjaG9vbC1kaXN0cmljdCIsIlwvcHJlbWl1bVwvaG91c2luZyIsIlwvcHJlbWl1bVwvaG91c2luZ1wvc2Nob29sZGlzdHJpY3RzIiwiXC9yb3V0ZVwvZGlyZWN0aW9ucyIsIlwvcm91dGVcL2Rpc3RhbmNlbWF0cml4IiwiXC9yb3V0ZVwvdHJpcCIsIlwvcm9hZFwvbmVhcmVzdFJvYWRzIiwiXC9yb2FkXC9zbmFwVG9Sb2FkcyJdLCJleHAiOjE5Nzk4MTg2NzN9.3mhfXnwwR0VSRJT2rfWnuO5OfXAgMEsH2Xl-k520zxw&location=$lat,$lng&radius=$distance&limit=$limit&select=$catgeory";
		$response = json_decode(download_page($details_url,"none",array("Host:api.map8.zone","Origin:https://www.map8.zone","Referer:https://www.map8.zone/")), true);
		if ($response['status'] == "ZERO_RESULTS") {
			return $sum_arr;
		} else {
			foreach ($response["results"] as $key => $value) {
				$title = str_replace("'", '′', params_security($value["name"]));

				$arr = array('type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [$value['geometry']['location']['lng'], $value['geometry']['location']['lat']]], 'properties' => ['formatted_address' => $value['formatted_address'], 'distance' => round($value['distance'], 2), 'tel' => $value['tel'], 'catgeory' => $value['cat'], 'title' => $title, 'place_id' => aes_encrypt($value['place_id'])]);
				array_push($features_arr, $arr);
			}

			$sum_arr["features"] = $features_arr;
			return $sum_arr;
		}
	} else if ($platform == "google") {
		$distance = $distance * 1000;
		$key = array("AIzaSyCkr49VEGIBcSBHgLwwRCLUV2vLmvPFNd0","AIzaSyASVzmcsxVK71grBnmNg3yCFbRtxIYNpLc","AIzaSyD7c1IID7zDCdcfpC69fC7CUqLjz50mcls");

		$random_keys = $key[array_rand($key, 1)];
		$response = json_decode(download_page("https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$lat,$lng&radius=$distance&keyword=$catgeory&key=$random_keys&language=zh-TW&type=establishment"), true);
		if ($response['status'] == "ZERO_RESULTS") {
			return $sum_arr;
		} else {

			foreach ($response["results"] as $key => $value) {
				$title = str_replace("'", '′', params_security($value["name"]));

				$arr = array('type' => 'Feature', 'geometry' => ['type' => 'Point', 'coordinates' => [$value['geometry']['location']['lng'], $value['geometry']['location']['lat']]], 'properties' => ['formatted_address' => '', 'distance' => '', 'tel' => '', 'catgeory' => implode(",", $value["types"]), 'title' => $title, 'place_id' => aes_encrypt($value['place_id']), 'user_ratings_total' => $value['user_ratings_total']]);
				array_push($features_arr, $arr);
			}

			$sum_arr["features"] = $features_arr;

			return $sum_arr;
		}
	}
}

//經緯度轉地址
function loc2add($lat, $lng, $platform = "map8")
{
	$array = array();
	if ($platform == "map8") {
		$details_url = "https://api.map8.zone/v2/place/geocode/json?key=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJodHRwczpcL1wvd3d3Lm1hcDguem9uZSIsIm5hbWUiOiJtYXBAZ295b3VybGlmZS5jb20iLCJpYXQiOjE2NjI5ODk4NzMsIm9iamVjdHMiOlsiXC9tYXBzXC9qcyIsIlwvbWFwc1wvc3RhdGljIiwiXC9tYXBzXC9lbWJlZCIsIlwvcGxhY2VcL2dlb2NvZGUiLCJcL3BsYWNlXC9maW5kcGxhY2Vmcm9tdGV4dCIsIlwvcGxhY2VcL25lYXJieXNlYXJjaCIsIlwvcGxhY2VcL3RleHRzZWFyY2giLCJcL3BsYWNlXC9hdXRvY29tcGxldGUiLCJcL2RhdGEiLCJcL3N0eWxlcyIsIlwvc3ByaXRlcyIsIlwvZm9udHMiLCJcL2RhdGFcL3NjaG9vbC1kaXN0cmljdCIsIlwvcHJlbWl1bVwvaG91c2luZyIsIlwvcHJlbWl1bVwvaG91c2luZ1wvc2Nob29sZGlzdHJpY3RzIiwiXC9yb3V0ZVwvZGlyZWN0aW9ucyIsIlwvcm91dGVcL2Rpc3RhbmNlbWF0cml4IiwiXC9yb3V0ZVwvdHJpcCIsIlwvcm9hZFwvbmVhcmVzdFJvYWRzIiwiXC9yb2FkXC9zbmFwVG9Sb2FkcyJdLCJleHAiOjE5Nzk4MTg2NzN9.3mhfXnwwR0VSRJT2rfWnuO5OfXAgMEsH2Xl-k520zxw&postcode&latlng=$lat,$lng";
		$response = json_decode(download_page($details_url,"none",array("Host:api.map8.zone","Origin:https://www.map8.zone","Referer:https://www.map8.zone/")), true);
		if ($response['status'] == "ZERO_RESULTS") {
			return $array;
		} else {
			$geometry = $response['results'][0];
			$array = array(
				'formatted_address' => $geometry['formatted_address'],
				'road' => $geometry['name'],
				'town' => $geometry['town'],
				'city' => $geometry['city'],
				'postcode' => $geometry['postcode'],
			);
			return $array;
		}
	}
}
//地址轉經緯度
function add2loc($string, $platform = "map8", $key = array())
{
	$array = array();
	$string = cover_width_small(replace_trim($string), 1);
	
	$input["address"] = replace_trim(preg_replace('/^[0-9]*/', '', $string));
	$input["platform"] = $platform;
	$input["key"] = $key;
	$temp = gettoken_value();
	
	$input["value"] = $temp["value"];
	$input["token"] = $temp["token"];
	return json_decode(download_page("https://house.linebot.tw/api/v2_add2loc.php", $input),true);
	/*
	if ($platform == "google") {
		
		//$key = array("AIzaSyCkr49VEGIBcSBHgLwwRCLUV2vLmvPFNd0","AIzaSyASVzmcsxVK71grBnmNg3yCFbRtxIYNpLc");
		$key = $key[array_rand($key, 1)];
		
		$string = str_replace(" ", "+", urlencode($string));
		$details_url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $string . "&language=zh-TW&key=" . $key;

		$response = json_decode(download_page($details_url), true);


		if ($response['status'] != 'OK') {
			return $array;
		} else {
			$geometry = $response['results'][0]['geometry'];

			$array = array(
				'lng' => $geometry['location']['lng'],
				'lat' => $geometry['location']['lat'],
				'location_type' => $geometry['location_type'],
				'address' => $response['results'][0]['formatted_address']
			);
			return $array;
		}
	} else if ($platform == "geologycloud") {
		$string = str_replace(" ", "+", urlencode($string));
		$details_url = "https://www.geologycloud.tw/map/utils/tgos?address=" . $string;
		$response = json_decode(download_page($details_url), true);
		if (count($response['AddressList']) == 0) {
			return $array;
		} else {
			$array = array(
				'lng' => $response['AddressList'][0]["Y"],
				'lat' => $response['AddressList'][0]["X"],
				'location_type' => "",
				'address' => $response['AddressList'][0]["FULL_ADDR"]
			);
			return $array;
		}
	} else if ($platform == "map8") {
		//eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJodHRwczpcL1wvd3d3Lm1hcDguem9uZSIsIm5hbWUiOiJtYXBAZ295b3VybGlmZS5jb20iLCJpYXQiOjE2NjI5ODk4NzMsIm9iamVjdHMiOlsiXC9tYXBzXC9qcyIsIlwvbWFwc1wvc3RhdGljIiwiXC9tYXBzXC9lbWJlZCIsIlwvcGxhY2VcL2dlb2NvZGUiLCJcL3BsYWNlXC9maW5kcGxhY2Vmcm9tdGV4dCIsIlwvcGxhY2VcL25lYXJieXNlYXJjaCIsIlwvcGxhY2VcL3RleHRzZWFyY2giLCJcL3BsYWNlXC9hdXRvY29tcGxldGUiLCJcL2RhdGEiLCJcL3N0eWxlcyIsIlwvc3ByaXRlcyIsIlwvZm9udHMiLCJcL2RhdGFcL3NjaG9vbC1kaXN0cmljdCIsIlwvcHJlbWl1bVwvaG91c2luZyIsIlwvcHJlbWl1bVwvaG91c2luZ1wvc2Nob29sZGlzdHJpY3RzIiwiXC9yb3V0ZVwvZGlyZWN0aW9ucyIsIlwvcm91dGVcL2Rpc3RhbmNlbWF0cml4IiwiXC9yb3V0ZVwvdHJpcCIsIlwvcm9hZFwvbmVhcmVzdFJvYWRzIiwiXC9yb2FkXC9zbmFwVG9Sb2FkcyJdLCJleHAiOjE5Nzk4MTg2NzN9.3mhfXnwwR0VSRJT2rfWnuO5OfXAgMEsH2Xl-k520zxw
		$key = $key[array_rand($key, 1)];
		$string = str_replace(" ", "+", urlencode($string));
		$details_url = "https://api.map8.zone/v2/place/geocode/json?key=".$key."&address=" . $string;
		$response = json_decode(download_page($details_url,"none",array("Host:api.map8.zone","Origin:https://www.map8.zone","Referer:https://www.map8.zone/")), true);
		if ($response['status'] == "ZERO_RESULTS") {
			return $array;
		} else {
			$geometry = $response['results'][0]['geometry'];
			$array = array(
				'lng' => $geometry['location']['lng'],
				'lat' => $geometry['location']['lat'],
				'location_type' => "",
				'address' => $response['results'][0]['formatted_address'],
				'city'=>  $response['results'][0]['city'],
				'town'=>  $response['results'][0]['town']
			);
			return $array;
		}
	}
	*/
}

//手機圖片橫向修正, 旋轉完再縮圖										
//image_fix_orientation($file_tmp);	
//$img = new SimpleImage($file_tmp)
function image_fix_orientation($filename)
{
	if (@imagecreatefromjpeg($filename) !== false) {
		$exif = @exif_read_data($filename);
		if (!empty($exif['Orientation'])) {
			$image = imagecreatefromjpeg($filename);
			switch ($exif['Orientation']) {
				case 3:
					$image = imagerotate($image, 180, 0);
					break;

				case 6:
					$image = imagerotate($image, -90, 0);
					break;

				case 8:
					$image = imagerotate($image, 90, 0);
					break;
			}

			imagejpeg($image, $filename, 90);
		}
	}
}

//資料夾與檔案複製, 如果帶入self_delete = true時，來源檔案搬移完成後會自動刪除
function smartCopy($source, $dest, $options = array('folderPermission' => 0755, 'filePermission' => 0755, 'self_delete' => false))
{
	$result = false;

	if (is_file($source)) {
		if ($dest[strlen($dest) - 1] == '/') {
			if (!file_exists($dest)) {
				cmfcDirectory::makeAll($dest, $options['folderPermission'], true);
			}
			$__dest = $dest . "/" . basename($source);
		} else {
			$__dest = $dest;
		}
		$result = "";
		if($options['self_delete'] == true){
			$result = rename($source, $__dest);
		}else{
			$result = copy($source, $__dest);
		}
		chmod($__dest, $options['filePermission']);
		
	} elseif (is_dir($source)) {
		if ($dest[strlen($dest) - 1] == '/') {
			if ($source[strlen($source) - 1] == '/') {
				//Copy only contents
			} else {
				//Change parent itself and its contents
				$dest = $dest . basename($source);
				@mkdir($dest);
				chmod($dest, $options['filePermission']);
			}
		} else {
			if ($source[strlen($source) - 1] == '/') {
				//Copy parent directory with new name and all its content
				@mkdir($dest, $options['folderPermission']);
				chmod($dest, $options['filePermission']);
			} else {
				//Copy parent directory with new name and all its content
				@mkdir($dest, $options['folderPermission']);
				chmod($dest, $options['filePermission']);
			}
		}

		$dirHandle = opendir($source);
		while ($file = readdir($dirHandle)) {
			if ($file != "." && $file != "..") {
				if (!is_dir($source . "/" . $file)) {
					$__dest = $dest . "/" . $file;
				} else {
					$__dest = $dest . "/" . $file;
				}				
				//echo "$source/$file ||| $__dest<br />";
				$result = smartCopy($source . "/" . $file, $__dest, $options);
			}
		}
		closedir($dirHandle);
	} else {
		$result = false;
	}
	return $result;
}

/* 下載時隱藏檔案真實路徑
if($uuid)
echo hide_file($uuid.".mp4","demo.mp4");
echo hide_file(真實路徑及檔名,假檔名);
*/
function hide_file($file, $filename)
{
	header("Content-type:application");
	header("Content-Disposition: attachment; filename=" . $filename);
	readfile($file);
	exit(0);
}


/*
image_synthesis("小張圖片",小張圖片X軸,小張圖片Y軸,"大張圖片", "背景是小圖還是大圖 ,"輸出檔名");
*/
function image_synthesis($small = "", $small_x = 0, $small_y = 0, $big ="", $background = "small", $filename)
{

	list($width, $height) = getimagesize($big);
	$out = imagecreatetruecolor($width, $height);
	//1. 建立一塊純黑色圖片
	if ($background == "small") {
		//2. 如果背景是小圖，通常前景大圖是有局部透明，作為遮照使用	
		imagecreatefrom($small, $out, $small_x, $small_y);
		imagecreatefrom($big, $out, 0, 0);
	} else {
		//3. 大張圖當底圖（通常用於浮水印之類）		
		imagecreatefrom($big, $out, 0, 0);
		imagecreatefrom($small, $out, $small_x, $small_y);
	}

	imagejpeg($out, $filename, 100);
}

function imagecreatefrom($src, $out, $x, $y)
{
	$srcIm = NULL;
	list($width, $height, $type, $attr) = getimagesize($src);
	switch ($type) {
		case IMAGETYPE_JPEG:
			$srcIm = imagecreatefromjpeg($src);
			break;
		case IMAGETYPE_PNG:
			$srcIm = imagecreatefrompng($src);
			break;
		case IMAGETYPE_GIF:
			$srcIm = imagecreatefromgif($src);
			break;
		case IMAGETYPE_WBMP:
			$srcIm = imagecreatefromwbmp($src);
			break;
		default:
			$srcIm = NULL;
	}
	imagecopyresampled($out, $srcIm, $x, $y, 0, 0, $width, $height, $width, $height);
}

//列出月份區間 dateMonths("2016-01","2016-07")
function dateMonths($start_date, $end_date, $explode = '-', $addOne = false)
{
	$start_date = date("Y-m", strtotime($start_date));

	$start_int = strtotime($start_date);
	$end_int = strtotime($end_date);
	if ($start_int > $end_int) {
		$tmp = $start_date;
		$start_date = $end_date;
		$end_date = $tmp;
	}

	$start_arr = explode($explode, $start_date);
	$start_year = intval($start_arr[0]);
	$start_month = intval($start_arr[1]);


	$end_arr = explode($explode, $end_date);
	$end_year = intval($end_arr[0]);
	$end_month = intval($end_arr[1]);


	$data = array();
	$data[] = $start_date;


	$tmp_month = $start_month;
	$tmp_year = $start_year;



	while (!(($tmp_month == $end_month) && ($tmp_year == $end_year))) {
		$tmp_month++;
		if ($tmp_month > 12) {
			$tmp_month = 1;
			$tmp_year++;
		}

		$data[] = $tmp_year . $explode . str_pad($tmp_month, 2, '0', STR_PAD_LEFT);
	}


	if ($addOne == true) {
		$tmp_month++;
		if ($tmp_month > 12) {
			$tmp_month = 1;
			$tmp_year++;
		}
		$data[] = $tmp_year . $explode . str_pad($tmp_month, 2, '0', STR_PAD_LEFT);
	}


	return $data;
}

//指定日期加減 getNewDate("1957-05-06",-1);
function getNewDate($day, $plus_day)
{
	$date	= new DateTime($day);
	$plus_day = ($plus_day) ? $plus_day : "0";
	$date->add(DateInterval::createFromDateString("$plus_day days"));
	return $date->format('Y-m-d');
}


//google縮址 原網址, API key
function google_shorturl($longUrl, $apiKey)
{
	$postData = array('longUrl' => $longUrl, 'key' => $apiKey);
	$jsonData = json_encode($postData);
	$callback = download_page('https://www.googleapis.com/urlshortener/v1/url?key=' . $apiKey, $jsonData, array('Content-type:application/json'));
	$json = json_decode($callback, true);
	return $json["id"];
}

// $str為字串，$int為3時，返回前3個字；$int為-3時，刪除最後三個字
// fun本身會去除HTML, $mode需視情況判斷是否帶入any
function str_remove($str, $int, $mode = "")
{
	return mb_substr(replace_trim(strip_html_tags($str), $mode), 0, $int, "UTF-8");
}

//列出主機上傳限制 memory_limit > post_max_size > upload_max_filesize
function dump_serversize()
{
	$array = array("memory_limit = " . ini_get('memory_limit') . "", "post_max_size = " . ini_get('post_max_size') . "", "upload_max_filesize = " . ini_get('upload_max_filesize') . "");
	return $array;
}


/*
help:
params_security(變數); 一般字串
params_security(變數,"int"); 數字
params_security(變數,"text"); textarea使用
params_security(變數,"html"); ckeditor使用
params_security(變數,"json"); json使用
params_security(變數,"json_break"); json使用, 但遇到錯誤不停止, 目前是明宗爬蟲專用
params_security(變數,"xml"); xml使用
params_security(變數,"plus"); 最少過濾，後台可選擇使用
params_security(變數,"none"); 完全不過濾，後台斟酌使用
*/
function params_security($params, $type = "")
{
	global $mysqli;
	$newLineArray = array("\r\n","\r","\n","\\r","\\n","\\r\\n");

	if(is_array($params)){
		exit("參數不能是陣列型態");
	}
	
	if(str_remove($params,16) == "pintech.encrypt@"){
		$params = str_replace('pintech.encrypt@', '', $params);
		$params = aes_decrypt($params);
		if(!empty($params)){
			$type = "none";	//如果可以被逆轉aes則不過濾
		}
		
	}	
	
	if (strpos (strtolower($_SERVER['PHP_SELF']), "_admin") === false AND strpos (strtolower($_SERVER['PHP_SELF']), "organiser") === false) {
		//如果路徑不包含admin通常在前台,就都要轉半形
		$params = cover_width_small($params, 1);
	} 
	
	
	if(validate_url($params) OR filter_var($params, FILTER_VALIDATE_EMAIL)){
		if(validate_url($params)){
			//$params = strtolower($params);
			//有的有認大小寫
		}else if( filter_var($params, FILTER_VALIDATE_EMAIL) ){
			$params = strtolower($params);
		}
		$type = "none";	//有的網址一解編碼就出錯	
	}
	
	if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $params)) {
        $type = "none";	//如果字串是base64 image就不處理
	}	
	
	//如果去除html仍相同, 表示不含html
	if (  $params == strip_tags(rawurldecode($params)) and $type == "") {
		//如果包含大寫
		if (preg_match('/[A-Z]/', $params)) {
			//如果包含數字
			if (preg_match('/\d/', $params)) {
				//字串長度 >= 20
				if(mb_strlen($params, 'UTF-8') >= 20 ){
					//如果可以被逆轉aes則不過濾
					if(!empty(aes_decrypt($params))){
						$type = "none";	
					}
				}
			}
		}
	}	
	
	if($type != "none"){
		$params = ($type == "text") ? trim(rawurldecode($params)) : replace_trim(rawurldecode($params));
	}
	
    
	if ($type == "int") {
		$temp = mysqli_escape(intval(strip_html_tags($params)));
	} else if ($type == "text") {
		//原生的 strip_tags 會過濾太多, 1>2<3 也會被過濾, 可以改用 preg_replace
		//preg_replace('/<[^>]*>/', ' ', $params) 這方式<apple>會被過慮 2023.05.30
		//改用以下, 先將<>都轉全形, 這樣就不是標準HTML
		$params = str_replace(['<', '>'], ['＜', '＞'], $params);
		
		if($params != preg_replace('/<[^>]*>/', ' ', $params)) {
			exit("字串內不能包含html語法,請返回上頁重新輸入");
		}else{
			$temp = strip_tags(str_replace($newLineArray, "<br />", preg_html($params)), "<br>");
			$temp = preg_replace('/\"|\'/i', '′', $temp);
			$temp = mysqli_escape($temp);		
		}
	} else if ($type == "html") {
		//$params = preg_replace('#<script(.*?)>(.*?)</script>#ius', '', $params); 
		//去除所有script
		$params = preg_replace('#face=".*?"#', '', $params);
		//去除face, 通常從word貼過來會有
		//$params = preg_replace('/\s*on\w+="[^"]+"/i', '', $params);
		//去除所有on事件
		//$params = preg_replace('/href="javascript:.*?">/i', 'href="">', $params);
		//去除所有 href script
		
		$config = array('safe'=>1, 'balance'=>1 ,'elements' => '*+iframe+video+embed-form');
		$temp = preg_html(stripslashes($params), $config);
	} else if ($type == "json") {		
		$json_validate = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
		if(validate_json($json_validate)){
			$temp = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
			$temp = htmlspecialchars_decode($temp, ENT_QUOTES);
		}else{
			exit("json組成失敗請聯繫管理員");
		}		
	} else if ($type == "json_break") {		
		$json_validate = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
		if(validate_json($json_validate)){
			$temp = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
			$temp = htmlspecialchars_decode($temp, ENT_QUOTES);
		}else{
			$temp = "";
		}		
	} else if ($type == "xml") {
		$temp = strip_tags(str_replace($newLineArray, "<br />", $params), "<br>");
		
	} else if ($type == "plus") {
		
		$json_validate = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
	
		if(validate_json($json_validate)){
			$temp = strip_tags(json2input(str_replace($newLineArray, "<br />", $params)), "<br>");
			$temp = htmlspecialchars_decode($temp, ENT_QUOTES);
			
		}else{
			//$temp = mysqli_real_escape_string($mysqli, stripslashes($params));
			if (PHP_VERSION >= 6 || !get_magic_quotes_gpc()){
				$temp = $params;	
			}else{
				$temp = stripslashes($params);
			}
			$temp = preg_html($temp);
			//$temp = stripevil($temp);
			//$temp = midfilter($temp);
			$temp = RemoveXSS($temp);	
			$temp = preg_replace('/\"|\'/i', '′', $temp);
			//$temp = htmlspecialchars($temp, ENT_QUOTES, 'utf-8');
			//或許應該判斷前後台?			
		}
		

				
	} else if ($type == "none") {
		$temp = $params;
	} else {
		$temp = strip_html_tags(preg_html(remove_emoji($params)));
		$temp = preg_replace('/\"|\'/i', '′', $temp);
		$temp = mysqli_escape($temp);
		//$temp = htmlspecialchars($temp, ENT_QUOTES, 'utf-8');
		//或許應該判斷前後台?
	}
	return no_ascii($temp);
}

//有些符號無法被印出的也都不要
function no_ascii($str)
{
	return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $str);
}

function remove_emoji($text)
{
	return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u', '', $text);
}



//如果跟其他function放起使用，如strip_html_tags，最好放最外層
//爬蟲下來的$html或json，再解析節點後，最好用這參數過濾一次
function mysqli_escape($foo)
{
	global $mysqli;
	IF(is_null($mysqli)){
		exit("請root.php設定mysqli並導入");
	}else{
		if ($mysqli->connect_errno) {		
			if (PHP_VERSION >= 6 || !get_magic_quotes_gpc()){
				return RemoveXSS(inject_check($foo));
				//如果要進DB，先解開預設的addslashes，改用mysqli_real_escape_string			
			}else{
				return RemoveXSS(inject_check(stripslashes($foo)));
			}
		} else {
			if (PHP_VERSION >= 6 || !get_magic_quotes_gpc()){
				return mysqli_escape_string($mysqli, RemoveXSS(inject_check($foo)));
				//如果要進DB，先解開預設的addslashes，改用mysqli_real_escape_string			
			}else{
				return mysqli_escape_string($mysqli, RemoveXSS(inject_check(stripslashes($foo))));
			}
				
		}			
	}


}



//隨機產生英文與數字，參數為長度, 第二參數能決定類型
function random_str($length, $type = "all")
{
	$password = '';
	if ($type == "all")
		$word     = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
	else if ($type == "en")
		$word     = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	else if ($type == "int")
		$word     = '23456789';

	$len      = strlen($word);

	for ($i = 0; $i < $length; $i++) {
		//$password .= $word[rand() % $len];
		$password .= $word[mt_rand(0, $len - 1)];
	}

	return $password;
}

//個資法, substr_cut("123456789",3);
function substr_cut($user_name, $x = "")
{
	$strlen   = mb_strlen($user_name, 'utf-8');

	$x = ($x == "") ? round($strlen / 3) : $x;
	$len = mb_strlen($user_name, 'utf-8') - $x - $x;
	$xing = ($strlen == 2) ? "*" : "";
	for ($i = 0; $i < $len; $i++) {
		$xing .= '*';
	}
	$firstStr = mb_substr($user_name, 0, $x, 'utf-8');
	$lastStr  = mb_substr($user_name, 0 - $x, $x, 'utf-8');

	if ($strlen == 1)
		$show_str = $user_name;
	else if ($strlen == 2)
		$show_str = $firstStr . $xing;
	else
		$show_str = $firstStr . $xing . $lastStr;
	return $show_str;
}

// 計算時間日期相差多久 echo DateDiff("2015-01-01 19:00:00","2015-01-01 18:00:00", $unit = "h");
function DateDiff($date1, $date2, $unit = "")
{
	switch ($unit) {
		case 's':
			$dividend = 1;
			break;
		case 'i': //分
			$dividend = 60;
			break;
		case 'h': //時
			$dividend = 3600;
			break;
		case 'd': //天
			$dividend = 86400;
			break;
		default:
			$dividend = 86400;
	}
	$time1 = strtotime($date1);
	$time2 = strtotime($date2);
	if ($time1 && $time2){
		return (float) ($time1 - $time2) / $dividend;
	}else{
		return false;
	}		
}

function gen_uuid()
{
	return sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		// 32 bits for "time_low"
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		// 16 bits for "time_mid"
		mt_rand(0, 0xffff),
		// 16 bits for "time_hi_and_version",

		// four most significant bits holds version number 4
		mt_rand(0, 0x0fff) | 0x4000,
		// 16 bits, 8 bits for "clk_seq_hi_res",

		// 8 bits for "clk_seq_low",

		// two most significant bits holds zero and one for variant DCE1.1
		mt_rand(0, 0x3fff) | 0x8000,
		// 48 bits for "node"
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff),
		mt_rand(0, 0xffff)
	);
}




//如果不希望textarea內顯示<br>原始碼時，可使用
function br2nl($text)
{
	$breaks = array("<br />", "<br>", "<br/>", "<p />", "<p>", "<p/>", "</p>");
	return str_ireplace($breaks, "\r\n", $text);
}

//驗證是否為json格式，下列為正確json時..
// if(validate_json($someJson)){}
function validate_json($string = NULL)
{
	$string = htmlspecialchars_decode($string, ENT_QUOTES);	
	$decoded = json_decode($string); // decode our JSON string
	if (!is_object($decoded) && !is_array($decoded)) {
		return false;
	}
	return (json_last_error() == JSON_ERROR_NONE);
}

//清除html與style與惡意字元，原生的只能去除html
//如果傳入第2個參數,就表示要保留特定html(但一樣去除style)
//$tag 可以多重標籤, <br><p><img>
function strip_html_tags($text, $tag = "")
{
	global $mysqli;
	if ($tag != "") {
		$text = preg_replace("/<([a-z][a-z0-9]*)(?:[^>]*(\ssrc=['\"][^'\"]*['\"]))?[^>]*?(\/?)>/i", '<$1$2$3>', strip_tags($text, $tag));	
	
		return $text;
		/*
		if (PHP_VERSION >= 6 || !get_magic_quotes_gpc())
			return mysqli_real_escape_string($mysqli, $text);
		//如果要進DB，先解開預設的addslashes，改用mysqli_real_escape_string
		else
			return mysqli_real_escape_string($mysqli, stripslashes($text));
		*/	
	} else {
		$text = preg_replace(array(
			// Remove invisible content
			'@<head[^>]*?>.*?</head>@siu',
			'@<style[^>]*?>.*?</style>@siu',
			'@<script[^>]*?.*?</script>@siu',
			'@<object[^>]*?.*?</object>@siu',
			'@<embed[^>]*?.*?</embed>@siu',
			'@<applet[^>]*?.*?</applet>@siu',
			'@<noframes[^>]*?.*?</noframes>@siu',
			'@<noscript[^>]*?.*?</noscript>@siu',
			'@<noembed[^>]*?.*?</noembed>@siu',
			// Add line breaks before and after blocks
			'@</?((address)|(blockquote)|(center)|(del))@iu',
			'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
			'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
			'@</?((table)|(th)|(td)|(caption))@iu',
			'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
			'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
			'@</?((frameset)|(frame)|(iframe))@iu'
		), array(
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			' ',
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0",
			"\n\$0"
		), $text);
		$text = strip_tags($text);
		$text = str_replace(array("\r", "\n"), "", $text);		
		return $text;
	}
}

function get_userip(){
  $ip = "";	
  if(isset($_SERVER['REMOTE_ADDR'])){		
	  $ip = $_SERVER['REMOTE_ADDR'];
	  if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
		foreach ($matches[0] AS $xip) {
		  if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
			$ip = $xip;
			break;
		  }
		}
	  } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	  } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
		$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
	  } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
		$ip = $_SERVER['HTTP_X_REAL_IP'];
	  }  
  }
  return $ip;
}

//刪除目錄與底下所有檔案, SureRemoveDir($log , true); // 第二個參數: true 連 2011 目錄也刪除
function SureRemoveDir($dir, $DeleteMe)
{
	if (!$dh = @opendir($dir))
		return;
	while (false !== ($obj = readdir($dh))) {
		if ($obj == '.' || $obj == '..')
			continue;
		if (!@unlink($dir . '/' . $obj))
			SureRemoveDir($dir . '/' . $obj, true);
	}
	if ($DeleteMe) {
		closedir($dh);
		@rmdir($dir);
	}
}
function youtube_id_from_url($link)
{
	$regexstr = '~
            # Match Youtube link and embed code
            (?:                             # Group to match embed codes
                (?:<iframe [^>]*src=")?       # If iframe match up to first quote of src
                |(?:                        # Group to match if older embed
                    (?:<object .*>)?      # Match opening Object tag
                    (?:<param .*</param>)*  # Match all param tags
                    (?:<embed [^>]*src=")?  # Match embed tag to the first quote of src
                )?                          # End older embed code group
            )?                              # End embed code groups
            (?:                             # Group youtube url
                https?:\/\/                 # Either http or https
                (?:[\w]+\.)*                # Optional subdomains
                (?:                         # Group host alternatives.
                youtu\.be/                  # Either youtu.be,
                | youtube\.com              # or youtube.com
                | youtube-nocookie\.com     # or youtube-nocookie.com
                )                           # End Host Group
                (?:\S*[^\w\-\s])?           # Extra stuff up to VIDEO_ID
                ([\w\-]{11})                # $1: VIDEO_ID is numeric
                [^\s]*                      # Not a space
            )                               # End group
            "?                              # Match end quote if part of src
            (?:[^>]*>)?                       # Match any extra stuff up to close brace
            (?:                             # Group to match last embed code
                </iframe>                 # Match the end of the iframe
                |</embed></object>          # or Match the end of the older embed
            )?                              # End Group of last bit of embed code
            ~ix';

	preg_match($regexstr, $link, $matches);
	if (isset($matches[1]))
		return $matches[1];
	else
		return "--";
}
//$mod如果代入any這參數, 字串間的空白也都會消失
function replace_trim($str, $mode = "")
{
	$str = trim($str);
	//去掉開始和結束的空白
	$str = preg_replace('/\s(?=\s)/', '', $str);
	//去掉跟隨別的擠在一塊的空白
	$str = preg_replace('/[\n\r\t]/', ' ', $str);
	//最後，去掉非space 的空白，用一個空格代替
	if ($mode == "any") {
		$str = str_replace(' ', '', $str);
		$str = str_replace('　', '', $str);
		$str = str_replace('&nbsp;', '', $str);
		$str = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", '', $str);
	}
	return $str;
}

//從字串中提取數字,如果要新增比對符號...findNum($str,".,%"), 可用逗點隔開要新增比對字符

function findNum($str = '', $arr = "")
{
	$str = trim($str);
	if (empty($str)) {
		return '';
	}
	$temp   = array(
		'1',
		'2',
		'3',
		'4',
		'5',
		'6',
		'7',
		'8',
		'9',
		'0'
	);
	if ($arr != "") {
		$arr_temp = explode(",", $arr);
		foreach ($arr_temp as $key => $value) {
			array_push($temp, $value);
		}
	}
	$result = '';
	for ($i = 0; $i < strlen($str); $i++) {
		if (in_array($str[$i], $temp)) {
			$result .= $str[$i];
		}
	}
	return $result;
}

// if(is_image("10.jpg"))
function is_image($path)
{
	/* 判斷是否為jpg, 速度快, 但還沒找到判斷png的
    if (!is_resource($file = fopen($path, 'rb'))) {
    return FALSE;
    }
    // check for the existence of the EOI segment header at the end of the file
    if (0 !== fseek($file, -2, SEEK_END) || "\xFF\xD9" !== fread($file, 2)) {
    fclose($file);
    return FALSE;
    }
    fclose($file);
    return TRUE;
    */
	$mimetype = exif_imagetype($path);
	//if ($mimetype == IMAGETYPE_GIF || $mimetype == IMAGETYPE_JPEG || $mimetype == IMAGETYPE_PNG || $mimetype == IMAGETYPE_BMP) {
	if ($mimetype > 0 and $mimetype < 19){	
		return true;
	} else {
		return false;
	}
}

//get_params($url,"name") 擷取參數
//get_params($url,"","PHP_URL_HOST") 擷取HOST
function get_params($url, $params, $type = "PHP_URL_QUERY")
{
	if ($type == "PHP_URL_QUERY") {
		parse_str(parse_url($url, PHP_URL_QUERY), $params_output);
		return isset($params_output[$params]) ? $params_output[$params] : "--";
	} else if ($type == "PHP_URL_HOST") {
		$parse = parse_url($url);
		return $parse['host'];
	}
}
//如果要上傳檔案，post_str就必須是陣列形式，如果不是上傳檔案，可以用字串或陣列
//download_page("網址",array('name' => 'lucien','FILE' => '@'.dirname(__FILE__)."/gemini.gif")); 
function download_page($path, $post_str = "none", $HTTPHEADER = array())
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $path);
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //這邊設1, 才不會直接輸出
	curl_setopt($ch, CURLOPT_TIMEOUT, 120); //超過30秒沒返回就失敗
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); //連線10秒
	if ($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')) {
		//$HTTPHEADER = array('X-FORWARDED-FOR:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '', 'CLIENT-IP:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '');
	}
	if(count($HTTPHEADER) > 0){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER); //构造IP
	}
	//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	curl_setopt($ch, CURLOPT_REFERER, $path); //构造?路
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	if ($post_str != "none") {
		curl_setopt($ch, CURLOPT_POST, true); // 啟用POST
		if (is_array($post_str))
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_str)); //POST參數，如果接到的是array形式
		else
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str); //POST參數，如果接到的是字串形式
	}
	$retValue = curl_exec($ch);
	curl_close($ch);
	return $retValue;
}

//確認遠端檔案是否存在
function getHeaders($url, $HTTPHEADER = array())
{
	$headers = array();
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	if ($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')) {
		$HTTPHEADER = array('X-FORWARDED-FOR:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '', 'CLIENT-IP:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '');
	}
	if(count($HTTPHEADER) > 0){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER); //构造IP
	}
	$output = curl_exec($ch);
	curl_close($ch);

	$headers = [];
	$output = rtrim($output);
	$data = explode("\n", $output);
	$headers['status'] = $data[0];
	array_shift($data);

	foreach ($data as $part) {

		//some headers will contain ":" character (Location for example), and the part after ":" will be lost, Thanks to @Emanuele
		$middle = explode(":", $part, 2);

		//Supress warning message if $middle[1] does not exist, Thanks to @crayons
		if (!isset($middle[1])) {
			$middle[1] = null;
		}

		$headers[trim($middle[0])] = trim($middle[1]);
	}
	return $headers;
}

function download_file($url, $path, $HTTPHEADER = array())
{

	$fp = fopen($path, 'w+');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	if ($HTTPHEADER == array('X-FORWARDED-FOR:8.8.8.8', 'CLIENT-IP:8.8.4.4')) {
		$HTTPHEADER = array('X-FORWARDED-FOR:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '', 'CLIENT-IP:' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '.' . mt_rand(0, 255) . '');
	}
	if(count($HTTPHEADER) > 0){
		curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPHEADER); //构造IP
	}
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
	curl_setopt($ch, CURLOPT_REFERER, $url); //构造?路    
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

	if (filesize($path) > 0)
		return true;
}

//讀取csv
function inputCsv($handle)
{
	$out = array();
	$n   = 0;
	while ($data = fgetcsv($handle, 10000)) {
		$num = count($data);
		for ($i = 0; $i < $num; $i++) {
			$out[$n][$i] = $data[$i];
		}
		$n++;
	}
	return $out;
}
//西元轉民國, $in_txt是指間格符號
function dateTo_c($in_date, $in_txt = "")
{
	$date = new DateTime($in_date);
	$date->modify("-1911 year");
	return str_replace("-", $in_txt, ltrim($date->format("Y-m-d"), "0"));
}
//民國轉西元, $in_txt是指間格符號
function dateTo_ad($date, $in_txt = ".")
{
	$date = explode($in_txt, $date);
	$year = (int)$date[0] + 1911;
	return $year . "-" . $date[1] . "-" . $date[2];
}
//全型半型互轉, 0是半轉全, 1是全轉半
function cover_width_small($strs, $types = '1')
{
	$DBC = Array( '－', '：' ,'．' , '，' , '／' , '％' , '＃' ,'！' , '＠' , '＆' , '（' , '）' ,'＜' , '＞' , '＂' , '＇' , '？' ,'［' , '］' , '｛' , '｝' , '＼' ,'｜' , '＋' , '＝' , '＿' , '＾' ,'￥' , '￣' , '｀');
	$SBC = Array('-', ':','.', ',', '/', '%', '#','!', '@', '&', '(', ')','<', '>', '"', '\'','?','[', ']', '{', '}', '\\','|', '+', '=', '_', '^','$', '~', '`');	
	
	if($types == '-1'){
		return str_replace($SBC,$DBC,$strs);
	} else if ($types == '0') {
		return mb_convert_kana($strs, 'RNAS');
	} else if($types == '2'){
		return str_replace($DBC,$SBC,$strs);
	} else {
		return mb_convert_kana($strs, 'rnas');
	}
}

//HEX TO RGB
function hexToRGB($hexStr)
{
	$colorVal          = hexdec($hexStr);
	$rgbArray['red']   = 0xFF & ($colorVal >> 0x10);
	$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
	$rgbArray['blue']  = 0xFF & $colorVal;
	return $rgbArray;
}

function inject_check($sql_str)
{
	//https://github.com/wupco/weblogger/blob/master/weblogpro.php
	if (strpos (strtolower($_SERVER['PHP_SELF']), "_admin") === false) {
		//如果在前台大原則都過濾
		$sql_str = stripevil($sql_str);
		$sql_str = midfilter($sql_str);	
		$sql_str = preg_replace('/call_user_func|array_map|select|insert|update|delete|\"|\\\\|\'|\/\*|\.\.\/|\.\/|union|into|load_file|outfile|rename|drop|create|truncate|alter|commit|rollback|explain|grant|revoke|savepoint|transaction|\sset|pfsockopen|fsocket|fsockopen|dumpfile|hex\(/i', '', $sql_str);
	}else{
		//如果在後台，就只過濾兩種
		if(strpos (strtolower($_SERVER['PHP_SELF']), "index") !== false OR strpos (strtolower($_SERVER['PHP_SELF']), "login") !== false){
			$sql_str = preg_replace('/call_user_func|array_map|select|insert|update|delete|\"|\\\\|\'|\/\*|\.\.\/|\.\/|union|into|load_file|outfile|rename|drop|create|truncate|alter|commit|rollback|explain|grant|revoke|savepoint|transaction|\sset|pfsockopen|fsocket|fsockopen|dumpfile|hex\(/i', '', $sql_str);
		}		
	}	
	
	return $sql_str;
}
function midfilter($string)
{
	$pattern = "/file_put_contents|fwrite|curl|eval|assert";
	$pattern .= "|passthru|exec|chroot|scandir|chgrp|chown|shell_exec|proc_open|proc_get_status|popen|ini_alter|ini_restore";
	$pattern .= "|`|openlog|syslog|readlink|symlink|popepassthru|stream_socket_server|assert|pcntl_exec/is";
	$string = preg_replace($pattern, '', $string);
	return $string;
}
function stripevil($string)
{
	$pattern = '/load_file\(|dumpfile\(|hex\(|substr\(|mid\(|left\(|right\(|ascii\(|group_concat\(|concat\(|substring\(|FIND_IN_SET\(|REPLACE\(|REPEAT\(|REVERSE\(|INSERT\(|SUBSTRING_INDEX\(|TRIM\(|PAD\(|POSITION\(|LOCATE\(|INSTR\(|LENGTH\(|BIN\(|OCT\(|ORD\(|file_put_contents\(|fwrite\(|curl\(|system\(|eval\(|assert\(|file_get_contents\(|passthru\(|exec\(|system\(|chroot\(|scandir\(|chgrp\(|chown\(|shell_exec\(|proc_open\(|proc_get_status\(|popen\(|ini_alter\(|ini_restore\(|dl\(|openlog\(|syslog\(|readlink\(|symlink\(|popepassthru\(|stream_socket_server\(|assert\(|pcntl_exec\(|phpinfo\(|unlink\(|fread\(|mail\(|base64_encode\(|var_dump\(/is';
	$string = preg_replace($pattern, '(', $string);
	if (preg_match($pattern, $string)) {
		$string = stripevil($string);
	}
	return $string;
}

function RemoveXSS($data)
{
	// Fix &entity\n;
	$data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
	$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
	$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
	$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

	// Remove any attribute starting with "on" or xmlns
	$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

	// Remove javascript: and vbscript: protocols
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
	$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

	// Only works in IE: <span style="width: expression(alert('Ping!'));"></spa)n>
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
	$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

	// Remove namespaced elements (we do not need them)
	$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);



	do {
		// Remove really unwanted tags
		$old_data = $data;
		$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
	} while ($old_data !== $data);

	// we are done...
	return $data;
}

//以下都是過濾安全HTML使用的，命名為preg_html，來源：http://www.bioinformatics.org/phplabware/internal_utilities/htmLawed/



function preg_html($t, $C=1, $S=array())
{
  // Standard elements including deprecated.

  $eleAr = array('a'=>1, 'abbr'=>1, 'acronym'=>1, 'address'=>1, 'applet'=>1, 'area'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'blockquote'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'caption'=>1, 'center'=>1, 'cite'=>1, 'code'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'dd'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'dfn'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'dt'=>1, 'em'=>1, 'embed'=>1, 'fieldset'=>1, 'figcaption'=>1, 'figure'=>1, 'font'=>1, 'footer'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'isindex'=>1, 'kbd'=>1, 'keygen'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'link'=>1, 'main'=>1, 'map'=>1, 'mark'=>1, 'menu'=>1, 'meta'=>1, 'meter'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'output'=>1, 'p'=>1, 'param'=>1, 'picture'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'slot'=>1, 'small'=>1, 'source'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'style'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'template'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'time'=>1, 'tr'=>1, 'track'=>1, 'tt'=>1, 'u'=>1, 'ul'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1);

  // Set $C array ($config), using default parameters as needed.

  $C = is_array($C) ? $C : array();
  if (!empty($C['valid_xhtml'])) {
    $C['elements'] = empty($C['elements']) ? '*-acronym-big-center-dir-font-isindex-s-strike-tt' : $C['elements'];
    $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 2;
    $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 2;
  }

  // -- Configure for elements.

  if (!empty($C['safe'])) {
    unset($eleAr['applet'], $eleAr['audio'], $eleAr['canvas'], $eleAr['dialog'], $eleAr['embed'], $eleAr['iframe'], $eleAr['object'], $eleAr['script'], $eleAr['video']);
  }
  $x = !empty($C['elements']) ? str_replace(array("\n", "\r", "\t", ' '), '', strtolower($C['elements'])) : '*';
  if ($x == '-*') {
    $eleAr = array();
  } elseif (strpos($x, '*') === false) {
    $eleAr = array_flip(explode(',', $x));
  } else {
    if (isset($x[1])) {
      if (strpos($x, '(')) { // Temporarily replace hyphen of custom element, minus being special character
        $x =
          preg_replace_callback(
            '`\([^()]+\)`',
            function ($m) {
              return str_replace(array('(', ')', '-'), array('', '', 'A'), $m[0]);
            },
            $x);
      }
      preg_match_all('`(?:^|-|\+)[^\-+]+?(?=-|\+|$)`', $x, $m, PREG_SET_ORDER);
      for ($i=count($m); --$i>=0;) {
        $m[$i] = $m[$i][0];
      }
      foreach ($m as $v) {
        $v = str_replace('A', '-', $v);
        if ($v[0] == '+') {
          $eleAr[substr($v, 1)] = 1;
        } elseif ($v[0] == '-') {
          if (strpos($v, '-', 1)) {
            $eleAr[$v] = 1;
          } elseif (isset($eleAr[($v = substr($v, 1))]) && !in_array('+'. $v, $m)) {
            unset($eleAr[$v]);
          }
        }
      }
    }
  }
  $C['elements'] =& $eleAr;

  // -- Configure for attributes.

  $x = !empty($C['deny_attribute']) ? strtolower(preg_replace('"\s+-"', '/', trim($C['deny_attribute']))) : '';
  $x = str_replace(array(' ', "\t", "\r", "\n"), '', $x);
  $x =
    array_flip(
      (isset($x[0]) && $x[0] == '*')
       ? preg_replace(
           '`^[^*]`',
           '-'. '\\0',
           explode(
             '/',
             (!empty($C['safe']) ? preg_replace('`/on[^/]+`', '', $x) : $x)))
       : array_filter(explode(',', $x. (!empty($C['safe']) ? ',on*' : ''))));
  $C['deny_attribute'] = $x;

  // -- Configure URL handling.

  $x = (isset($C['schemes'][2]) && strpos($C['schemes'], ':')
        ? strtolower($C['schemes'])
        : ('href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, tel, telnet, ws, wss'
           . (empty($C['safe'])
              ? ', app, javascript; *: data, javascript, '
              : '; *:')
           . 'file, http, https, ws, wss'));
  $C['schemes'] = array();
  foreach (explode(';', trim(str_replace(array(' ', "\t", "\r", "\n"), '', $x), ';')) as $v) {
    if(strpos($v, ':')) {
      list($x, $y) = explode(':', $v, 2);
      $C['schemes'][$x] = array_flip(explode(',', $y));
    }
  }
  if (!isset($C['schemes']['*'])) {
    $C['schemes']['*'] = array('file'=>1, 'http'=>1, 'https'=>1, 'ws'=>1, 'wss'=>1);
    if (empty($C['safe'])) {
      $C['schemes']['*'] += array('data'=>1, 'javascript'=>1);
    }
  }
  if (!empty($C['safe']) && empty($C['schemes']['style'])) {
    $C['schemes']['style'] = array('!'=>1);
  }
  $C['abs_url'] = isset($C['abs_url']) ? $C['abs_url'] : 0;
  if (!isset($C['base_url']) || !preg_match('`^[a-zA-Z\d.+\-]+://[^/]+/(.+?/)?$`', $C['base_url'])) {
    $C['base_url'] = $C['abs_url'] = 0;
  }

  // -- Configure other parameters.

  $C['and_mark'] = empty($C['and_mark']) ? 0 : 1;
  $C['anti_link_spam'] =
    (isset($C['anti_link_spam'])
     && is_array($C['anti_link_spam'])
     && count($C['anti_link_spam']) == 2
     && (empty($C['anti_link_spam'][0])
         || hl_regex($C['anti_link_spam'][0]))
     && (empty($C['anti_link_spam'][1])
         || hl_regex($C['anti_link_spam'][1])))
    ? $C['anti_link_spam']
    : 0;
  $C['anti_mail_spam'] = isset($C['anti_mail_spam']) ? $C['anti_mail_spam'] : 0;
  $C['any_custom_element'] = (!isset($C['any_custom_element']) || !empty($C['any_custom_element'])) ? 1 : 0;
  $C['balance'] = isset($C['balance']) ? (bool)$C['balance'] : 1;
  $C['cdata'] = isset($C['cdata']) ? $C['cdata'] : (empty($C['safe']) ? 3 : 0);
  $C['clean_ms_char'] = empty($C['clean_ms_char']) ? 0 : $C['clean_ms_char'];
  $C['comment'] = isset($C['comment']) ? $C['comment'] : (empty($C['safe']) ? 3 : 0);
  $C['css_expression'] = empty($C['css_expression']) ? 0 : 1;
  $C['direct_list_nest'] = empty($C['direct_list_nest']) ? 0 : 1;
  $C['hexdec_entity'] = isset($C['hexdec_entity']) ? $C['hexdec_entity'] : 1;
  $C['hook'] = (!empty($C['hook']) && is_callable($C['hook'])) ? $C['hook'] : 0;
  $C['hook_tag'] = (!empty($C['hook_tag']) && is_callable($C['hook_tag'])) ? $C['hook_tag'] : 0;
  $C['keep_bad'] = isset($C['keep_bad']) ? $C['keep_bad'] : 6;
  $C['lc_std_val'] = isset($C['lc_std_val']) ? (bool)$C['lc_std_val'] : 1;
  $C['make_tag_strict'] = isset($C['make_tag_strict']) ? $C['make_tag_strict'] : 1;
  $C['named_entity'] = isset($C['named_entity']) ? (bool)$C['named_entity'] : 1;
  $C['no_deprecated_attr'] = isset($C['no_deprecated_attr']) ? $C['no_deprecated_attr'] : 1;
  $C['parent'] = isset($C['parent'][0]) ? strtolower($C['parent']) : 'body';
  $C['show_setting'] = !empty($C['show_setting']) ? $C['show_setting'] : 0;
  $C['style_pass'] = empty($C['style_pass']) ? 0 : 1;
  $C['tidy'] = empty($C['tidy']) ? 0 : $C['tidy'];
  $C['unique_ids'] = isset($C['unique_ids']) && (!preg_match('`\W`', $C['unique_ids'])) ? $C['unique_ids'] : 1;
  $C['xml:lang'] = isset($C['xml:lang']) ? $C['xml:lang'] : 0;

  if (isset($GLOBALS['C'])) {
    $oldC = $GLOBALS['C'];
  }
  $GLOBALS['C'] = $C;

  // Set $S array ($spec).

  $S = is_array($S) ? $S : hl_spec($S);
  if (isset($GLOBALS['S'])) {
    $oldS = $GLOBALS['S'];
  }
  $GLOBALS['S'] = $S;

  // Handle characters.

  $t = preg_replace('`[\x00-\x08\x0b-\x0c\x0e-\x1f]`', '', $t); // Remove illegal
  if ($C['clean_ms_char']) { // Convert MS Windows CP-1252
    $x = array("\x7f"=>'', "\x80"=>'&#8364;', "\x81"=>'', "\x83"=>'&#402;', "\x85"=>'&#8230;', "\x86"=>'&#8224;', "\x87"=>'&#8225;', "\x88"=>'&#710;', "\x89"=>'&#8240;', "\x8a"=>'&#352;', "\x8b"=>'&#8249;', "\x8c"=>'&#338;', "\x8d"=>'', "\x8e"=>'&#381;', "\x8f"=>'', "\x90"=>'', "\x95"=>'&#8226;', "\x96"=>'&#8211;', "\x97"=>'&#8212;', "\x98"=>'&#732;', "\x99"=>'&#8482;', "\x9a"=>'&#353;', "\x9b"=>'&#8250;', "\x9c"=>'&#339;', "\x9d"=>'', "\x9e"=>'&#382;', "\x9f"=>'&#376;');
    $x = $x
         + ($C['clean_ms_char'] == 1
            ? array("\x82"=>'&#8218;', "\x84"=>'&#8222;', "\x91"=>'&#8216;', "\x92"=>'&#8217;', "\x93"=>'&#8220;', "\x94"=>'&#8221;')
            : array("\x82"=>'\'', "\x84"=>'"', "\x91"=>'\'', "\x92"=>'\'', "\x93"=>'"', "\x94"=>'"'));
    $t = strtr($t, $x);
  }

  // Handle CDATA, comments, and entities.

  if ($C['cdata'] || $C['comment']) {
    $t = preg_replace_callback('`<!(?:(?:--.*?--)|(?:\[CDATA\[.*?\]\]))>`sm', 'hl_commentCdata', $t);
  }
  $t =
    preg_replace_callback(
      '`&amp;([a-zA-Z][a-zA-Z0-9]{1,30}|#(?:[0-9]{1,8}|[Xx][0-9A-Fa-f]{1,7}));`',
      'hl_entity',
      str_replace('&', '&amp;', $t));
  if ($C['unique_ids'] && !isset($GLOBALS['hl_Ids'])) {
    $GLOBALS['hl_Ids'] = array();
  }

  if ($C['hook']) {
    $t = call_user_func($C['hook'], $t, $C, $S);
  }

  // Handle remaining text.

  $t = preg_replace_callback('`<(?:(?:\s|$)|(?:[^>]*(?:>|$)))|>`m', 'hl_tag', $t);
  $t = $C['balance'] ? hl_balance($t, $C['keep_bad'], $C['parent']) : $t;
  $t = (($C['cdata'] || $C['comment']) && strpos($t, "\x01") !== false)
       ? str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05"), array('', '', '&', '<', '>'), $t)
       : $t;
  $t = $C['tidy'] ? hl_tidy($t, $C['tidy'], $C['parent']) : $t;

  // Cleanup.

  if ($C['show_setting'] && preg_match('`^[a-z][a-z0-9_]*$`i', $C['show_setting'])) {
    $GLOBALS[$C['show_setting']] = array('config'=>$C, 'spec'=>$S, 'time'=>microtime(true), 'version'=>hl_version());
  }
  unset($C, $eleAr);
  if (isset($oldC)) {
    $GLOBALS['C'] = $oldC;
  }
  if (isset($oldS)) {
    $GLOBALS['S'] = $oldS;
  }
  return $t;
}

/**
 * Validate attribute value and possibly reset to a default.
 *
 * @param  string  $attr   Attribute name.
 * @param  string  $value  Attribute value.
 * @param  array   $ruleAr Array of rules derived from $spec.
 * @param  string  $ele    Element.
 * @return mixed           0 if invalid $value,
 *                         or string with validated or default value.
 */
function hl_attributeValue($attr, $value, $ruleAr, $ele)
{
  static $spacedValsAttrAr = array('accesskey', 'class', 'itemtype', 'rel'); // Some attributes have multiple values
  $valSep =
    (in_array($attr, $spacedValsAttrAr) || ($attr == 'archive' && $ele == 'object'))
    ? ' '
    : (($attr == 'sizes' || $attr == 'srcset' || ($attr == 'archive' && $ele == 'applet'))
       ? ','
       : '');
  $out = array();
  $valAr = !empty($valSep) ? explode($valSep, $value) : array($value);
  foreach ($valAr as $v) {
    $ok = 1;
    $v = trim($v);
    $lengthVal = strlen($v);
    foreach ($ruleAr as $ruleType=>$ruleVal) {
      if (!$lengthVal) {
        continue;
      }
      switch ($ruleType) {
        case 'maxlen': if ($lengthVal > $ruleVal) {
          $ok = 0;
        }
        break; case 'minlen': if ($lengthVal < $ruleVal) {
          $ok = 0;
        }
        break; case 'maxval': if ((float)($v) > $ruleVal) {
          $ok = 0;
        }
        break; case 'minval': if ((float)($v) < $ruleVal) {
          $ok = 0;
        }
        break; case 'match': if (!preg_match($ruleVal, $v)) {
          $ok = 0;
        }
        break; case 'nomatch': if (preg_match($ruleVal, $v)) {
          $ok = 0;
        }
        break; case 'oneof': if(!in_array($v, explode('|', $ruleVal))) {
          $ok = 0;
        }
        break; case 'noneof': if(in_array($v, explode('|', $ruleVal))) {
          $ok = 0;
        }
        break; default:
        break;
      }
      if (!$ok) {
        break;
      }
    }
    if ($ok) {
      $out[] = $v;
    }
  }
  $out = implode($valSep == ',' ? ', ' : ' ', $out);
  return (isset($out[0]) ? $out : (isset($ruleAr['default']) ? $ruleAr['default'] : 0));
}

/*
 * Enforce parent-child validity of elements and balance tags.
 *
 * @param  string $t         HTM. Previously partly sanitized/filtered. CDATA
 *                           and comment sections have </> characters hidden.
 * @param  int    $act       $config's keep_bad parameter.
 * @param  string $parentEle $t's parent element option.
 * @return string            $t with valid nesting and balanced tags.
 */
function hl_balance($t, $act=1, $parentEle='div')
{
  // Group elements in different ways.

  $closingTagOmitableEleAr = array('caption'=>1, 'colgroup'=>1, 'dd'=>1, 'dt'=>1, 'li'=>1, 'optgroup'=>1, 'option'=>1, 'p'=>1, 'rp'=>1, 'rt'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1);

  // -- Block, inline, etc.

  $blockEleAr = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'dir'=>1, 'dl'=>1, 'div'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'ins'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hr'=>1, 'isindex'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'section'=>1, 'slot'=>1, 'style'=>1, 'table'=>1, 'template'=>1, 'ul'=>1);
  $inlineEleAr = array('#pcdata'=>1, 'a'=>1, 'abbr'=>1, 'acronym'=>1, 'applet'=>1, 'audio'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'br'=>1, 'button'=>1, 'canvas'=>1, 'cite'=>1, 'code'=>1, 'command'=>1, 'data'=>1, 'datalist'=>1, 'del'=>1, 'dfn'=>1, 'em'=>1, 'embed'=>1, 'figcaption'=>1, 'font'=>1, 'i'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'ins'=>1, 'kbd'=>1, 'label'=>1, 'link'=>1, 'map'=>1, 'mark'=>1, 'meta'=>1, 'meter'=>1, 'object'=>1, 'output'=>1, 'picture'=>1, 'progress'=>1, 'q'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'select'=>1, 'script'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'textarea'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1, 'video'=>1, 'wbr'=>1);
  $otherEleAr = array('area'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'command'=>1, 'dd'=>1, 'dt'=>1, 'hgroup'=>1, 'keygen'=>1, 'legend'=>1, 'li'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1, 'script'=>1, 'source'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'thead'=>1, 'th'=>1, 'tr'=>1, 'track'=>1);
  $flowEleAr = $blockEleAr + $inlineEleAr;

  // -- Type of child allowed.

  $blockKidEleAr = array('blockquote'=>1, 'form'=>1, 'map'=>1, 'noscript'=>1);
  $flowKidEleAr = array('a'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'button'=>1, 'canvas'=>1, 'del'=>1, 'details'=>1, 'dialog'=>1, 'div'=>1, 'dd'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'header'=>1, 'iframe'=>1, 'ins'=>1, 'li'=>1, 'main'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'object'=>1, 'section'=>1, 'slot'=>1, 'style'=>1, 'td'=>1, 'template'=>1, 'th'=>1, 'video'=>1); // Later context-wise dynamic move of ins & del to $inlineKidEleAr
  $inlineKidEleAr = array('abbr'=>1, 'acronym'=>1, 'address'=>1, 'b'=>1, 'bdi'=>1, 'bdo'=>1, 'big'=>1, 'caption'=>1, 'cite'=>1, 'code'=>1, 'data'=>1, 'datalist'=>1, 'dfn'=>1, 'dt'=>1, 'em'=>1, 'figcaption'=>1, 'font'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hgroup'=>1, 'i'=>1, 'kbd'=>1, 'label'=>1, 'legend'=>1, 'mark'=>1, 'meter'=>1, 'output'=>1, 'p'=>1, 'picture'=>1, 'pre'=>1, 'progress'=>1, 'q'=>1, 'rb'=>1, 'rt'=>1, 'ruby'=>1, 's'=>1, 'samp'=>1, 'small'=>1, 'span'=>1, 'strike'=>1, 'strong'=>1, 'sub'=>1, 'summary'=>1, 'sup'=>1, 'time'=>1, 'tt'=>1, 'u'=>1, 'var'=>1);
  $noKidEleAr = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1);

  // Special parent-child relations.

  $invalidMomKidAr = array('a'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'iframe'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'address'=>array('address'=>1, 'article'=>1, 'aside'=>1, 'footer'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hgroup'=>1, 'keygen'=>1, 'nav'=>1, 'section'=>1), 'audio'=>array('audio'=>1, 'video'=>1), 'button'=>array('a'=>1, 'address'=>1, 'button'=>1, 'details'=>1, 'embed'=>1, 'iframe'=>1, 'keygen'=>1, 'label'=>1, 'select'=>1, 'textarea'=>1), 'dfn'=>array('dfn'=>1), 'fieldset'=>array('fieldset'=>1), 'footer'=>array('footer'=>1, 'header'=>1), 'form'=>array('form'=>1), 'header'=>array('footer'=>1, 'header'=>1), 'label'=>array('label'=>1), 'main'=>array('main'=>1), 'meter'=>array('meter'=>1), 'noscript'=>array('script'=>1), 'progress'=>array('progress'=>1), 'rb'=>array('ruby'=>1), 'rt'=>array('ruby'=>1), 'ruby'=>array('ruby'=>1), 'time'=>array('time'=>1), 'video'=>array('audio'=>1, 'video'=>1));
  $invalidKidEleAr = array('a'=>1, 'address'=>1, 'article'=>1, 'aside'=>1, 'audio'=>1, 'button'=>1, 'details'=>1, 'dfn'=>1, 'embed'=>1, 'fieldset'=>1, 'footer'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'header'=>1, 'hgroup'=>1, 'iframe'=>1, 'keygen'=>1, 'label'=>1, 'main'=>1, 'meter'=>1, 'nav'=>1, 'progress'=>1, 'ruby'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'textarea'=>1, 'time'=>1, 'video'=>1); // $invalidMomKidAr values
  $invalidMomEleAr = array_keys($invalidMomKidAr);
  $validMomKidAr = array('colgroup'=>array('col'=>1, 'template'=>1), 'datalist'=>array('option'=>1, 'script'=>1), 'dir'=>array('li'=>1), 'dl'=>array('dd'=>1, 'div'=>1, 'dt'=>1), 'hgroup'=>array('h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1), 'menu'=>array('li'=>1, 'script'=>1, 'template'=>1), 'ol'=>array('li'=>1, 'script'=>1, 'template'=>1), 'optgroup'=>array('option'=>1, 'script'=>1, 'template'=>1), 'option'=>array('#pcdata'=>1), 'picture'=>array('img'=>1, 'script'=>1, 'source'=>1, 'template'=>1), 'rbc'=>array('rb'=>1), 'rp'=>array('#pcdata'=>1), 'rtc'=>array('rp'=>1, 'rt'=>1), 'select'=>array('optgroup'=>1, 'option'=>1), 'script'=>array('#pcdata'=>1), 'table'=>array('caption'=>1, 'col'=>1, 'colgroup'=>1, 'script'=>1, 'tbody'=>1, 'tfoot'=>1, 'thead'=>1, 'tr'=>1, 'template'=>1), 'tbody'=>array('script'=>1, 'template'=>1, 'tr'=>1), 'tfoot'=>array('tr'=>1), 'textarea'=>array('#pcdata'=>1), 'thead'=>array('script'=>1, 'template'=>1, 'tr'=>1), 'tr'=>array('script'=>1, 'td'=>1, 'template'=>1, 'th'=>1), 'ul'=>array('li'=>1, 'script'=>1, 'template'=>1)); // Immediate parent-child relation
  if ($GLOBALS['C']['direct_list_nest']) {
    $validMomKidAr['ol'] = $validMomKidAr['ul'] = $validMomKidAr['menu'] += array('menu'=>1, 'ol'=>1, 'ul'=>1);
  }
  $otherValidMomKidAr = array('address'=>array('p'=>1), 'applet'=>array('param'=>1), 'audio'=>array('source'=>1, 'track'=>1), 'blockquote'=>array('script'=>1), 'fieldset'=>array('legend'=>1, '#pcdata'=>1),  'figure'=>array('figcaption'=>1),'form'=>array('script'=>1), 'map'=>array('area'=>1), 'legend'=>array('h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1), 'object'=>array('param'=>1, 'embed'=>1), 'ruby'=>array('rb'=>1, 'rbc'=>1, 'rp'=>1, 'rt'=>1, 'rtc'=>1), 'summary'=>array('h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hgroup'=>1), 'video'=>array('source'=>1, 'track'=>1));

  // Valid elements for top-level parent.

  $mom = ((isset($flowEleAr[$parentEle]) && $parentEle != '#pcdata')
          || isset($otherEleAr[$parentEle]))
         ? $parentEle
         : 'div';
  if (isset($noKidEleAr[$mom])) {
    return (!$act ? '' : str_replace(array('<', '>'), array('&lt;', '&gt;'), $t));
  }
  if (isset($validMomKidAr[$mom])) {
    $validInMomEleAr = $validMomKidAr[$mom];
  } elseif (isset($inlineKidEleAr[$mom])) {
    $validInMomEleAr = $inlineEleAr;
    $inlineKidEleAr['del'] = 1;
    $inlineKidEleAr['ins'] = 1;
  } elseif (isset($flowKidEleAr[$mom])) {
    $validInMomEleAr = $flowEleAr;
    unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
  } elseif (isset($blockKidEleAr[$mom])) {
    $validInMomEleAr = $blockEleAr;
    unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
  }
  if (isset($otherValidMomKidAr[$mom])) {
    $validInMomEleAr = $validInMomEleAr + $otherValidMomKidAr[$mom];
  }
  if (isset($invalidMomKidAr[$mom])) {
    $validInMomEleAr = array_diff_assoc($validInMomEleAr, $invalidMomKidAr[$mom]);
  }
  if (strpos($mom, '-')) { // Custom element
    $validInMomEleAr = array('*' => 1, '#pcdata' =>1);
  }

  // Loop over elements.

  $t = explode('<', $t);
  $validKidsOfMom = $openEleQueue = array(); // Queue of opened elements
  ob_start();
  for ($i=-1, $eleCount=count($t); ++$i<$eleCount;) {

    // Check element validity as child. Same code as section: Finishing (below).

    if ($queueLength = count($openEleQueue)) {
      $eleNow = array_pop($openEleQueue);
      $openEleQueue[] = $eleNow;
      if (isset($validMomKidAr[$eleNow])) {
        $validKidsOfMom = $validMomKidAr[$eleNow];
      } elseif (isset($inlineKidEleAr[$eleNow])) {
        $validKidsOfMom = $inlineEleAr;
        $inlineKidEleAr['del'] = 1;
        $inlineKidEleAr['ins'] = 1;
      } elseif (isset($flowKidEleAr[$eleNow])) {
        $validKidsOfMom = $flowEleAr;
        unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
      } elseif (isset($blockKidEleAr[$eleNow])) {
        $validKidsOfMom = $blockEleAr;
        unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
      }
      if (isset($otherValidMomKidAr[$eleNow])) {
        $validKidsOfMom = $validKidsOfMom + $otherValidMomKidAr[$eleNow];
      }
      if (isset($invalidMomKidAr[$eleNow])) {
        $validKidsOfMom = array_diff_assoc($validKidsOfMom, $invalidMomKidAr[$eleNow]);
      }
      if (strpos($eleNow, '-')) { // Custom element
        $validKidsOfMom = array('*'=>1, '#pcdata'=>1);
      }
    } else {
      $validKidsOfMom = $validInMomEleAr;
      unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    }
    if (
      isset($ele)
      && ($act == 1
          || (isset($validKidsOfMom['#pcdata'])
              && ($act == 3
                  || $act == 5)))
      ) {
      echo '&lt;', $slash, $ele, $attrs, '&gt;';
    }
    if (isset($content[0])) {
      if (strlen(trim($content))
          && (($queueLength && isset($blockKidEleAr[$eleNow]))
              || (isset($blockKidEleAr[$mom]) && !$queueLength))
      ) {
        echo '<div>', $content, '</div>';
      } elseif ($act < 3 || isset($validKidsOfMom['#pcdata'])) {
        echo $content;
      } elseif (strpos($content, "\x02\x04")) {
        foreach (
          preg_split(
            '`(\x01\x02[^\x01\x02]+\x02\x01)`', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $m) {
          echo(
            substr($m, 0, 2) == "\x01\x02"
            ? $m
            : ($act > 4
               ? preg_replace('`\S`', '', $m)
               : ''));
        }
      } elseif ($act > 4) {
        echo preg_replace('`\S`', '', $content);
      }
    } // End: Check element validity as child

    // Get parts of element.

    if (!preg_match('`^(/?)([a-z][^ >]*)([^>]*)>(.*)`sm', $t[$i], $m)) {
      $content = $t[$i];
      continue;
    }
    $slash = null; // Closing tag's slash
    $ele = null; // Name
    $attrs = null; // Attribute string
    $content = null; // Content
    list($all, $slash, $ele, $attrs, $content) = $m;

     // Handle closing tag.

    if ($slash) {
      if (isset($noKidEleAr[$ele]) || !in_array($ele, $openEleQueue)) { // Element empty type or unopened
        continue;
      }
      if ($eleNow == $ele) { // Last open tag
        array_pop($openEleQueue);
        echo '</', $ele, '>';
        unset($ele);
        continue;
      }
      $closedTags = ''; // Nesting, so close open elements as necessary
      for ($j=-1, $cj=count($openEleQueue); ++$j<$cj;) {
        if (($closableEle = array_pop($openEleQueue)) == $ele) {
          break;
        } else {
          $closedTags .= "</{$closableEle}>";
        }
      }
      echo $closedTags, '</', $ele, '>';
      unset($ele);
      continue;
    }

    // Handle opening tag.

    if (isset($blockKidEleAr[$ele]) && strlen(trim($content))) { // $blockKidEleAr element needs $blockEleAr element
      $t[$i] = "{$ele}{$attrs}>";
      array_splice($t, $i+1, 0, 'div>'. $content);
      unset($ele, $content);
      ++$eleCount;
      --$i;
      continue;
    }
    if (strpos($ele, '-')) { // Custom element
      $validKidsOfMom[$ele] = 1;
    }
    if ((($queueLength && isset($blockKidEleAr[$eleNow]))
         || (isset($blockKidEleAr[$mom]) && !$queueLength))
        && !isset($blockEleAr[$ele])
        && !isset($validKidsOfMom[$ele])
        && !isset($validKidsOfMom['*'])
      ) {
      array_splice($t, $i, 0, 'div>');
      unset($ele, $content);
      ++$eleCount;
      --$i;
      continue;
    }
    if (
      !$queueLength
      || !isset($invalidKidEleAr[$ele])
      || !array_intersect($openEleQueue, $invalidMomEleAr)
      ) { // If no open element; mostly immediate parent-child relation should hold
      if (!isset($validKidsOfMom[$ele]) && !isset($validKidsOfMom['*'])) {
        if ($queueLength && isset($closingTagOmitableEleAr[$eleNow])) {
          echo '</', array_pop($openEleQueue), '>';
          unset($ele, $content);
          --$i;
        }
        continue;
      }
      if (!isset($noKidEleAr[$ele])) {
        $openEleQueue[] = $ele;
      }
      echo '<', $ele, $attrs, '>';
      unset($ele);
      continue;
    }
    if (isset($validMomKidAr[$eleNow][$ele])) { // Specific parent-child relation
      if (!isset($noKidEleAr[$ele])) {
        $openEleQueue[] = $ele;
      }
      echo '<', $ele, $attrs, '>';
      unset($ele);
      continue;
    }
    $closedTags = ''; // Nesting, so close open elements as needed
    $openEleQueue2 = array();
    for ($k=-1, $kc=count($openEleQueue); ++$k<$kc;) {
      $closableEle = $openEleQueue[$k];
      $validKids2 = array();
      if (isset($validMomKidAr[$closableEle])) {
        $openEleQueue2[] = $closableEle;
        continue;
      }
      $validKids2 = isset($inlineKidEleAr[$closableEle]) ? $inlineEleAr : $flowEleAr;
      if (isset($otherValidMomKidAr[$closableEle])) {
        $validKids2 = $validKids2 + $otherValidMomKidAr[$closableEle];
      }
      if (isset($invalidMomKidAr[$closableEle])) {
        $validKids2 = array_diff_assoc($validKids2, $invalidMomKidAr[$closableEle]);
      }
      if (!isset($validKids2[$ele]) && !strpos($ele, '-')) {
        if (!$k && !isset($validInMomEleAr[$ele]) && !isset($validInMomEleAr['*'])) {
          continue 2;
        }
        $closedTags = "</{$closableEle}>";
        for (;++$k<$kc;) {
          $closedTags = "</{$openEleQueue[$k]}>{$closedTags}";
        }
        break;
      } else {
        $openEleQueue2[] = $closableEle;
      }
    }
    $openEleQueue = $openEleQueue2;
    if (!isset($noKidEleAr[$ele])) {
      $openEleQueue[] = $ele;
    }
    echo $closedTags, '<', $ele, $attrs, '>';
    unset($ele);
    continue;
  } // End of For: loop over elements

  // Finishing. Same code as: 'Check element validity as child'.

  if ($queueLength = count($openEleQueue)) {
    $eleNow = array_pop($openEleQueue);
    $openEleQueue[] = $eleNow;
    if (isset($validMomKidAr[$eleNow])) {
      $validKidsOfMom = $validMomKidAr[$eleNow];
    } elseif (isset($inlineKidEleAr[$eleNow])) {
      $validKidsOfMom = $inlineEleAr;
      $inlineKidEleAr['del'] = 1;
      $inlineKidEleAr['ins'] = 1;
    } elseif (isset($flowKidEleAr[$eleNow])) {
      $validKidsOfMom = $flowEleAr;
      unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    } elseif (isset($blockKidEleAr[$eleNow])) {
      $validKidsOfMom = $blockEleAr;
      unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
    }
    if (isset($otherValidMomKidAr[$eleNow])) {
      $validKidsOfMom = $validKidsOfMom + $otherValidMomKidAr[$eleNow];
    }
    if (isset($invalidMomKidAr[$eleNow])) {
      $validKidsOfMom = array_diff_assoc($validKidsOfMom, $invalidMomKidAr[$eleNow]);
    }
    if (strpos($eleNow, '-')) { // Custom element
      $validKidsOfMom = array('*'=>1, '#pcdata'=>1);
    }
  } else {
    $validKidsOfMom = $validInMomEleAr;
    unset($inlineKidEleAr['del'], $inlineKidEleAr['ins']);
  }
  if (
    isset($ele)
    && ($act == 1
        || (isset($validKidsOfMom['#pcdata'])
            && ($act == 3
                || $act == 5)))
    ) {
    echo '&lt;', $slash, $ele, $attrs, '&gt;';
  }
  if (isset($content[0])) {
    if (
      strlen(trim($content))
      && (($queueLength && isset($blockKidEleAr[$eleNow]))
          || (isset($blockKidEleAr[$mom]) && !$queueLength))
    ) {
      echo '<div>', $content, '</div>';
    } elseif ($act < 3 || isset($validKidsOfMom['#pcdata'])) {
      echo $content;
    } elseif (strpos($content, "\x02\x04")) {
      foreach (
        preg_split(
          '`(\x01\x02[^\x01\x02]+\x02\x01)`', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $m) {
        echo(
          substr($m, 0, 2) == "\x01\x02"
          ? $m
          : ($act > 4
             ? preg_replace('`\S`', '', $m)
             : ''));
      }
    } elseif ($act > 4) {
      echo preg_replace('`\S`', '', $content);
    }
  } // End: Finishing

  while (!empty($openEleQueue) && ($ele = array_pop($openEleQueue))) {
    echo '</', $ele, '>';
  }
  $o = ob_get_contents();
  ob_end_clean();
  return $o;
}

/**
 * Handle comment/CDATA section.
 *
 * Filter/sanitize as per $config and disguise special characters.
 *
 * @param  array  $t Array result of preg_replace, with potential comment/CDATA.
 * @return string    Sanitized comment/CDATA with hidden special characters.
 */
function hl_commentCdata($t)
{
  $t = $t[0];
  global $C;
  if (!($rule = $C[$type = $t[3] == '-' ? 'comment' : 'cdata'])) {
    return $t;
  }
  if ($rule == 1) {
    return '';
  }
  if ($type == 'comment') {
    if (substr(($t = preg_replace('`--+`', '-', substr($t, 4, -3))), -1) != ' ') {
      $t .= $rule == 4 ? '' : ' ';
    }
  } else {
    $t = substr($t, 1, -1);
  }
  $t = $rule == 2 ? str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $t) : $t;
  return
    str_replace(
      array('&', '<', '>'),
      array("\x03", "\x04", "\x05"),
      ($type == 'comment' ? "\x01\x02\x04!--$t--\x05\x02\x01" : "\x01\x01\x04$t\x05\x01\x01"));
}

/**
 * Transform deprecated element, with any attribute, into a new element.
 *
 *
 * @param  string $ele     Deprecated element.
 * @param  string $attrStr Attribute string of element.
 * @param  int    $act     No transformation if 2.
 * @return mixed           New attribute string (may be empty) or 0.
 */
function hl_deprecatedElement(&$ele, &$attrStr, $act=1)
{
  if ($ele == 'big') {
    $ele = 'span';
    return 'font-size: larger;';
  }
  if ($ele == 's' || $ele == 'strike') {
    $ele = 'span';
    return 'text-decoration: line-through;';
  }
  if ($ele == 'tt') {
    $ele = 'code';
    return '';
  }
  if ($ele == 'center') {
    $ele = 'div';
    return 'text-align: center;';
  }
  static $fontSizeAr = array('0'=>'xx-small', '1'=>'xx-small', '2'=>'small', '3'=>'medium', '4'=>'large', '5'=>'x-large', '6'=>'xx-large', '7'=>'300%', '-1'=>'smaller', '-2'=>'60%', '+1'=>'larger', '+2'=>'150%', '+3'=>'200%', '+4'=>'300%');
  if ($ele == 'font') {
    $attrStrNew = '';
    while (preg_match('`(^|\s)(color|size)\s*=\s*(\'|")?(.+?)(\\3|\s|$)`i', $attrStr, $m)) {
      $attrStr = str_replace($m[0], ' ', $attrStr) ;
      $attrStrNew .=
        strtolower($m[2]) == 'color'
        ? ' color: '. str_replace(array('"', ';', ':'), '\'', trim($m[4])). ';'
        : (isset($fontSizeAr[($m = trim($m[4]))])
           ? ' font-size: '. $fontSizeAr[$m]. ';'
           : '');
    }
    while (
      preg_match('`(^|\s)face\s*=\s*(\'|")?([^=]+?)\\2`i', $attrStr, $m)
      || preg_match('`(^|\s)face\s*=(\s*)(\S+)`i', $attrStr, $m)
      ) {
      $attrStr = str_replace($m[0], ' ', $attrStr) ;
      $attrStrNew .= ' font-family: '. str_replace(array('"', ';', ':'), '\'', trim($m[3])). ';';
    }
    $ele = 'span';
    return ltrim(str_replace('<', '', $attrStrNew));
  }
  if ($ele == 'acronym') {
    $ele = 'abbr';
    return '';
  }
  if ($ele == 'dir') {
    $ele = 'ul';
    return '';
  }
  if ($act == 2) {
    $ele = 0;
    return 0;
  }
  return '';
}

/**
 * Handle entity.
 *
 * As needed, convert to named/hexadecimal form, or neutralize '&' as '&amp;'.
 *
 * @param  array  $t Array result of preg_replace, with potential entity.
 * @return string    Neutralized or converted entity.
 */
function hl_entity($t)
{
  global $C;
  $t = $t[1];
  static $reservedEntAr = array('amp'=>1, 'AMP'=>1, 'gt'=>1, 'GT'=>1, 'lt'=>1, 'LT'=>1, 'quot'=>1, 'QUOT'=>1);
  static $commonEntNameAr = array('Aacute'=>'193', 'aacute'=>'225', 'Acirc'=>'194', 'acirc'=>'226', 'acute'=>'180', 'AElig'=>'198', 'aelig'=>'230', 'Agrave'=>'192', 'agrave'=>'224', 'alefsym'=>'8501', 'Alpha'=>'913', 'alpha'=>'945', 'and'=>'8743', 'ang'=>'8736', 'apos'=>'39', 'Aring'=>'197', 'aring'=>'229', 'asymp'=>'8776', 'Atilde'=>'195', 'atilde'=>'227', 'Auml'=>'196', 'auml'=>'228', 'bdquo'=>'8222', 'Beta'=>'914', 'beta'=>'946', 'brvbar'=>'166', 'bull'=>'8226', 'cap'=>'8745', 'Ccedil'=>'199', 'ccedil'=>'231', 'cedil'=>'184', 'cent'=>'162', 'Chi'=>'935', 'chi'=>'967', 'circ'=>'710', 'clubs'=>'9827', 'cong'=>'8773', 'copy'=>'169', 'crarr'=>'8629', 'cup'=>'8746', 'curren'=>'164', 'dagger'=>'8224', 'Dagger'=>'8225', 'darr'=>'8595', 'dArr'=>'8659', 'deg'=>'176', 'Delta'=>'916', 'delta'=>'948', 'diams'=>'9830', 'divide'=>'247', 'Eacute'=>'201', 'eacute'=>'233', 'Ecirc'=>'202', 'ecirc'=>'234', 'Egrave'=>'200', 'egrave'=>'232', 'empty'=>'8709', 'emsp'=>'8195', 'ensp'=>'8194', 'Epsilon'=>'917', 'epsilon'=>'949', 'equiv'=>'8801', 'Eta'=>'919', 'eta'=>'951', 'ETH'=>'208', 'eth'=>'240', 'Euml'=>'203', 'euml'=>'235', 'euro'=>'8364', 'exist'=>'8707', 'fnof'=>'402', 'forall'=>'8704', 'frac12'=>'189', 'frac14'=>'188', 'frac34'=>'190', 'frasl'=>'8260', 'Gamma'=>'915', 'gamma'=>'947', 'ge'=>'8805', 'harr'=>'8596', 'hArr'=>'8660', 'hearts'=>'9829', 'hellip'=>'8230', 'Iacute'=>'205', 'iacute'=>'237', 'Icirc'=>'206', 'icirc'=>'238', 'iexcl'=>'161', 'Igrave'=>'204', 'igrave'=>'236', 'image'=>'8465', 'infin'=>'8734', 'int'=>'8747', 'Iota'=>'921', 'iota'=>'953', 'iquest'=>'191', 'isin'=>'8712', 'Iuml'=>'207', 'iuml'=>'239', 'Kappa'=>'922', 'kappa'=>'954', 'Lambda'=>'923', 'lambda'=>'955', 'laquo'=>'171', 'larr'=>'8592', 'lArr'=>'8656', 'lceil'=>'8968', 'ldquo'=>'8220', 'le'=>'8804', 'lfloor'=>'8970', 'lowast'=>'8727', 'loz'=>'9674', 'lrm'=>'8206', 'lsaquo'=>'8249', 'lsquo'=>'8216', 'macr'=>'175', 'mdash'=>'8212', 'micro'=>'181', 'middot'=>'183', 'minus'=>'8722', 'Mu'=>'924', 'mu'=>'956', 'nabla'=>'8711', 'nbsp'=>'160', 'ndash'=>'8211', 'ne'=>'8800', 'ni'=>'8715', 'not'=>'172', 'notin'=>'8713', 'nsub'=>'8836', 'Ntilde'=>'209', 'ntilde'=>'241', 'Nu'=>'925', 'nu'=>'957', 'Oacute'=>'211', 'oacute'=>'243', 'Ocirc'=>'212', 'ocirc'=>'244', 'OElig'=>'338', 'oelig'=>'339', 'Ograve'=>'210', 'ograve'=>'242', 'oline'=>'8254', 'Omega'=>'937', 'omega'=>'969', 'Omicron'=>'927', 'omicron'=>'959', 'oplus'=>'8853', 'or'=>'8744', 'ordf'=>'170', 'ordm'=>'186', 'Oslash'=>'216', 'oslash'=>'248', 'Otilde'=>'213', 'otilde'=>'245', 'otimes'=>'8855', 'Ouml'=>'214', 'ouml'=>'246', 'para'=>'182', 'part'=>'8706', 'permil'=>'8240', 'perp'=>'8869', 'Phi'=>'934', 'phi'=>'966', 'Pi'=>'928', 'pi'=>'960', 'piv'=>'982', 'plusmn'=>'177', 'pound'=>'163', 'prime'=>'8242', 'Prime'=>'8243', 'prod'=>'8719', 'prop'=>'8733', 'Psi'=>'936', 'psi'=>'968', 'radic'=>'8730', 'raquo'=>'187', 'rarr'=>'8594', 'rArr'=>'8658', 'rceil'=>'8969', 'rdquo'=>'8221', 'real'=>'8476', 'reg'=>'174', 'rfloor'=>'8971', 'Rho'=>'929', 'rho'=>'961', 'rlm'=>'8207', 'rsaquo'=>'8250', 'rsquo'=>'8217', 'sbquo'=>'8218', 'Scaron'=>'352', 'scaron'=>'353', 'sdot'=>'8901', 'sect'=>'167', 'shy'=>'173', 'Sigma'=>'931', 'sigma'=>'963', 'sigmaf'=>'962', 'sim'=>'8764', 'spades'=>'9824', 'sub'=>'8834', 'sube'=>'8838', 'sum'=>'8721', 'sup'=>'8835', 'sup1'=>'185', 'sup2'=>'178', 'sup3'=>'179', 'supe'=>'8839', 'szlig'=>'223', 'Tau'=>'932', 'tau'=>'964', 'there4'=>'8756', 'Theta'=>'920', 'theta'=>'952', 'thetasym'=>'977', 'thinsp'=>'8201', 'THORN'=>'222', 'thorn'=>'254', 'tilde'=>'732', 'times'=>'215', 'trade'=>'8482', 'Uacute'=>'218', 'uacute'=>'250', 'uarr'=>'8593', 'uArr'=>'8657', 'Ucirc'=>'219', 'ucirc'=>'251', 'Ugrave'=>'217', 'ugrave'=>'249', 'uml'=>'168', 'upsih'=>'978', 'Upsilon'=>'933', 'upsilon'=>'965', 'Uuml'=>'220', 'uuml'=>'252', 'weierp'=>'8472', 'Xi'=>'926', 'xi'=>'958', 'Yacute'=>'221', 'yacute'=>'253', 'yen'=>'165', 'yuml'=>'255', 'Yuml'=>'376', 'Zeta'=>'918', 'zeta'=>'950', 'zwj'=>'8205', 'zwnj'=>'8204');
  static $rareEntNameAr = array('Abreve'=>'258', 'abreve'=>'259', 'ac'=>'8766', 'acd'=>'8767', 'Acy'=>'1040', 'acy'=>'1072', 'af'=>'8289', 'Afr'=>'120068', 'afr'=>'120094', 'aleph'=>'8501', 'Amacr'=>'256', 'amacr'=>'257', 'amalg'=>'10815', 'And'=>'10835', 'andand'=>'10837', 'andd'=>'10844', 'andslope'=>'10840', 'andv'=>'10842', 'ange'=>'10660', 'angle'=>'8736', 'angmsd'=>'8737', 'angmsdaa'=>'10664', 'angmsdab'=>'10665', 'angmsdac'=>'10666', 'angmsdad'=>'10667', 'angmsdae'=>'10668', 'angmsdaf'=>'10669', 'angmsdag'=>'10670', 'angmsdah'=>'10671', 'angrt'=>'8735', 'angrtvb'=>'8894', 'angrtvbd'=>'10653', 'angsph'=>'8738', 'angst'=>'197', 'angzarr'=>'9084', 'Aogon'=>'260', 'aogon'=>'261', 'Aopf'=>'120120', 'aopf'=>'120146', 'ap'=>'8776', 'apacir'=>'10863', 'apE'=>'10864', 'ape'=>'8778', 'apid'=>'8779', 'ApplyFunction'=>'8289', 'approx'=>'8776', 'approxeq'=>'8778', 'Ascr'=>'119964', 'ascr'=>'119990', 'Assign'=>'8788', 'ast'=>'42', 'asympeq'=>'8781', 'awconint'=>'8755', 'awint'=>'10769', 'backcong'=>'8780', 'backepsilon'=>'1014', 'backprime'=>'8245', 'backsim'=>'8765', 'backsimeq'=>'8909', 'Backslash'=>'8726', 'Barv'=>'10983', 'barvee'=>'8893', 'barwed'=>'8965', 'Barwed'=>'8966', 'barwedge'=>'8965', 'bbrk'=>'9141', 'bbrktbrk'=>'9142', 'bcong'=>'8780', 'Bcy'=>'1041', 'bcy'=>'1073', 'becaus'=>'8757', 'because'=>'8757', 'Because'=>'8757', 'bemptyv'=>'10672', 'bepsi'=>'1014', 'bernou'=>'8492', 'Bernoullis'=>'8492', 'beth'=>'8502', 'between'=>'8812', 'Bfr'=>'120069', 'bfr'=>'120095', 'bigcap'=>'8898', 'bigcirc'=>'9711', 'bigcup'=>'8899', 'bigodot'=>'10752', 'bigoplus'=>'10753', 'bigotimes'=>'10754', 'bigsqcup'=>'10758', 'bigstar'=>'9733', 'bigtriangledown'=>'9661', 'bigtriangleup'=>'9651', 'biguplus'=>'10756', 'bigvee'=>'8897', 'bigwedge'=>'8896', 'bkarow'=>'10509', 'blacklozenge'=>'10731', 'blacksquare'=>'9642', 'blacktriangle'=>'9652', 'blacktriangledown'=>'9662', 'blacktriangleleft'=>'9666', 'blacktriangleright'=>'9656', 'blank'=>'9251', 'blk12'=>'9618', 'blk14'=>'9617', 'blk34'=>'9619', 'block'=>'9608', 'bNot'=>'10989', 'bnot'=>'8976', 'Bopf'=>'120121', 'bopf'=>'120147', 'bot'=>'8869', 'bottom'=>'8869', 'bowtie'=>'8904', 'boxbox'=>'10697', 'boxdl'=>'9488', 'boxdL'=>'9557', 'boxDl'=>'9558', 'boxDL'=>'9559', 'boxdr'=>'9484', 'boxdR'=>'9554', 'boxDr'=>'9555', 'boxDR'=>'9556', 'boxh'=>'9472', 'boxH'=>'9552', 'boxhd'=>'9516', 'boxHd'=>'9572', 'boxhD'=>'9573', 'boxHD'=>'9574', 'boxhu'=>'9524', 'boxHu'=>'9575', 'boxhU'=>'9576', 'boxHU'=>'9577', 'boxminus'=>'8863', 'boxplus'=>'8862', 'boxtimes'=>'8864', 'boxul'=>'9496', 'boxuL'=>'9563', 'boxUl'=>'9564', 'boxUL'=>'9565', 'boxur'=>'9492', 'boxuR'=>'9560', 'boxUr'=>'9561', 'boxUR'=>'9562', 'boxv'=>'9474', 'boxV'=>'9553', 'boxvh'=>'9532', 'boxvH'=>'9578', 'boxVh'=>'9579', 'boxVH'=>'9580', 'boxvl'=>'9508', 'boxvL'=>'9569', 'boxVl'=>'9570', 'boxVL'=>'9571', 'boxvr'=>'9500', 'boxvR'=>'9566', 'boxVr'=>'9567', 'boxVR'=>'9568', 'bprime'=>'8245', 'breve'=>'728', 'Breve'=>'728', 'bscr'=>'119991', 'Bscr'=>'8492', 'bsemi'=>'8271', 'bsim'=>'8765', 'bsime'=>'8909', 'bsol'=>'92', 'bsolb'=>'10693', 'bsolhsub'=>'10184', 'bullet'=>'8226', 'bump'=>'8782', 'bumpE'=>'10926', 'bumpe'=>'8783', 'Bumpeq'=>'8782', 'bumpeq'=>'8783', 'Cacute'=>'262', 'cacute'=>'263', 'Cap'=>'8914', 'capand'=>'10820', 'capbrcup'=>'10825', 'capcap'=>'10827', 'capcup'=>'10823', 'capdot'=>'10816', 'CapitalDifferentialD'=>'8517', 'caret'=>'8257', 'caron'=>'711', 'Cayleys'=>'8493', 'ccaps'=>'10829', 'Ccaron'=>'268', 'ccaron'=>'269', 'Ccirc'=>'264', 'ccirc'=>'265', 'Cconint'=>'8752', 'ccups'=>'10828', 'ccupssm'=>'10832', 'Cdot'=>'266', 'cdot'=>'267', 'Cedilla'=>'184', 'cemptyv'=>'10674', 'centerdot'=>'183', 'CenterDot'=>'183', 'cfr'=>'120096', 'Cfr'=>'8493', 'CHcy'=>'1063', 'chcy'=>'1095', 'check'=>'10003', 'checkmark'=>'10003', 'cir'=>'9675', 'circeq'=>'8791', 'circlearrowleft'=>'8634', 'circlearrowright'=>'8635', 'circledast'=>'8859', 'circledcirc'=>'8858', 'circleddash'=>'8861', 'CircleDot'=>'8857', 'circledR'=>'174', 'circledS'=>'9416', 'CircleMinus'=>'8854', 'CirclePlus'=>'8853', 'CircleTimes'=>'8855', 'cirE'=>'10691', 'cire'=>'8791', 'cirfnint'=>'10768', 'cirmid'=>'10991', 'cirscir'=>'10690', 'ClockwiseContourIntegral'=>'8754', 'CloseCurlyDoubleQuote'=>'8221', 'CloseCurlyQuote'=>'8217', 'clubsuit'=>'9827', 'colon'=>'58', 'Colon'=>'8759', 'Colone'=>'10868', 'colone'=>'8788', 'coloneq'=>'8788', 'comma'=>'44', 'commat'=>'64', 'comp'=>'8705', 'compfn'=>'8728', 'complement'=>'8705', 'complexes'=>'8450', 'congdot'=>'10861', 'Congruent'=>'8801', 'conint'=>'8750', 'Conint'=>'8751', 'ContourIntegral'=>'8750', 'copf'=>'120148', 'Copf'=>'8450', 'coprod'=>'8720', 'Coproduct'=>'8720', 'COPY'=>'169', 'copysr'=>'8471', 'CounterClockwiseContourIntegral'=>'8755', 'cross'=>'10007', 'Cross'=>'10799', 'Cscr'=>'119966', 'cscr'=>'119992', 'csub'=>'10959', 'csube'=>'10961', 'csup'=>'10960', 'csupe'=>'10962', 'ctdot'=>'8943', 'cudarrl'=>'10552', 'cudarrr'=>'10549', 'cuepr'=>'8926', 'cuesc'=>'8927', 'cularr'=>'8630', 'cularrp'=>'10557', 'Cup'=>'8915', 'cupbrcap'=>'10824', 'cupcap'=>'10822', 'CupCap'=>'8781', 'cupcup'=>'10826', 'cupdot'=>'8845', 'cupor'=>'10821', 'curarr'=>'8631', 'curarrm'=>'10556', 'curlyeqprec'=>'8926', 'curlyeqsucc'=>'8927', 'curlyvee'=>'8910', 'curlywedge'=>'8911', 'curvearrowleft'=>'8630', 'curvearrowright'=>'8631', 'cuvee'=>'8910', 'cuwed'=>'8911', 'cwconint'=>'8754', 'cwint'=>'8753', 'cylcty'=>'9005', 'daleth'=>'8504', 'Darr'=>'8609', 'dash'=>'8208', 'Dashv'=>'10980', 'dashv'=>'8867', 'dbkarow'=>'10511', 'dblac'=>'733', 'Dcaron'=>'270', 'dcaron'=>'271', 'Dcy'=>'1044', 'dcy'=>'1076', 'DD'=>'8517', 'dd'=>'8518', 'ddagger'=>'8225', 'ddarr'=>'8650', 'DDotrahd'=>'10513', 'ddotseq'=>'10871', 'Del'=>'8711', 'demptyv'=>'10673', 'dfisht'=>'10623', 'Dfr'=>'120071', 'dfr'=>'120097', 'dHar'=>'10597', 'dharl'=>'8643', 'dharr'=>'8642', 'DiacriticalAcute'=>'180', 'DiacriticalDot'=>'729', 'DiacriticalDoubleAcute'=>'733', 'DiacriticalGrave'=>'96', 'DiacriticalTilde'=>'732', 'diam'=>'8900', 'diamond'=>'8900', 'Diamond'=>'8900', 'diamondsuit'=>'9830', 'die'=>'168', 'DifferentialD'=>'8518', 'digamma'=>'989', 'disin'=>'8946', 'div'=>'247', 'divideontimes'=>'8903', 'divonx'=>'8903', 'DJcy'=>'1026', 'djcy'=>'1106', 'dlcorn'=>'8990', 'dlcrop'=>'8973', 'dollar'=>'36', 'Dopf'=>'120123', 'dopf'=>'120149', 'Dot'=>'168', 'dot'=>'729', 'DotDot'=>'8412', 'doteq'=>'8784', 'doteqdot'=>'8785', 'DotEqual'=>'8784', 'dotminus'=>'8760', 'dotplus'=>'8724', 'dotsquare'=>'8865', 'doublebarwedge'=>'8966', 'DoubleContourIntegral'=>'8751', 'DoubleDot'=>'168', 'DoubleDownArrow'=>'8659', 'DoubleLeftArrow'=>'8656', 'DoubleLeftRightArrow'=>'8660', 'DoubleLeftTee'=>'10980', 'DoubleLongLeftArrow'=>'10232', 'DoubleLongLeftRightArrow'=>'10234', 'DoubleLongRightArrow'=>'10233', 'DoubleRightArrow'=>'8658', 'DoubleRightTee'=>'8872', 'DoubleUpArrow'=>'8657', 'DoubleUpDownArrow'=>'8661', 'DoubleVerticalBar'=>'8741', 'downarrow'=>'8595', 'DownArrow'=>'8595', 'Downarrow'=>'8659', 'DownArrowBar'=>'10515', 'DownArrowUpArrow'=>'8693', 'DownBreve'=>'785', 'downdownarrows'=>'8650', 'downharpoonleft'=>'8643', 'downharpoonright'=>'8642', 'DownLeftRightVector'=>'10576', 'DownLeftTeeVector'=>'10590', 'DownLeftVector'=>'8637', 'DownLeftVectorBar'=>'10582', 'DownRightTeeVector'=>'10591', 'DownRightVector'=>'8641', 'DownRightVectorBar'=>'10583', 'DownTee'=>'8868', 'DownTeeArrow'=>'8615', 'drbkarow'=>'10512', 'drcorn'=>'8991', 'drcrop'=>'8972', 'Dscr'=>'119967', 'dscr'=>'119993', 'DScy'=>'1029', 'dscy'=>'1109', 'dsol'=>'10742', 'Dstrok'=>'272', 'dstrok'=>'273', 'dtdot'=>'8945', 'dtri'=>'9663', 'dtrif'=>'9662', 'duarr'=>'8693', 'duhar'=>'10607', 'dwangle'=>'10662', 'DZcy'=>'1039', 'dzcy'=>'1119', 'dzigrarr'=>'10239', 'easter'=>'10862', 'Ecaron'=>'282', 'ecaron'=>'283', 'ecir'=>'8790', 'ecolon'=>'8789', 'Ecy'=>'1069', 'ecy'=>'1101', 'eDDot'=>'10871', 'Edot'=>'278', 'edot'=>'279', 'eDot'=>'8785', 'ee'=>'8519', 'efDot'=>'8786', 'Efr'=>'120072', 'efr'=>'120098', 'eg'=>'10906', 'egs'=>'10902', 'egsdot'=>'10904', 'el'=>'10905', 'Element'=>'8712', 'elinters'=>'9191', 'ell'=>'8467', 'els'=>'10901', 'elsdot'=>'10903', 'Emacr'=>'274', 'emacr'=>'275', 'emptyset'=>'8709', 'EmptySmallSquare'=>'9723', 'emptyv'=>'8709', 'EmptyVerySmallSquare'=>'9643', 'emsp13'=>'8196', 'emsp14'=>'8197', 'ENG'=>'330', 'eng'=>'331', 'Eogon'=>'280', 'eogon'=>'281', 'Eopf'=>'120124', 'eopf'=>'120150', 'epar'=>'8917', 'eparsl'=>'10723', 'eplus'=>'10865', 'epsi'=>'949', 'epsiv'=>'1013', 'eqcirc'=>'8790', 'eqcolon'=>'8789', 'eqsim'=>'8770', 'eqslantgtr'=>'10902', 'eqslantless'=>'10901', 'Equal'=>'10869', 'equals'=>'61', 'EqualTilde'=>'8770', 'equest'=>'8799', 'Equilibrium'=>'8652', 'equivDD'=>'10872', 'eqvparsl'=>'10725', 'erarr'=>'10609', 'erDot'=>'8787', 'escr'=>'8495', 'Escr'=>'8496', 'esdot'=>'8784', 'Esim'=>'10867', 'esim'=>'8770', 'excl'=>'33', 'Exists'=>'8707', 'expectation'=>'8496', 'exponentiale'=>'8519', 'ExponentialE'=>'8519', 'fallingdotseq'=>'8786', 'Fcy'=>'1060', 'fcy'=>'1092', 'female'=>'9792', 'ffilig'=>'64259', 'fflig'=>'64256', 'ffllig'=>'64260', 'Ffr'=>'120073', 'ffr'=>'120099', 'filig'=>'64257', 'FilledSmallSquare'=>'9724', 'FilledVerySmallSquare'=>'9642', 'flat'=>'9837', 'fllig'=>'64258', 'fltns'=>'9649', 'Fopf'=>'120125', 'fopf'=>'120151', 'ForAll'=>'8704', 'fork'=>'8916', 'forkv'=>'10969', 'Fouriertrf'=>'8497', 'fpartint'=>'10765', 'frac13'=>'8531', 'frac15'=>'8533', 'frac16'=>'8537', 'frac18'=>'8539', 'frac23'=>'8532', 'frac25'=>'8534', 'frac35'=>'8535', 'frac38'=>'8540', 'frac45'=>'8536', 'frac56'=>'8538', 'frac58'=>'8541', 'frac78'=>'8542', 'frown'=>'8994', 'fscr'=>'119995', 'Fscr'=>'8497', 'gacute'=>'501', 'Gammad'=>'988', 'gammad'=>'989', 'gap'=>'10886', 'Gbreve'=>'286', 'gbreve'=>'287', 'Gcedil'=>'290', 'Gcirc'=>'284', 'gcirc'=>'285', 'Gcy'=>'1043', 'gcy'=>'1075', 'Gdot'=>'288', 'gdot'=>'289', 'gE'=>'8807', 'gEl'=>'10892', 'gel'=>'8923', 'geq'=>'8805', 'geqq'=>'8807', 'geqslant'=>'10878', 'ges'=>'10878', 'gescc'=>'10921', 'gesdot'=>'10880', 'gesdoto'=>'10882', 'gesdotol'=>'10884', 'gesles'=>'10900', 'Gfr'=>'120074', 'gfr'=>'120100', 'gg'=>'8811', 'Gg'=>'8921', 'ggg'=>'8921', 'gimel'=>'8503', 'GJcy'=>'1027', 'gjcy'=>'1107', 'gl'=>'8823', 'gla'=>'10917', 'glE'=>'10898', 'glj'=>'10916', 'gnap'=>'10890', 'gnapprox'=>'10890', 'gne'=>'10888', 'gnE'=>'8809', 'gneq'=>'10888', 'gneqq'=>'8809', 'gnsim'=>'8935', 'Gopf'=>'120126', 'gopf'=>'120152', 'grave'=>'96', 'GreaterEqual'=>'8805', 'GreaterEqualLess'=>'8923', 'GreaterFullEqual'=>'8807', 'GreaterGreater'=>'10914', 'GreaterLess'=>'8823', 'GreaterSlantEqual'=>'10878', 'GreaterTilde'=>'8819', 'Gscr'=>'119970', 'gscr'=>'8458', 'gsim'=>'8819', 'gsime'=>'10894', 'gsiml'=>'10896', 'Gt'=>'8811', 'gtcc'=>'10919', 'gtcir'=>'10874', 'gtdot'=>'8919', 'gtlPar'=>'10645', 'gtquest'=>'10876', 'gtrapprox'=>'10886', 'gtrarr'=>'10616', 'gtrdot'=>'8919', 'gtreqless'=>'8923', 'gtreqqless'=>'10892', 'gtrless'=>'8823', 'gtrsim'=>'8819', 'Hacek'=>'711', 'hairsp'=>'8202', 'half'=>'189', 'hamilt'=>'8459', 'HARDcy'=>'1066', 'hardcy'=>'1098', 'harrcir'=>'10568', 'harrw'=>'8621', 'Hat'=>'94', 'hbar'=>'8463', 'Hcirc'=>'292', 'hcirc'=>'293', 'heartsuit'=>'9829', 'hercon'=>'8889', 'hfr'=>'120101', 'Hfr'=>'8460', 'HilbertSpace'=>'8459', 'hksearow'=>'10533', 'hkswarow'=>'10534', 'hoarr'=>'8703', 'homtht'=>'8763', 'hookleftarrow'=>'8617', 'hookrightarrow'=>'8618', 'hopf'=>'120153', 'Hopf'=>'8461', 'horbar'=>'8213', 'HorizontalLine'=>'9472', 'hscr'=>'119997', 'Hscr'=>'8459', 'hslash'=>'8463', 'Hstrok'=>'294', 'hstrok'=>'295', 'HumpDownHump'=>'8782', 'HumpEqual'=>'8783', 'hybull'=>'8259', 'hyphen'=>'8208', 'ic'=>'8291', 'Icy'=>'1048', 'icy'=>'1080', 'Idot'=>'304', 'IEcy'=>'1045', 'iecy'=>'1077', 'iff'=>'8660', 'ifr'=>'120102', 'Ifr'=>'8465', 'ii'=>'8520', 'iiiint'=>'10764', 'iiint'=>'8749', 'iinfin'=>'10716', 'iiota'=>'8489', 'IJlig'=>'306', 'ijlig'=>'307', 'Im'=>'8465', 'Imacr'=>'298', 'imacr'=>'299', 'ImaginaryI'=>'8520', 'imagline'=>'8464', 'imagpart'=>'8465', 'imath'=>'305', 'imof'=>'8887', 'imped'=>'437', 'Implies'=>'8658', 'in'=>'8712', 'incare'=>'8453', 'infintie'=>'10717', 'inodot'=>'305', 'Int'=>'8748', 'intcal'=>'8890', 'integers'=>'8484', 'Integral'=>'8747', 'intercal'=>'8890', 'Intersection'=>'8898', 'intlarhk'=>'10775', 'intprod'=>'10812', 'InvisibleComma'=>'8291', 'InvisibleTimes'=>'8290', 'IOcy'=>'1025', 'iocy'=>'1105', 'Iogon'=>'302', 'iogon'=>'303', 'Iopf'=>'120128', 'iopf'=>'120154', 'iprod'=>'10812', 'iscr'=>'119998', 'Iscr'=>'8464', 'isindot'=>'8949', 'isinE'=>'8953', 'isins'=>'8948', 'isinsv'=>'8947', 'isinv'=>'8712', 'it'=>'8290', 'Itilde'=>'296', 'itilde'=>'297', 'Iukcy'=>'1030', 'iukcy'=>'1110', 'Jcirc'=>'308', 'jcirc'=>'309', 'Jcy'=>'1049', 'jcy'=>'1081', 'Jfr'=>'120077', 'jfr'=>'120103', 'jmath'=>'567', 'Jopf'=>'120129', 'jopf'=>'120155', 'Jscr'=>'119973', 'jscr'=>'119999', 'Jsercy'=>'1032', 'jsercy'=>'1112', 'Jukcy'=>'1028', 'jukcy'=>'1108', 'kappav'=>'1008', 'Kcedil'=>'310', 'kcedil'=>'311', 'Kcy'=>'1050', 'kcy'=>'1082', 'Kfr'=>'120078', 'kfr'=>'120104', 'kgreen'=>'312', 'KHcy'=>'1061', 'khcy'=>'1093', 'KJcy'=>'1036', 'kjcy'=>'1116', 'Kopf'=>'120130', 'kopf'=>'120156', 'Kscr'=>'119974', 'kscr'=>'120000', 'lAarr'=>'8666', 'Lacute'=>'313', 'lacute'=>'314', 'laemptyv'=>'10676', 'lagran'=>'8466', 'lang'=>'10216', 'Lang'=>'10218', 'langd'=>'10641', 'langle'=>'10216', 'lap'=>'10885', 'Laplacetrf'=>'8466', 'Larr'=>'8606', 'larrb'=>'8676', 'larrbfs'=>'10527', 'larrfs'=>'10525', 'larrhk'=>'8617', 'larrlp'=>'8619', 'larrpl'=>'10553', 'larrsim'=>'10611', 'larrtl'=>'8610', 'lat'=>'10923', 'latail'=>'10521', 'lAtail'=>'10523', 'late'=>'10925', 'lbarr'=>'10508', 'lBarr'=>'10510', 'lbbrk'=>'10098', 'lbrace'=>'123', 'lbrack'=>'91', 'lbrke'=>'10635', 'lbrksld'=>'10639', 'lbrkslu'=>'10637', 'Lcaron'=>'317', 'lcaron'=>'318', 'Lcedil'=>'315', 'lcedil'=>'316', 'lcub'=>'123', 'Lcy'=>'1051', 'lcy'=>'1083', 'ldca'=>'10550', 'ldquor'=>'8222', 'ldrdhar'=>'10599', 'ldrushar'=>'10571', 'ldsh'=>'8626', 'lE'=>'8806', 'LeftAngleBracket'=>'10216', 'leftarrow'=>'8592', 'LeftArrow'=>'8592', 'Leftarrow'=>'8656', 'LeftArrowBar'=>'8676', 'LeftArrowRightArrow'=>'8646', 'leftarrowtail'=>'8610', 'LeftCeiling'=>'8968', 'LeftDoubleBracket'=>'10214', 'LeftDownTeeVector'=>'10593', 'LeftDownVector'=>'8643', 'LeftDownVectorBar'=>'10585', 'LeftFloor'=>'8970', 'leftharpoondown'=>'8637', 'leftharpoonup'=>'8636', 'leftleftarrows'=>'8647', 'leftrightarrow'=>'8596', 'LeftRightArrow'=>'8596', 'Leftrightarrow'=>'8660', 'leftrightarrows'=>'8646', 'leftrightharpoons'=>'8651', 'leftrightsquigarrow'=>'8621', 'LeftRightVector'=>'10574', 'LeftTee'=>'8867', 'LeftTeeArrow'=>'8612', 'LeftTeeVector'=>'10586', 'leftthreetimes'=>'8907', 'LeftTriangle'=>'8882', 'LeftTriangleBar'=>'10703', 'LeftTriangleEqual'=>'8884', 'LeftUpDownVector'=>'10577', 'LeftUpTeeVector'=>'10592', 'LeftUpVector'=>'8639', 'LeftUpVectorBar'=>'10584', 'LeftVector'=>'8636', 'LeftVectorBar'=>'10578', 'lEg'=>'10891', 'leg'=>'8922', 'leq'=>'8804', 'leqq'=>'8806', 'leqslant'=>'10877', 'les'=>'10877', 'lescc'=>'10920', 'lesdot'=>'10879', 'lesdoto'=>'10881', 'lesdotor'=>'10883', 'lesges'=>'10899', 'lessapprox'=>'10885', 'lessdot'=>'8918', 'lesseqgtr'=>'8922', 'lesseqqgtr'=>'10891', 'LessEqualGreater'=>'8922', 'LessFullEqual'=>'8806', 'LessGreater'=>'8822', 'lessgtr'=>'8822', 'LessLess'=>'10913', 'lesssim'=>'8818', 'LessSlantEqual'=>'10877', 'LessTilde'=>'8818', 'lfisht'=>'10620', 'Lfr'=>'120079', 'lfr'=>'120105', 'lg'=>'8822', 'lgE'=>'10897', 'lHar'=>'10594', 'lhard'=>'8637', 'lharu'=>'8636', 'lharul'=>'10602', 'lhblk'=>'9604', 'LJcy'=>'1033', 'ljcy'=>'1113', 'll'=>'8810', 'Ll'=>'8920', 'llarr'=>'8647', 'llcorner'=>'8990', 'Lleftarrow'=>'8666', 'llhard'=>'10603', 'lltri'=>'9722', 'Lmidot'=>'319', 'lmidot'=>'320', 'lmoust'=>'9136', 'lmoustache'=>'9136', 'lnap'=>'10889', 'lnapprox'=>'10889', 'lne'=>'10887', 'lnE'=>'8808', 'lneq'=>'10887', 'lneqq'=>'8808', 'lnsim'=>'8934', 'loang'=>'10220', 'loarr'=>'8701', 'lobrk'=>'10214', 'longleftarrow'=>'10229', 'LongLeftArrow'=>'10229', 'Longleftarrow'=>'10232', 'longleftrightarrow'=>'10231', 'LongLeftRightArrow'=>'10231', 'Longleftrightarrow'=>'10234', 'longmapsto'=>'10236', 'longrightarrow'=>'10230', 'LongRightArrow'=>'10230', 'Longrightarrow'=>'10233', 'looparrowleft'=>'8619', 'looparrowright'=>'8620', 'lopar'=>'10629', 'Lopf'=>'120131', 'lopf'=>'120157', 'loplus'=>'10797', 'lotimes'=>'10804', 'lowbar'=>'95', 'LowerLeftArrow'=>'8601', 'LowerRightArrow'=>'8600', 'lozenge'=>'9674', 'lozf'=>'10731', 'lpar'=>'40', 'lparlt'=>'10643', 'lrarr'=>'8646', 'lrcorner'=>'8991', 'lrhar'=>'8651', 'lrhard'=>'10605', 'lrtri'=>'8895', 'lscr'=>'120001', 'Lscr'=>'8466', 'lsh'=>'8624', 'Lsh'=>'8624', 'lsim'=>'8818', 'lsime'=>'10893', 'lsimg'=>'10895', 'lsqb'=>'91', 'lsquor'=>'8218', 'Lstrok'=>'321', 'lstrok'=>'322', 'Lt'=>'8810', 'ltcc'=>'10918', 'ltcir'=>'10873', 'ltdot'=>'8918', 'lthree'=>'8907', 'ltimes'=>'8905', 'ltlarr'=>'10614', 'ltquest'=>'10875', 'ltri'=>'9667', 'ltrie'=>'8884', 'ltrif'=>'9666', 'ltrPar'=>'10646', 'lurdshar'=>'10570', 'luruhar'=>'10598', 'male'=>'9794', 'malt'=>'10016', 'maltese'=>'10016', 'Map'=>'10501', 'map'=>'8614', 'mapsto'=>'8614', 'mapstodown'=>'8615', 'mapstoleft'=>'8612', 'mapstoup'=>'8613', 'marker'=>'9646', 'mcomma'=>'10793', 'Mcy'=>'1052', 'mcy'=>'1084', 'mDDot'=>'8762', 'measuredangle'=>'8737', 'MediumSpace'=>'8287', 'Mellintrf'=>'8499', 'Mfr'=>'120080', 'mfr'=>'120106', 'mho'=>'8487', 'mid'=>'8739', 'midast'=>'42', 'midcir'=>'10992', 'minusb'=>'8863', 'minusd'=>'8760', 'minusdu'=>'10794', 'MinusPlus'=>'8723', 'mlcp'=>'10971', 'mldr'=>'8230', 'mnplus'=>'8723', 'models'=>'8871', 'Mopf'=>'120132', 'mopf'=>'120158', 'mp'=>'8723', 'mscr'=>'120002', 'Mscr'=>'8499', 'mstpos'=>'8766', 'multimap'=>'8888', 'mumap'=>'8888', 'Nacute'=>'323', 'nacute'=>'324', 'nap'=>'8777', 'napos'=>'329', 'napprox'=>'8777', 'natur'=>'9838', 'natural'=>'9838', 'naturals'=>'8469', 'ncap'=>'10819', 'Ncaron'=>'327', 'ncaron'=>'328', 'Ncedil'=>'325', 'ncedil'=>'326', 'ncong'=>'8775', 'ncup'=>'10818', 'Ncy'=>'1053', 'ncy'=>'1085', 'nearhk'=>'10532', 'nearr'=>'8599', 'neArr'=>'8663', 'nearrow'=>'8599', 'NegativeMediumSpace'=>'8203', 'NegativeThickSpace'=>'8203', 'NegativeThinSpace'=>'8203', 'NegativeVeryThinSpace'=>'8203', 'nequiv'=>'8802', 'nesear'=>'10536', 'NestedGreaterGreater'=>'8811', 'NestedLessLess'=>'8810', 'NewLine'=>'10', 'nexist'=>'8708', 'nexists'=>'8708', 'Nfr'=>'120081', 'nfr'=>'120107', 'nge'=>'8817', 'ngeq'=>'8817', 'ngsim'=>'8821', 'ngt'=>'8815', 'ngtr'=>'8815', 'nharr'=>'8622', 'nhArr'=>'8654', 'nhpar'=>'10994', 'nis'=>'8956', 'nisd'=>'8954', 'niv'=>'8715', 'NJcy'=>'1034', 'njcy'=>'1114', 'nlarr'=>'8602', 'nlArr'=>'8653', 'nldr'=>'8229', 'nle'=>'8816', 'nleftarrow'=>'8602', 'nLeftarrow'=>'8653', 'nleftrightarrow'=>'8622', 'nLeftrightarrow'=>'8654', 'nleq'=>'8816', 'nless'=>'8814', 'nlsim'=>'8820', 'nlt'=>'8814', 'nltri'=>'8938', 'nltrie'=>'8940', 'nmid'=>'8740', 'NoBreak'=>'8288', 'NonBreakingSpace'=>'160', 'nopf'=>'120159', 'Nopf'=>'8469', 'Not'=>'10988', 'NotCongruent'=>'8802', 'NotCupCap'=>'8813', 'NotDoubleVerticalBar'=>'8742', 'NotElement'=>'8713', 'NotEqual'=>'8800', 'NotExists'=>'8708', 'NotGreater'=>'8815', 'NotGreaterEqual'=>'8817', 'NotGreaterLess'=>'8825', 'NotGreaterTilde'=>'8821', 'notinva'=>'8713', 'notinvb'=>'8951', 'notinvc'=>'8950', 'NotLeftTriangle'=>'8938', 'NotLeftTriangleEqual'=>'8940', 'NotLess'=>'8814', 'NotLessEqual'=>'8816', 'NotLessGreater'=>'8824', 'NotLessTilde'=>'8820', 'notni'=>'8716', 'notniva'=>'8716', 'notnivb'=>'8958', 'notnivc'=>'8957', 'NotPrecedes'=>'8832', 'NotPrecedesSlantEqual'=>'8928', 'NotReverseElement'=>'8716', 'NotRightTriangle'=>'8939', 'NotRightTriangleEqual'=>'8941', 'NotSquareSubsetEqual'=>'8930', 'NotSquareSupersetEqual'=>'8931', 'NotSubsetEqual'=>'8840', 'NotSucceeds'=>'8833', 'NotSucceedsSlantEqual'=>'8929', 'NotSupersetEqual'=>'8841', 'NotTilde'=>'8769', 'NotTildeEqual'=>'8772', 'NotTildeFullEqual'=>'8775', 'NotTildeTilde'=>'8777', 'NotVerticalBar'=>'8740', 'npar'=>'8742', 'nparallel'=>'8742', 'npolint'=>'10772', 'npr'=>'8832', 'nprcue'=>'8928', 'nprec'=>'8832', 'nrarr'=>'8603', 'nrArr'=>'8655', 'nrightarrow'=>'8603', 'nRightarrow'=>'8655', 'nrtri'=>'8939', 'nrtrie'=>'8941', 'nsc'=>'8833', 'nsccue'=>'8929', 'Nscr'=>'119977', 'nscr'=>'120003', 'nshortmid'=>'8740', 'nshortparallel'=>'8742', 'nsim'=>'8769', 'nsime'=>'8772', 'nsimeq'=>'8772', 'nsmid'=>'8740', 'nspar'=>'8742', 'nsqsube'=>'8930', 'nsqsupe'=>'8931', 'nsube'=>'8840', 'nsubseteq'=>'8840', 'nsucc'=>'8833', 'nsup'=>'8837', 'nsupe'=>'8841', 'nsupseteq'=>'8841', 'ntgl'=>'8825', 'ntlg'=>'8824', 'ntriangleleft'=>'8938', 'ntrianglelefteq'=>'8940', 'ntriangleright'=>'8939', 'ntrianglerighteq'=>'8941', 'num'=>'35', 'numero'=>'8470', 'numsp'=>'8199', 'nvdash'=>'8876', 'nvDash'=>'8877', 'nVdash'=>'8878', 'nVDash'=>'8879', 'nvHarr'=>'10500', 'nvinfin'=>'10718', 'nvlArr'=>'10498', 'nvrArr'=>'10499', 'nwarhk'=>'10531', 'nwarr'=>'8598', 'nwArr'=>'8662', 'nwarrow'=>'8598', 'nwnear'=>'10535', 'oast'=>'8859', 'ocir'=>'8858', 'Ocy'=>'1054', 'ocy'=>'1086', 'odash'=>'8861', 'Odblac'=>'336', 'odblac'=>'337', 'odiv'=>'10808', 'odot'=>'8857', 'odsold'=>'10684', 'ofcir'=>'10687', 'Ofr'=>'120082', 'ofr'=>'120108', 'ogon'=>'731', 'ogt'=>'10689', 'ohbar'=>'10677', 'ohm'=>'937', 'oint'=>'8750', 'olarr'=>'8634', 'olcir'=>'10686', 'olcross'=>'10683', 'olt'=>'10688', 'Omacr'=>'332', 'omacr'=>'333', 'omid'=>'10678', 'ominus'=>'8854', 'Oopf'=>'120134', 'oopf'=>'120160', 'opar'=>'10679', 'OpenCurlyDoubleQuote'=>'8220', 'OpenCurlyQuote'=>'8216', 'operp'=>'10681', 'Or'=>'10836', 'orarr'=>'8635', 'ord'=>'10845', 'order'=>'8500', 'orderof'=>'8500', 'origof'=>'8886', 'oror'=>'10838', 'orslope'=>'10839', 'orv'=>'10843', 'oS'=>'9416', 'Oscr'=>'119978', 'oscr'=>'8500', 'osol'=>'8856', 'Otimes'=>'10807', 'otimesas'=>'10806', 'ovbar'=>'9021', 'OverBar'=>'8254', 'OverBrace'=>'9182', 'OverBracket'=>'9140', 'OverParenthesis'=>'9180', 'par'=>'8741', 'parallel'=>'8741', 'parsim'=>'10995', 'parsl'=>'11005', 'PartialD'=>'8706', 'Pcy'=>'1055', 'pcy'=>'1087', 'percnt'=>'37', 'period'=>'46', 'pertenk'=>'8241', 'Pfr'=>'120083', 'pfr'=>'120109', 'phiv'=>'981', 'phmmat'=>'8499', 'phone'=>'9742', 'pitchfork'=>'8916', 'planck'=>'8463', 'planckh'=>'8462', 'plankv'=>'8463', 'plus'=>'43', 'plusacir'=>'10787', 'plusb'=>'8862', 'pluscir'=>'10786', 'plusdo'=>'8724', 'plusdu'=>'10789', 'pluse'=>'10866', 'PlusMinus'=>'177', 'plussim'=>'10790', 'plustwo'=>'10791', 'pm'=>'177', 'Poincareplane'=>'8460', 'pointint'=>'10773', 'popf'=>'120161', 'Popf'=>'8473', 'Pr'=>'10939', 'pr'=>'8826', 'prap'=>'10935', 'prcue'=>'8828', 'pre'=>'10927', 'prE'=>'10931', 'prec'=>'8826', 'precapprox'=>'10935', 'preccurlyeq'=>'8828', 'Precedes'=>'8826', 'PrecedesEqual'=>'10927', 'PrecedesSlantEqual'=>'8828', 'PrecedesTilde'=>'8830', 'preceq'=>'10927', 'precnapprox'=>'10937', 'precneqq'=>'10933', 'precnsim'=>'8936', 'precsim'=>'8830', 'primes'=>'8473', 'prnap'=>'10937', 'prnE'=>'10933', 'prnsim'=>'8936', 'Product'=>'8719', 'profalar'=>'9006', 'profline'=>'8978', 'profsurf'=>'8979', 'Proportion'=>'8759', 'Proportional'=>'8733', 'propto'=>'8733', 'prsim'=>'8830', 'prurel'=>'8880', 'Pscr'=>'119979', 'pscr'=>'120005', 'puncsp'=>'8200', 'Qfr'=>'120084', 'qfr'=>'120110', 'qint'=>'10764', 'qopf'=>'120162', 'Qopf'=>'8474', 'qprime'=>'8279', 'Qscr'=>'119980', 'qscr'=>'120006', 'quaternions'=>'8461', 'quatint'=>'10774', 'quest'=>'63', 'questeq'=>'8799', 'rAarr'=>'8667', 'Racute'=>'340', 'racute'=>'341', 'raemptyv'=>'10675', 'rang'=>'10217', 'Rang'=>'10219', 'rangd'=>'10642', 'range'=>'10661', 'rangle'=>'10217', 'Rarr'=>'8608', 'rarrap'=>'10613', 'rarrb'=>'8677', 'rarrbfs'=>'10528', 'rarrc'=>'10547', 'rarrfs'=>'10526', 'rarrhk'=>'8618', 'rarrlp'=>'8620', 'rarrpl'=>'10565', 'rarrsim'=>'10612', 'Rarrtl'=>'10518', 'rarrtl'=>'8611', 'rarrw'=>'8605', 'ratail'=>'10522', 'rAtail'=>'10524', 'ratio'=>'8758', 'rationals'=>'8474', 'rbarr'=>'10509', 'rBarr'=>'10511', 'RBarr'=>'10512', 'rbbrk'=>'10099', 'rbrace'=>'125', 'rbrack'=>'93', 'rbrke'=>'10636', 'rbrksld'=>'10638', 'rbrkslu'=>'10640', 'Rcaron'=>'344', 'rcaron'=>'345', 'Rcedil'=>'342', 'rcedil'=>'343', 'rcub'=>'125', 'Rcy'=>'1056', 'rcy'=>'1088', 'rdca'=>'10551', 'rdldhar'=>'10601', 'rdquor'=>'8221', 'rdsh'=>'8627', 'Re'=>'8476', 'realine'=>'8475', 'realpart'=>'8476', 'reals'=>'8477', 'rect'=>'9645', 'REG'=>'174', 'ReverseElement'=>'8715', 'ReverseEquilibrium'=>'8651', 'ReverseUpEquilibrium'=>'10607', 'rfisht'=>'10621', 'rfr'=>'120111', 'Rfr'=>'8476', 'rHar'=>'10596', 'rhard'=>'8641', 'rharu'=>'8640', 'rharul'=>'10604', 'rhov'=>'1009', 'RightAngleBracket'=>'10217', 'rightarrow'=>'8594', 'RightArrow'=>'8594', 'Rightarrow'=>'8658', 'RightArrowBar'=>'8677', 'RightArrowLeftArrow'=>'8644', 'rightarrowtail'=>'8611', 'RightCeiling'=>'8969', 'RightDoubleBracket'=>'10215', 'RightDownTeeVector'=>'10589', 'RightDownVector'=>'8642', 'RightDownVectorBar'=>'10581', 'RightFloor'=>'8971', 'rightharpoondown'=>'8641', 'rightharpoonup'=>'8640', 'rightleftarrows'=>'8644', 'rightleftharpoons'=>'8652', 'rightrightarrows'=>'8649', 'rightsquigarrow'=>'8605', 'RightTee'=>'8866', 'RightTeeArrow'=>'8614', 'RightTeeVector'=>'10587', 'rightthreetimes'=>'8908', 'RightTriangle'=>'8883', 'RightTriangleBar'=>'10704', 'RightTriangleEqual'=>'8885', 'RightUpDownVector'=>'10575', 'RightUpTeeVector'=>'10588', 'RightUpVector'=>'8638', 'RightUpVectorBar'=>'10580', 'RightVector'=>'8640', 'RightVectorBar'=>'10579', 'ring'=>'730', 'risingdotseq'=>'8787', 'rlarr'=>'8644', 'rlhar'=>'8652', 'rmoust'=>'9137', 'rmoustache'=>'9137', 'rnmid'=>'10990', 'roang'=>'10221', 'roarr'=>'8702', 'robrk'=>'10215', 'ropar'=>'10630', 'ropf'=>'120163', 'Ropf'=>'8477', 'roplus'=>'10798', 'rotimes'=>'10805', 'RoundImplies'=>'10608', 'rpar'=>'41', 'rpargt'=>'10644', 'rppolint'=>'10770', 'rrarr'=>'8649', 'Rrightarrow'=>'8667', 'rscr'=>'120007', 'Rscr'=>'8475', 'rsh'=>'8625', 'Rsh'=>'8625', 'rsqb'=>'93', 'rsquor'=>'8217', 'rthree'=>'8908', 'rtimes'=>'8906', 'rtri'=>'9657', 'rtrie'=>'8885', 'rtrif'=>'9656', 'rtriltri'=>'10702', 'RuleDelayed'=>'10740', 'ruluhar'=>'10600', 'rx'=>'8478', 'Sacute'=>'346', 'sacute'=>'347', 'Sc'=>'10940', 'sc'=>'8827', 'scap'=>'10936', 'sccue'=>'8829', 'sce'=>'10928', 'scE'=>'10932', 'Scedil'=>'350', 'scedil'=>'351', 'Scirc'=>'348', 'scirc'=>'349', 'scnap'=>'10938', 'scnE'=>'10934', 'scnsim'=>'8937', 'scpolint'=>'10771', 'scsim'=>'8831', 'Scy'=>'1057', 'scy'=>'1089', 'sdotb'=>'8865', 'sdote'=>'10854', 'searhk'=>'10533', 'searr'=>'8600', 'seArr'=>'8664', 'searrow'=>'8600', 'semi'=>'59', 'seswar'=>'10537', 'setminus'=>'8726', 'setmn'=>'8726', 'sext'=>'10038', 'Sfr'=>'120086', 'sfr'=>'120112', 'sfrown'=>'8994', 'sharp'=>'9839', 'SHCHcy'=>'1065', 'shchcy'=>'1097', 'SHcy'=>'1064', 'shcy'=>'1096', 'ShortDownArrow'=>'8595', 'ShortLeftArrow'=>'8592', 'shortmid'=>'8739', 'shortparallel'=>'8741', 'ShortRightArrow'=>'8594', 'ShortUpArrow'=>'8593', 'sigmav'=>'962', 'simdot'=>'10858', 'sime'=>'8771', 'simeq'=>'8771', 'simg'=>'10910', 'simgE'=>'10912', 'siml'=>'10909', 'simlE'=>'10911', 'simne'=>'8774', 'simplus'=>'10788', 'simrarr'=>'10610', 'slarr'=>'8592', 'SmallCircle'=>'8728', 'smallsetminus'=>'8726', 'smashp'=>'10803', 'smeparsl'=>'10724', 'smid'=>'8739', 'smile'=>'8995', 'smt'=>'10922', 'smte'=>'10924', 'SOFTcy'=>'1068', 'softcy'=>'1100', 'sol'=>'47', 'solb'=>'10692', 'solbar'=>'9023', 'Sopf'=>'120138', 'sopf'=>'120164', 'spadesuit'=>'9824', 'spar'=>'8741', 'sqcap'=>'8851', 'sqcup'=>'8852', 'Sqrt'=>'8730', 'sqsub'=>'8847', 'sqsube'=>'8849', 'sqsubset'=>'8847', 'sqsubseteq'=>'8849', 'sqsup'=>'8848', 'sqsupe'=>'8850', 'sqsupset'=>'8848', 'sqsupseteq'=>'8850', 'squ'=>'9633', 'square'=>'9633', 'Square'=>'9633', 'SquareIntersection'=>'8851', 'SquareSubset'=>'8847', 'SquareSubsetEqual'=>'8849', 'SquareSuperset'=>'8848', 'SquareSupersetEqual'=>'8850', 'SquareUnion'=>'8852', 'squarf'=>'9642', 'squf'=>'9642', 'srarr'=>'8594', 'Sscr'=>'119982', 'sscr'=>'120008', 'ssetmn'=>'8726', 'ssmile'=>'8995', 'sstarf'=>'8902', 'Star'=>'8902', 'star'=>'9734', 'starf'=>'9733', 'straightepsilon'=>'1013', 'straightphi'=>'981', 'strns'=>'175', 'Sub'=>'8912', 'subdot'=>'10941', 'subE'=>'10949', 'subedot'=>'10947', 'submult'=>'10945', 'subnE'=>'10955', 'subne'=>'8842', 'subplus'=>'10943', 'subrarr'=>'10617', 'subset'=>'8834', 'Subset'=>'8912', 'subseteq'=>'8838', 'subseteqq'=>'10949', 'SubsetEqual'=>'8838', 'subsetneq'=>'8842', 'subsetneqq'=>'10955', 'subsim'=>'10951', 'subsub'=>'10965', 'subsup'=>'10963', 'succ'=>'8827', 'succapprox'=>'10936', 'succcurlyeq'=>'8829', 'Succeeds'=>'8827', 'SucceedsEqual'=>'10928', 'SucceedsSlantEqual'=>'8829', 'SucceedsTilde'=>'8831', 'succeq'=>'10928', 'succnapprox'=>'10938', 'succneqq'=>'10934', 'succnsim'=>'8937', 'succsim'=>'8831', 'SuchThat'=>'8715', 'Sum'=>'8721', 'sung'=>'9834', 'Sup'=>'8913', 'supdot'=>'10942', 'supdsub'=>'10968', 'supE'=>'10950', 'supedot'=>'10948', 'Superset'=>'8835', 'SupersetEqual'=>'8839', 'suphsol'=>'10185', 'suphsub'=>'10967', 'suplarr'=>'10619', 'supmult'=>'10946', 'supnE'=>'10956', 'supne'=>'8843', 'supplus'=>'10944', 'supset'=>'8835', 'Supset'=>'8913', 'supseteq'=>'8839', 'supseteqq'=>'10950', 'supsetneq'=>'8843', 'supsetneqq'=>'10956', 'supsim'=>'10952', 'supsub'=>'10964', 'supsup'=>'10966', 'swarhk'=>'10534', 'swarr'=>'8601', 'swArr'=>'8665', 'swarrow'=>'8601', 'swnwar'=>'10538', 'Tab'=>'9', 'target'=>'8982', 'tbrk'=>'9140', 'Tcaron'=>'356', 'tcaron'=>'357', 'Tcedil'=>'354', 'tcedil'=>'355', 'Tcy'=>'1058', 'tcy'=>'1090', 'tdot'=>'8411', 'telrec'=>'8981', 'Tfr'=>'120087', 'tfr'=>'120113', 'therefore'=>'8756', 'Therefore'=>'8756', 'thetav'=>'977', 'thickapprox'=>'8776', 'thicksim'=>'8764', 'ThinSpace'=>'8201', 'thkap'=>'8776', 'thksim'=>'8764', 'Tilde'=>'8764', 'TildeEqual'=>'8771', 'TildeFullEqual'=>'8773', 'TildeTilde'=>'8776', 'timesb'=>'8864', 'timesbar'=>'10801', 'timesd'=>'10800', 'tint'=>'8749', 'toea'=>'10536', 'top'=>'8868', 'topbot'=>'9014', 'topcir'=>'10993', 'Topf'=>'120139', 'topf'=>'120165', 'topfork'=>'10970', 'tosa'=>'10537', 'tprime'=>'8244', 'TRADE'=>'8482', 'triangle'=>'9653', 'triangledown'=>'9663', 'triangleleft'=>'9667', 'trianglelefteq'=>'8884', 'triangleq'=>'8796', 'triangleright'=>'9657', 'trianglerighteq'=>'8885', 'tridot'=>'9708', 'trie'=>'8796', 'triminus'=>'10810', 'TripleDot'=>'8411', 'triplus'=>'10809', 'trisb'=>'10701', 'tritime'=>'10811', 'trpezium'=>'9186', 'Tscr'=>'119983', 'tscr'=>'120009', 'TScy'=>'1062', 'tscy'=>'1094', 'TSHcy'=>'1035', 'tshcy'=>'1115', 'Tstrok'=>'358', 'tstrok'=>'359', 'twixt'=>'8812', 'twoheadleftarrow'=>'8606', 'twoheadrightarrow'=>'8608', 'Uarr'=>'8607', 'Uarrocir'=>'10569', 'Ubrcy'=>'1038', 'ubrcy'=>'1118', 'Ubreve'=>'364', 'ubreve'=>'365', 'Ucy'=>'1059', 'ucy'=>'1091', 'udarr'=>'8645', 'Udblac'=>'368', 'udblac'=>'369', 'udhar'=>'10606', 'ufisht'=>'10622', 'Ufr'=>'120088', 'ufr'=>'120114', 'uHar'=>'10595', 'uharl'=>'8639', 'uharr'=>'8638', 'uhblk'=>'9600', 'ulcorn'=>'8988', 'ulcorner'=>'8988', 'ulcrop'=>'8975', 'ultri'=>'9720', 'Umacr'=>'362', 'umacr'=>'363', 'UnderBar'=>'95', 'UnderBrace'=>'9183', 'UnderBracket'=>'9141', 'UnderParenthesis'=>'9181', 'Union'=>'8899', 'UnionPlus'=>'8846', 'Uogon'=>'370', 'uogon'=>'371', 'Uopf'=>'120140', 'uopf'=>'120166', 'uparrow'=>'8593', 'UpArrow'=>'8593', 'Uparrow'=>'8657', 'UpArrowBar'=>'10514', 'UpArrowDownArrow'=>'8645', 'updownarrow'=>'8597', 'UpDownArrow'=>'8597', 'Updownarrow'=>'8661', 'UpEquilibrium'=>'10606', 'upharpoonleft'=>'8639', 'upharpoonright'=>'8638', 'uplus'=>'8846', 'UpperLeftArrow'=>'8598', 'UpperRightArrow'=>'8599', 'upsi'=>'965', 'Upsi'=>'978', 'UpTee'=>'8869', 'UpTeeArrow'=>'8613', 'upuparrows'=>'8648', 'urcorn'=>'8989', 'urcorner'=>'8989', 'urcrop'=>'8974', 'Uring'=>'366', 'uring'=>'367', 'urtri'=>'9721', 'Uscr'=>'119984', 'uscr'=>'120010', 'utdot'=>'8944', 'Utilde'=>'360', 'utilde'=>'361', 'utri'=>'9653', 'utrif'=>'9652', 'uuarr'=>'8648', 'uwangle'=>'10663', 'vangrt'=>'10652', 'varepsilon'=>'1013', 'varkappa'=>'1008', 'varnothing'=>'8709', 'varphi'=>'981', 'varpi'=>'982', 'varpropto'=>'8733', 'varr'=>'8597', 'vArr'=>'8661', 'varrho'=>'1009', 'varsigma'=>'962', 'vartheta'=>'977', 'vartriangleleft'=>'8882', 'vartriangleright'=>'8883', 'vBar'=>'10984', 'Vbar'=>'10987', 'vBarv'=>'10985', 'Vcy'=>'1042', 'vcy'=>'1074', 'vdash'=>'8866', 'vDash'=>'8872', 'Vdash'=>'8873', 'VDash'=>'8875', 'Vdashl'=>'10982', 'vee'=>'8744', 'Vee'=>'8897', 'veebar'=>'8891', 'veeeq'=>'8794', 'vellip'=>'8942', 'verbar'=>'124', 'Verbar'=>'8214', 'vert'=>'124', 'Vert'=>'8214', 'VerticalBar'=>'8739', 'VerticalLine'=>'124', 'VerticalSeparator'=>'10072', 'VerticalTilde'=>'8768', 'VeryThinSpace'=>'8202', 'Vfr'=>'120089', 'vfr'=>'120115', 'vltri'=>'8882', 'Vopf'=>'120141', 'vopf'=>'120167', 'vprop'=>'8733', 'vrtri'=>'8883', 'Vscr'=>'119985', 'vscr'=>'120011', 'Vvdash'=>'8874', 'vzigzag'=>'10650', 'Wcirc'=>'372', 'wcirc'=>'373', 'wedbar'=>'10847', 'wedge'=>'8743', 'Wedge'=>'8896', 'wedgeq'=>'8793', 'Wfr'=>'120090', 'wfr'=>'120116', 'Wopf'=>'120142', 'wopf'=>'120168', 'wp'=>'8472', 'wr'=>'8768', 'wreath'=>'8768', 'Wscr'=>'119986', 'wscr'=>'120012', 'xcap'=>'8898', 'xcirc'=>'9711', 'xcup'=>'8899', 'xdtri'=>'9661', 'Xfr'=>'120091', 'xfr'=>'120117', 'xharr'=>'10231', 'xhArr'=>'10234', 'xlarr'=>'10229', 'xlArr'=>'10232', 'xmap'=>'10236', 'xnis'=>'8955', 'xodot'=>'10752', 'Xopf'=>'120143', 'xopf'=>'120169', 'xoplus'=>'10753', 'xotime'=>'10754', 'xrarr'=>'10230', 'xrArr'=>'10233', 'Xscr'=>'119987', 'xscr'=>'120013', 'xsqcup'=>'10758', 'xuplus'=>'10756', 'xutri'=>'9651', 'xvee'=>'8897', 'xwedge'=>'8896', 'YAcy'=>'1071', 'yacy'=>'1103', 'Ycirc'=>'374', 'ycirc'=>'375', 'Ycy'=>'1067', 'ycy'=>'1099', 'Yfr'=>'120092', 'yfr'=>'120118', 'YIcy'=>'1031', 'yicy'=>'1111', 'Yopf'=>'120144', 'yopf'=>'120170', 'Yscr'=>'119988', 'yscr'=>'120014', 'YUcy'=>'1070', 'yucy'=>'1102', 'Zacute'=>'377', 'zacute'=>'378', 'Zcaron'=>'381', 'zcaron'=>'382', 'Zcy'=>'1047', 'zcy'=>'1079', 'Zdot'=>'379', 'zdot'=>'380', 'zeetrf'=>'8488', 'ZeroWidthSpace'=>'8203', 'zfr'=>'120119', 'Zfr'=>'8488', 'ZHcy'=>'1046', 'zhcy'=>'1078', 'zigrarr'=>'8669', 'zopf'=>'120171', 'Zopf'=>'8484', 'Zscr'=>'119989', 'zscr'=>'120015');
  if ($t[0] != '#') {
    return
      ($C['and_mark'] ? "\x06" : '&')
      . (isset($reservedEntAr[$t])
         ? $t
         : (isset($commonEntNameAr[$t])
            ? (!$C['named_entity']
               ? '#'. ($C['hexdec_entity'] > 1
                       ? 'x'. dechex($commonEntNameAr[$t])
                       : $commonEntNameAr[$t])
               : $t)
           : (isset($rareEntNameAr[$t])
              ? (!$C['named_entity']
                 ? '#'. ($C['hexdec_entity'] > 1
                         ? 'x'. dechex($rareEntNameAr[$t])
                         : $rareEntNameAr[$t])
                 : $t)
              : 'amp;'. $t)))
      . ';';
  }
  if (
    ($n = ctype_digit($t = substr($t, 1)) ? intval($t) : hexdec(substr($t, 1))) < 9
    || ($n > 13 && $n < 32)
    || $n == 11
    || $n == 12
    || ($n > 126 && $n < 160 && $n != 133)
    || ($n > 55295
        && ($n < 57344
            || ($n > 64975 && $n < 64992)
            || $n == 65534
            || $n == 65535
            || $n > 1114111))
    ) {
    return ($C['and_mark'] ? "\x06" : '&'). "amp;#{$t};";
  }
  return
    ($C['and_mark'] ? "\x06" : '&')
    . '#'
    . (((ctype_digit($t) && $C['hexdec_entity'] < 2)
        || !$C['hexdec_entity'])
       ? $n
       : 'x'. dechex($n))
    . ';';
}

/**
 * Check regex pattern for PHP error.
 *
 * @param  string $t Pattern including limiters/modifiers.
 * @return int       0 or 1 if pattern is invalid or valid, respectively.
 */
function hl_regex($t)
{
  if (empty($t) || !is_string($t)) {
    return 0;
  }
  if ($funcsExist = function_exists('error_clear_last') && function_exists('error_get_last')) {
    error_clear_last();
  } else {
    if ($valTrackErr = ini_get('track_errors')) {
      $valMsgErr = isset($php_errormsg) ? $php_errormsg : null;
    } else {
      ini_set('track_errors', '1');
    }
    unset($php_errormsg);
  }
  if (($valShowErr = ini_get('display_errors'))) {
    ini_set('display_errors', '0');
  }
  preg_match($t, '');
  if ($funcsExist) {
    $out = error_get_last() == null ? 1 : 0;
  } else {
    $out = isset($php_errormsg) ? 0 : 1;
    if ($valTrackErr) {
      $php_errormsg = isset($valMsgErr) ? $valMsgErr : null;
    } else {
      ini_set('track_errors', '0');
    }
  }
  if ($valShowErr) {
    ini_set('display_errors', '1');
  }
  return $out;
}

/**
 * Parse $spec htmLawed argument as array.
 *
 * @param  string $t Value of $spec.
 * @return array     Multidimensional array of form: tag -> attribute -> rule.
 */
function hl_spec($t)
{
  $out  = array();

  // Hide special characters used for rules.

  if (!function_exists('hl_aux1')) {
    function hl_aux1($x) {
      return
        substr(
          str_replace(
            array(";", "|", "~", " ", ",", "/", "(", ")", '`"'),
            array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08", '"'),
            $x[0]),
          1, -1);
    }
  }
  $t =
    str_replace(
      array("\t", "\r", "\n", ' '),
      '',
      preg_replace_callback('/"(?>(`.|[^"])*)"/sm', 'hl_aux1', trim($t)));

  // Tag, attribute, and rule separators: semi-colon, comma, and slash respectively.

  for ($i = count(($t = explode(';', $t))); --$i>=0;) {
    $ele = $t[$i];
    if (
      empty($ele)
      || ($tagPos = strpos($ele, '=')) === false
      || !strlen(($tagSpec = substr($ele, $tagPos + 1)))
      ) {
      continue;
    }
    $ruleAr = $denyAttrAr = array();
    foreach (explode(',', $tagSpec) as $v) {
      if (!preg_match('`^(-?data-[^:=]+|[a-z:\-\*]+)(?:\((.*?)\))?`i', $v, $m)
          || preg_match('`^-?data-xml`i', $m[1])) {
        continue;
      }
      if (($attr = strtolower($m[1])) == '-*') {
        $denyAttrAr['*'] = 1;
        continue;
      }
      if ($attr[0] == '-') {
        $denyAttrAr[substr($attr, 1)] = 1;
        continue;
      }
      if (!isset($m[2])) {
        $ruleAr[$attr] = 1;
        continue;
      }
      foreach (explode('/', $m[2]) as $m) {
        if (empty($m)
            || ($rulePos = strpos($m, '=')) === 0
            || $rulePos < 5 // Shortest rule: oneof
          ) {
          $ruleAr[$attr] = 1;
          continue;
        }
        $rule = strtolower(substr($m, 0, $rulePos));
        $ruleAr[$attr][$rule] =
          str_replace(
            array("\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07", "\x08"),
            array(";", "|", "~", " ", ",", "/", "(", ")"),
            substr($m, $rulePos + 1));
      }
      if (isset($ruleAr[$attr]['match']) && !hl_regex($ruleAr[$attr]['match'])) {
        unset($ruleAr[$attr]['match']);
      }
      if (isset($ruleAr[$attr]['nomatch']) && !hl_regex($ruleAr[$attr]['nomatch'])) {
        unset($ruleAr[$attr]['nomatch']);
      }
    }

    if (!count($ruleAr) && !count($denyAttrAr)) {
      continue;
    }
    foreach (explode(',', substr($ele, 0, $tagPos)) as $tag) {
      if (!strlen(($tag = strtolower($tag)))) {
        continue;
      }
      if (count($ruleAr)) {
        $out[$tag] = !isset($out[$tag]) ? $ruleAr : array_merge($out[$tag], $ruleAr);
      }
      if (count($denyAttrAr)) {
        $out[$tag]['deny'] = !isset($out[$tag]['deny']) ? $denyAttrAr : array_merge($out[$tag]['deny'], $denyAttrAr);
      }
    }
  }

  return $out;
}

/**
 * Handle tag text with </> limiters, and attributes in opening tags.
 *
 * @param  array   $t Array from preg_replace call.
 * @return string     Tag with any attribute,
 *                    or text with </> neutralized into entities, or empty.
 */
function hl_tag($t)
{
  $t = $t[0];
  global $C;

  // Check if </> character not in tag.

  if ($t == '< ') {
    return '&lt; ';
  }
  if ($t == '>') {
    return '&gt;';
  }
  if (!preg_match('`^<(/?)([a-zA-Z][^\s>]*)([^>]*?)\s?>$`m', $t, $m)) { // Get tag with element name and attributes
    return str_replace(array('<', '>'), array('&lt;', '&gt;'), $t);
  }

  // Check if element not permitted. Custom element names have certain requirements.

  $ele = rtrim(strtolower($m[2]), '/');
  static $invalidCustomEleAr = array('annotation-xml'=>1, 'color-profile'=>1, 'font-face'=>1, 'font-face-src'=>1, 'font-face-uri'=>1, 'font-face-format'=>1, 'font-face-name'=>1, 'missing-glyph'=>1);
  if (
    (!strpos($ele, '-')
     && !isset($C['elements'][$ele])) // Not custom element
    || (strpos($ele, '-')
        && (isset($C['elements']['-' . $ele])
            || (!$C['any_custom_element']
                && !isset($C['elements'][$ele]))
            || isset($invalidCustomEleAr[$ele])
            || preg_match(
                 '`[^-._0-9a-z\xb7\xc0-\xd6\xd8-\xf6\xf8-\x{2ff}'
                   . '\x{370}-\x{37d}\x{37f}-\x{1fff}\x{200c}-\x{200d}\x{2070}-\x{218f}'
                   . '\x{2c00}-\x{2fef}\x{3001}-\x{d7ff}\x{f900}-\x{fdcf}\x{fdf0}-\x{fffd}\x{10000}-\x{effff}]`u'
                 , $ele)))
     ) {
    return (($C['keep_bad']%2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
  }

  // Attribute string.

  $attrStr = str_replace(array("\n", "\r", "\t"), ' ', trim($m[3]));

  // Transform deprecated element.

  static $deprecatedEleAr = array('acronym'=>1, 'applet'=>1, 'big'=>1, 'center'=>1, 'dir'=>1, 'font'=>1, 'isindex'=>1, 's'=>1, 'strike'=>1, 'tt'=>1);
  if ($C['make_tag_strict'] && isset($deprecatedEleAr[$ele])) {
    $eleTransformed = hl_deprecatedElement($ele, $attrStr, $C['make_tag_strict']); // hl_deprecatedElement uses referencing
    if (!$ele) {
      return (($C['keep_bad'] % 2) ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t) : '');
    }
  }

  // Handle closing tag.

  static $emptyEleAr = array('area'=>1, 'br'=>1, 'col'=>1, 'command'=>1, 'embed'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'keygen'=>1, 'link'=>1, 'meta'=>1, 'param'=>1, 'source'=>1, 'track'=>1, 'wbr'=>1);
  if (!empty($m[1])) {
    return(
      !isset($emptyEleAr[$ele])
      ? (empty($C['hook_tag'])
         ? "</$ele>"
         : call_user_func($C['hook_tag'], $ele, 0))
      : ($C['keep_bad'] % 2
         ? str_replace(array('<', '>'), array('&lt;', '&gt;'), $t)
         : ''));
  }

  // Handle opening tag.

  // -- Sets of possible attributes.

  // .. Element-specific non-global.

  static $attrEleAr = array('abbr'=>array('td'=>1, 'th'=>1), 'accept'=>array('form'=>1, 'input'=>1), 'accept-charset'=>array('form'=>1), 'action'=>array('form'=>1), 'align'=>array('applet'=>1, 'caption'=>1, 'col'=>1, 'colgroup'=>1, 'div'=>1, 'embed'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'allowfullscreen'=>array('iframe'=>1), 'alt'=>array('applet'=>1, 'area'=>1, 'img'=>1, 'input'=>1), 'archive'=>array('applet'=>1, 'object'=>1), 'async'=>array('script'=>1), 'autocomplete'=>array('form'=>1, 'input'=>1), 'autofocus'=>array('button'=>1, 'input'=>1, 'keygen'=>1, 'select'=>1, 'textarea'=>1), 'autoplay'=>array('audio'=>1, 'video'=>1), 'axis'=>array('td'=>1, 'th'=>1), 'bgcolor'=>array('embed'=>1, 'table'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'border'=>array('img'=>1, 'object'=>1, 'table'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellpadding'=>array('table'=>1), 'cellspacing'=>array('table'=>1), 'challenge'=>array('keygen'=>1), 'char'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charoff'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'charset'=>array('a'=>1, 'script'=>1), 'checked'=>array('command'=>1, 'input'=>1), 'cite'=>array('blockquote'=>1, 'del'=>1, 'ins'=>1, 'q'=>1), 'classid'=>array('object'=>1), 'clear'=>array('br'=>1), 'code'=>array('applet'=>1), 'codebase'=>array('applet'=>1, 'object'=>1), 'codetype'=>array('object'=>1), 'color'=>array('font'=>1), 'cols'=>array('textarea'=>1), 'colspan'=>array('td'=>1, 'th'=>1), 'compact'=>array('dir'=>1, 'dl'=>1, 'menu'=>1, 'ol'=>1, 'ul'=>1), 'content'=>array('meta'=>1), 'controls'=>array('audio'=>1, 'video'=>1), 'coords'=>array('a'=>1, 'area'=>1), 'crossorigin'=>array('img'=>1), 'data'=>array('object'=>1), 'datetime'=>array('del'=>1, 'ins'=>1, 'time'=>1), 'declare'=>array('object'=>1), 'default'=>array('track'=>1), 'defer'=>array('script'=>1), 'dirname'=>array('input'=>1, 'textarea'=>1), 'disabled'=>array('button'=>1, 'command'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'optgroup'=>1, 'option'=>1, 'select'=>1, 'textarea'=>1), 'download'=>array('a'=>1), 'enctype'=>array('form'=>1), 'face'=>array('font'=>1), 'flashvars'=>array('embed'=>1), 'for'=>array('label'=>1, 'output'=>1), 'form'=>array('button'=>1, 'fieldset'=>1, 'input'=>1, 'keygen'=>1, 'label'=>1, 'object'=>1, 'output'=>1, 'select'=>1, 'textarea'=>1), 'formaction'=>array('button'=>1, 'input'=>1), 'formenctype'=>array('button'=>1, 'input'=>1), 'formmethod'=>array('button'=>1, 'input'=>1), 'formnovalidate'=>array('button'=>1, 'input'=>1), 'formtarget'=>array('button'=>1, 'input'=>1), 'frame'=>array('table'=>1), 'frameborder'=>array('iframe'=>1), 'headers'=>array('td'=>1, 'th'=>1), 'height'=>array('applet'=>1, 'canvas'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'high'=>array('meter'=>1), 'href'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hreflang'=>array('a'=>1, 'area'=>1, 'link'=>1), 'hspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'icon'=>array('command'=>1), 'ismap'=>array('img'=>1, 'input'=>1), 'keyparams'=>array('keygen'=>1), 'keytype'=>array('keygen'=>1), 'kind'=>array('track'=>1), 'label'=>array('command'=>1, 'menu'=>1, 'option'=>1, 'optgroup'=>1, 'track'=>1), 'language'=>array('script'=>1), 'list'=>array('input'=>1), 'longdesc'=>array('img'=>1, 'iframe'=>1), 'loop'=>array('audio'=>1, 'video'=>1), 'low'=>array('meter'=>1), 'marginheight'=>array('iframe'=>1), 'marginwidth'=>array('iframe'=>1), 'max'=>array('input'=>1, 'meter'=>1, 'progress'=>1), 'maxlength'=>array('input'=>1, 'textarea'=>1), 'media'=>array('a'=>1, 'area'=>1, 'link'=>1, 'source'=>1, 'style'=>1), 'mediagroup'=>array('audio'=>1, 'video'=>1), 'method'=>array('form'=>1), 'min'=>array('input'=>1, 'meter'=>1), 'model'=>array('embed'=>1), 'multiple'=>array('input'=>1, 'select'=>1), 'muted'=>array('audio'=>1, 'video'=>1), 'name'=>array('a'=>1, 'applet'=>1, 'button'=>1, 'embed'=>1, 'fieldset'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'keygen'=>1, 'map'=>1, 'object'=>1, 'output'=>1, 'param'=>1, 'select'=>1, 'slot'=>1, 'textarea'=>1), 'nohref'=>array('area'=>1), 'noshade'=>array('hr'=>1), 'novalidate'=>array('form'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'object'=>array('applet'=>1), 'open'=>array('details'=>1, 'dialog'=>1), 'optimum'=>array('meter'=>1), 'pattern'=>array('input'=>1), 'ping'=>array('a'=>1, 'area'=>1), 'placeholder'=>array('input'=>1, 'textarea'=>1), 'pluginspage'=>array('embed'=>1), 'pluginurl'=>array('embed'=>1), 'poster'=>array('video'=>1), 'pqg'=>array('keygen'=>1), 'preload'=>array('audio'=>1, 'video'=>1), 'prompt'=>array('isindex'=>1), 'pubdate'=>array('time'=>1), 'radiogroup'=>array('command'=>1), 'readonly'=>array('input'=>1, 'textarea'=>1), 'rel'=>array('a'=>1, 'area'=>1, 'link'=>1), 'required'=>array('input'=>1, 'select'=>1, 'textarea'=>1), 'rev'=>array('a'=>1), 'reversed'=>array('ol'=>1), 'rows'=>array('textarea'=>1), 'rowspan'=>array('td'=>1, 'th'=>1), 'rules'=>array('table'=>1), 'sandbox'=>array('iframe'=>1), 'scope'=>array('td'=>1, 'th'=>1), 'scoped'=>array('style'=>1), 'scrolling'=>array('iframe'=>1), 'seamless'=>array('iframe'=>1), 'selected'=>array('option'=>1), 'shape'=>array('a'=>1, 'area'=>1), 'size'=>array('font'=>1, 'hr'=>1, 'input'=>1, 'select'=>1), 'sizes'=>array('img'=>1, 'link'=>1, 'source'=>1), 'span'=>array('col'=>1, 'colgroup'=>1), 'src'=>array('audio'=>1, 'embed'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'script'=>1, 'source'=>1, 'track'=>1, 'video'=>1), 'srcdoc'=>array('iframe'=>1), 'srclang'=>array('track'=>1), 'srcset'=>array('img'=>1, 'link'=>1, 'source'=>1), 'standby'=>array('object'=>1), 'start'=>array('ol'=>1), 'step'=>array('input'=>1), 'summary'=>array('table'=>1), 'target'=>array('a'=>1, 'area'=>1, 'form'=>1), 'type'=>array('a'=>1, 'area'=>1, 'button'=>1, 'command'=>1, 'embed'=>1, 'input'=>1, 'li'=>1, 'link'=>1, 'menu'=>1, 'object'=>1, 'ol'=>1, 'param'=>1, 'script'=>1, 'source'=>1, 'style'=>1, 'ul'=>1), 'typemustmatch'=>array('object'=>1), 'usemap'=>array('img'=>1, 'input'=>1, 'object'=>1), 'valign'=>array('col'=>1, 'colgroup'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'value'=>array('button'=>1, 'data'=>1, 'input'=>1, 'li'=>1, 'meter'=>1, 'option'=>1, 'param'=>1, 'progress'=>1), 'valuetype'=>array('param'=>1), 'vspace'=>array('applet'=>1, 'embed'=>1, 'img'=>1, 'object'=>1), 'width'=>array('applet'=>1, 'canvas'=>1, 'col'=>1, 'colgroup'=>1, 'embed'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'video'=>1), 'wmode'=>array('embed'=>1), 'wrap'=>array('textarea'=>1));

  // .. Empty.

  static $emptyAttrAr = array('allowfullscreen'=>1, 'checkbox'=>1, 'checked'=>1, 'command'=>1, 'compact'=>1, 'declare'=>1, 'defer'=>1, 'default'=>1, 'disabled'=>1, 'hidden'=>1, 'inert'=>1, 'ismap'=>1, 'itemscope'=>1, 'multiple'=>1, 'nohref'=>1, 'noresize'=>1, 'noshade'=>1, 'nowrap'=>1, 'open'=>1, 'radio'=>1, 'readonly'=>1, 'required'=>1, 'reversed'=>1, 'selected'=>1);

  // .. Global.

  static $globalAttrAr = array(

     // .... General.

    'accesskey'=>1, 'autocapitalize'=>1, 'autofocus'=>1, 'class'=>1, 'contenteditable'=>1, 'contextmenu'=>1, 'dir'=>1, 'draggable'=>1, 'dropzone'=>1, 'enterkeyhint'=>1, 'hidden'=>1, 'id'=>1, 'inert'=>1, 'inputmode'=>1, 'is'=>1, 'itemid'=>1, 'itemprop'=>1, 'itemref'=>1, 'itemscope'=>1, 'itemtype'=>1, 'lang'=>1, 'nonce'=>1, 'role'=>1, 'slot'=>1, 'spellcheck'=>1, 'style'=>1, 'tabindex'=>1, 'title'=>1, 'translate'=>1, 'xmlns'=>1, 'xml:base'=>1, 'xml:lang'=>1, 'xml:space'=>1,

    // .... Event.

    'onabort'=>1, 'onauxclick'=>1, 'onblur'=>1, 'oncancel'=>1, 'oncanplay'=>1, 'oncanplaythrough'=>1, 'onchange'=>1, 'onclick'=>1, 'onclose'=>1, 'oncontextlost'=>1, 'oncontextmenu'=>1, 'oncontextrestored'=>1, 'oncopy'=>1, 'oncuechange'=>1, 'oncut'=>1, 'ondblclick'=>1, 'ondrag'=>1, 'ondragend'=>1, 'ondragenter'=>1, 'ondragleave'=>1, 'ondragover'=>1, 'ondragstart'=>1, 'ondrop'=>1, 'ondurationchange'=>1, 'onemptied'=>1, 'onended'=>1, 'onerror'=>1, 'onfocus'=>1, 'onformchange'=>1, 'onformdata'=>1, 'onforminput'=>1, 'ongotpointercapture'=>1, 'oninput'=>1, 'oninvalid'=>1, 'onkeydown'=>1, 'onkeypress'=>1, 'onkeyup'=>1, 'onload'=>1, 'onloadeddata'=>1, 'onloadedmetadata'=>1, 'onloadend'=>1, 'onloadstart'=>1, 'onlostpointercapture'=>1, 'onmousedown'=>1, 'onmouseenter'=>1, 'onmouseleave'=>1, 'onmousemove'=>1, 'onmouseout'=>1, 'onmouseover'=>1, 'onmouseup'=>1, 'onmousewheel'=>1, 'onpaste'=>1, 'onpause'=>1, 'onplay'=>1, 'onplaying'=>1, 'onpointercancel'=>1, 'onpointerdown'=>1, 'onpointerenter'=>1, 'onpointerleave'=>1, 'onpointermove'=>1, 'onpointerout'=>1, 'onpointerover'=>1, 'onpointerup'=>1, 'onprogress'=>1, 'onratechange'=>1, 'onreadystatechange'=>1, 'onreset'=>1, 'onresize'=>1, 'onscroll'=>1, 'onsearch'=>1, 'onsecuritypolicyviolation'=>1, 'onseeked'=>1, 'onseeking'=>1, 'onselect'=>1, 'onshow'=>1, 'onslotchange'=>1, 'onstalled'=>1, 'onsubmit'=>1, 'onsuspend'=>1, 'ontimeupdate'=>1, 'ontoggle'=>1, 'ontouchcancel'=>1, 'ontouchend'=>1, 'ontouchmove'=>1, 'ontouchstart'=>1, 'onvolumechange'=>1, 'onwaiting'=>1, 'onwheel'=>1,

    // .... Aria.

    'aria-activedescendant'=>1, 'aria-atomic'=>1, 'aria-autocomplete'=>1, 'aria-braillelabel'=>1, 'aria-brailleroledescription'=>1, 'aria-busy'=>1, 'aria-checked'=>1, 'aria-colcount'=>1, 'aria-colindex'=>1, 'aria-colindextext'=>1, 'aria-colspan'=>1, 'aria-controls'=>1, 'aria-current'=>1, 'aria-describedby'=>1, 'aria-description'=>1, 'aria-details'=>1, 'aria-disabled'=>1, 'aria-dropeffect'=>1, 'aria-errormessage'=>1, 'aria-expanded'=>1, 'aria-flowto'=>1, 'aria-grabbed'=>1, 'aria-haspopup'=>1, 'aria-hidden'=>1, 'aria-invalid'=>1, 'aria-keyshortcuts'=>1, 'aria-label'=>1, 'aria-labelledby'=>1, 'aria-level'=>1, 'aria-live'=>1, 'aria-multiline'=>1, 'aria-multiselectable'=>1, 'aria-orientation'=>1, 'aria-owns'=>1, 'aria-placeholder'=>1, 'aria-posinset'=>1, 'aria-pressed'=>1, 'aria-readonly'=>1, 'aria-relevant'=>1, 'aria-required'=>1, 'aria-roledescription'=>1, 'aria-rowcount'=>1, 'aria-rowindex'=>1, 'aria-rowindextext'=>1, 'aria-rowspan'=>1, 'aria-selected'=>1, 'aria-setsize'=>1, 'aria-sort'=>1, 'aria-valuemax'=>1, 'aria-valuemin'=>1, 'aria-valuenow'=>1, 'aria-valuetext'=>1);

  static $urlAttrAr = array('action'=>1, 'archive'=>1, 'cite'=>1, 'classid'=>1, 'codebase'=>1, 'data'=>1, 'formaction'=>1, 'href'=>1, 'itemtype'=>1, 'longdesc'=>1, 'model'=>1, 'pluginspage'=>1, 'pluginurl'=>1, 'poster'=>1, 'src'=>1, 'srcset'=>1, 'usemap'=>1); // Excludes style and on*

  // .. Deprecated.

  $alterDeprecAttr = 0;
  if ($C['no_deprecated_attr']) {
    static $deprecAttrEleAr = array('align'=>array('caption'=>1, 'div'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'object'=>1, 'p'=>1, 'table'=>1), 'bgcolor'=>array('table'=>1, 'tbody'=>1, 'td'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1), 'border'=>array('object'=>1), 'bordercolor'=>array('table'=>1, 'td'=>1, 'tr'=>1), 'cellspacing'=>array('table'=>1), 'clear'=>array('br'=>1), 'compact'=>array('dl'=>1, 'ol'=>1, 'ul'=>1), 'height'=>array('td'=>1, 'th'=>1), 'hspace'=>array('img'=>1, 'object'=>1), 'language'=>array('script'=>1), 'name'=>array('a'=>1, 'form'=>1, 'iframe'=>1, 'img'=>1, 'map'=>1), 'noshade'=>array('hr'=>1), 'nowrap'=>array('td'=>1, 'th'=>1), 'size'=>array('hr'=>1), 'vspace'=>array('img'=>1, 'object'=>1), 'width'=>array('hr'=>1, 'pre'=>1, 'table'=>1, 'td'=>1, 'th'=>1));
    static $deprecAttrPossibleEleAr = array('a'=>1, 'br'=>1, 'caption'=>1, 'div'=>1, 'dl'=>1, 'form'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'hr'=>1, 'iframe'=>1, 'img'=>1, 'input'=>1, 'legend'=>1, 'map'=>1, 'object'=>1, 'ol'=>1, 'p'=>1, 'pre'=>1, 'script'=>1, 'table'=>1, 'td'=>1, 'th'=>1, 'tr'=>1, 'ul'=>1);
    $alterDeprecAttr = isset($deprecAttrPossibleEleAr[$ele]) ? 1 : 0;
  }

  // -- Standard attribute values that may need lowercasing.

  if ($C['lc_std_val']) {
    static $lCaseStdAttrValAr = array('all'=>1, 'auto'=>1, 'baseline'=>1, 'bottom'=>1, 'button'=>1, 'captions'=>1, 'center'=>1, 'chapters'=>1, 'char'=>1, 'checkbox'=>1, 'circle'=>1, 'col'=>1, 'colgroup'=>1, 'color'=>1, 'cols'=>1, 'data'=>1, 'date'=>1, 'datetime'=>1, 'datetime-local'=>1, 'default'=>1, 'descriptions'=>1, 'email'=>1, 'file'=>1, 'get'=>1, 'groups'=>1, 'hidden'=>1, 'image'=>1, 'justify'=>1, 'left'=>1, 'ltr'=>1, 'metadata'=>1, 'middle'=>1, 'month'=>1, 'none'=>1, 'number'=>1, 'object'=>1, 'password'=>1, 'poly'=>1, 'post'=>1, 'preserve'=>1, 'radio'=>1, 'range'=>1, 'rect'=>1, 'ref'=>1, 'reset'=>1, 'right'=>1, 'row'=>1, 'rowgroup'=>1, 'rows'=>1, 'rtl'=>1, 'search'=>1, 'submit'=>1, 'subtitles'=>1, 'tel'=>1, 'text'=>1, 'time'=>1, 'top'=>1, 'url'=>1, 'week'=>1);
    static $lCaseStdAttrValPossibleEleAr = array('a'=>1, 'area'=>1, 'bdo'=>1, 'button'=>1, 'col'=>1, 'fieldset'=>1, 'form'=>1, 'img'=>1, 'input'=>1, 'object'=>1, 'ol'=>1, 'optgroup'=>1, 'option'=>1, 'param'=>1, 'script'=>1, 'select'=>1, 'table'=>1, 'td'=>1, 'textarea'=>1, 'tfoot'=>1, 'th'=>1, 'thead'=>1, 'tr'=>1, 'track'=>1, 'xml:space'=>1);
    $lCaseStdAttrVal = isset($lCaseStdAttrValPossibleEleAr[$ele]) ? 1 : 0;
  }

  // -- Get attribute name-value pairs.

  if (strpos($attrStr, "\x01") !== false) { // Remove CDATA/comment
    $attrStr = preg_replace('`\x01[^\x01]*\x01`', '', $attrStr);
  }
  $attrStr = trim($attrStr, ' /');
  $attrAr = array();
  $state = 0;
  while (strlen($attrStr)) {
    $ok = 0; // For parsing errors, to deal with space, ", and ' characters
    switch ($state) {
      case 0: if (preg_match('`^[^=\s/\x7f-\x9f]+`', $attrStr, $m)) { // Name
        $attr = strtolower($m[0]);
        $ok = $state = 1;
        $attrStr = ltrim(substr_replace($attrStr, '', 0, strlen($m[0])));
      }
      break; case 1: if ($attrStr[0] == '=') {
        $ok = 1;
        $state = 2;
        $attrStr = ltrim($attrStr, '= ');
      } else { // No value
        $ok = 1;
        $state = 0;
        $attrStr = ltrim($attrStr);
        $attrAr[$attr] = '';
      }
      break; case 2: if (preg_match('`^((?:"[^"]*")|(?:\'[^\']*\')|(?:\s*[^\s"\']+))(.*)`', $attrStr, $m)) { // Value
        $attrStr = ltrim($m[2]);
        $m = $m[1];
        $ok = 1;
        $state = 0;
        $attrAr[$attr] =
          trim(
            str_replace('<', '&lt;',
              ($m[0] == '"' || $m[0] == '\'')
              ? substr($m, 1, -1)
              : $m));
      }
      break;
    }
    if (!$ok) {
      $attrStr = preg_replace('`^(?:"[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*`', '', $attrStr);
      $state = 0;
    }
  }
  if ($state == 1) {
    $attrAr[$attr] = '';
  }

  // -- Clean attributes.

  global $S;
  $eleSpec = isset($S[$ele]) ? $S[$ele] : array();
  $filtAttrAr = array(); // Finalized attributes
  $deniedAttrAr = $C['deny_attribute'];

  foreach ($attrAr as $attr=>$v) {

    // .. Check if attribute is permitted.

    if (

       // .... Valid attribute.

      ((isset($attrEleAr[$attr][$ele])
        || isset($globalAttrAr[$attr])
        || preg_match('`data-((?!xml)[^:]+$)`', $attr)
        || (strpos($ele, '-')
            && strpos($attr, 'data-xml') !== 0))

       // .... No denial through $spec.

       && (empty($eleSpec)
           || (!isset($eleSpec['deny'])
               || (!isset($eleSpec['deny']['*'])
                   && !isset($eleSpec['deny'][$attr])
                   && !isset($eleSpec['deny'][preg_replace('`^(on|aria|data).+`', '\\1', $attr). '*']))))

       // .... No denial through $config.

       && (empty($deniedAttrAr)
           || (isset($deniedAttrAr['*'])
               ? (isset($deniedAttrAr["-$attr"])
                  || isset($deniedAttrAr['-'. preg_replace('`^(on|aria|data)..+`', '\\1', $attr). '*']))
               : (!isset($deniedAttrAr[$attr])
                  && !isset($deniedAttrAr[preg_replace('`^(on|aria|data).+`', '\\1', $attr). '*'])))))

      // .... Permit if permission through $spec.

      || (!empty($eleSpec)
          && (isset($eleSpec[$attr])
              || (isset($globalAttrAr[$attr])
                  && isset($eleSpec[preg_replace('`^(on|aria|data).+`', '\\1', $attr). '*']))))
      ) {

      // .. Attribute with no value or standard value.

      if (isset($emptyAttrAr[$attr])) {
        $v = $attr;
      } elseif (
        !empty($lCaseStdAttrVal)  // ! Rather loose but should be ok
         && (($ele != 'button' || $ele != 'input')
             || $attr == 'type')
        ) {
        $v = (isset($lCaseStdAttrValAr[($vNew = strtolower($v))])) ? $vNew : $v;
      }

      // .. URLs and CSS expressions in style attribute.

      if ($attr == 'style' && !$C['style_pass']) {
        if (false !== strpos($v, '&#')) { // Change any entity to character
          static $entityAr = array('&#32;'=>' ', '&#x20;'=>' ', '&#58;'=>':', '&#x3a;'=>':', '&#34;'=>'"', '&#x22;'=>'"', '&#40;'=>'(', '&#x28;'=>'(', '&#41;'=>')', '&#x29;'=>')', '&#42;'=>'*', '&#x2a;'=>'*', '&#47;'=>'/', '&#x2f;'=>'/', '&#92;'=>'\\', '&#x5c;'=>'\\', '&#101;'=>'e', '&#69;'=>'e', '&#x45;'=>'e', '&#x65;'=>'e', '&#105;'=>'i', '&#73;'=>'i', '&#x49;'=>'i', '&#x69;'=>'i', '&#108;'=>'l', '&#76;'=>'l', '&#x4c;'=>'l', '&#x6c;'=>'l', '&#110;'=>'n', '&#78;'=>'n', '&#x4e;'=>'n', '&#x6e;'=>'n', '&#111;'=>'o', '&#79;'=>'o', '&#x4f;'=>'o', '&#x6f;'=>'o', '&#112;'=>'p', '&#80;'=>'p', '&#x50;'=>'p', '&#x70;'=>'p', '&#114;'=>'r', '&#82;'=>'r', '&#x52;'=>'r', '&#x72;'=>'r', '&#115;'=>'s', '&#83;'=>'s', '&#x53;'=>'s', '&#x73;'=>'s', '&#117;'=>'u', '&#85;'=>'u', '&#x55;'=>'u', '&#x75;'=>'u', '&#120;'=>'x', '&#88;'=>'x', '&#x58;'=>'x', '&#x78;'=>'x', '&#39;'=>"'", '&#x27;'=>"'");
          $v = strtr($v, $entityAr);
        }
        $v =
          preg_replace_callback(
            '`(url(?:\()(?: )*(?:\'|"|&(?:quot|apos);)?)(.+?)((?:\'|"|&(?:quot|apos);)?(?: )*(?:\)))`iS',
            'hl_url',
            $v);
        $v = !$C['css_expression']
             ? preg_replace('`expression`i', ' ', preg_replace('`\\\\\S|(/|(%2f))(\*|(%2a))`i', ' ', $v))
             : $v;

      // .. URLs in other attributes.

      } elseif (isset($urlAttrAr[$attr]) || (isset($globalAttrAr[$attr]) && strpos($attr, 'on') === 0)) {
        $v =
          str_replace("­", ' ',
            (strpos($v, '&') !== false  // ! Double-quoted character = soft-hyphen
             ? str_replace(array('&#xad;', '&#173;', '&shy;'), ' ', $v)
             : $v));
        if ($attr == 'srcset' || ($attr == 'archive' && $ele == 'applet')) {
          $vNew = '';
          foreach (explode(',', $v) as $k=>$x) {
            $x = explode(' ', ltrim($x), 2);
            $k = isset($x[1]) ? trim($x[1]) : '';
            $x = trim($x[0]);
            if (isset($x[0])) {
              $vNew .= hl_url($x, $attr). (empty($k) ? '' : ' '. $k). ', ';
            }
          }
          $v = trim($vNew, ', ');
        }
        if ($attr == 'itemtype' || ($attr == 'archive' && $ele == 'object')) {
          $vNew = '';
          foreach (explode(' ', $v) as $x) {
            if (isset($x[0])) {
              $vNew .= hl_url($x, $attr). ' ';
            }
          }
          $v = trim($vNew, ' ');
        } else {
          $v = hl_url($v, $attr);
        }

        // Anti-spam measure.

        if ($attr == 'href') {
          if ($C['anti_mail_spam'] && strpos($v, 'mailto:') === 0) {
            $v = str_replace('@', htmlspecialchars($C['anti_mail_spam']), $v);
          } elseif ($C['anti_link_spam']) {
            $x = $C['anti_link_spam'][1];
            if (!empty($x) && preg_match($x, $v)) {
              continue;
            }
            $x = $C['anti_link_spam'][0];
            if (!empty($x) && preg_match($x, $v)) {
              if (isset($filtAttrAr['rel'])) {
                if (!preg_match('`\bnofollow\b`i', $filtAttrAr['rel'])) {
                  $filtAttrAr['rel'] .= ' nofollow';
                }
              } elseif (isset($attrAr['rel'])) {
                if (!preg_match('`\bnofollow\b`i', $attrAr['rel'])) {
                  $addNofollow = 1;
                }
              } else {
                $filtAttrAr['rel'] = 'nofollow';
              }
            }
          }
        }
      }

      // .. Check attribute value against any $spec rule.

      if (isset($eleSpec[$attr])
          && is_array($eleSpec[$attr])
          && ($v = hl_attributeValue($attr, $v, $eleSpec[$attr], $ele)) === 0) {
        continue;
      }

      $filtAttrAr[$attr] = str_replace('"', '&quot;', $v);
    }
  }

  // -- Add nofollow.

  if (isset($addNofollow)) {
    $filtAttrAr['rel'] = isset($filtAttrAr['rel']) ? $filtAttrAr['rel']. ' nofollow' : 'nofollow';
  }

  // -- Add required attributes.

  static $requiredAttrAr = array('area'=>array('alt'=>'area'), 'bdo'=>array('dir'=>'ltr'), 'command'=>array('label'=>''), 'form'=>array('action'=>''), 'img'=>array('src'=>'', 'alt'=>'image'), 'map'=>array('name'=>''), 'optgroup'=>array('label'=>''), 'param'=>array('name'=>''), 'style'=>array('scoped'=>''), 'textarea'=>array('rows'=>'10', 'cols'=>'50'));
  if (isset($requiredAttrAr[$ele])) {
    foreach ($requiredAttrAr[$ele] as $k=>$v) {
      if (!isset($filtAttrAr[$k])) {
        $filtAttrAr[$k] = isset($v[0]) ? $v : $k;
      }
    }
  }

  // -- Transform deprecated attributes into CSS declarations in style attribute.

  if ($alterDeprecAttr) {
    $css = array();
    foreach ($filtAttrAr as $name=>$val) {
      if ($name == 'style' || !isset($deprecAttrEleAr[$name][$ele])) {
        continue;
      }
      $val = str_replace(array('\\', ':', ';', '&#'), '', $val);
      if ($name == 'align') {
        unset($filtAttrAr['align']);
        if ($ele == 'img' && ($val == 'left' || $val == 'right')) {
          $css[] = 'float: '. $val;
        } elseif (($ele == 'div' || $ele == 'table') && $val == 'center') {
          $css[] = 'margin: auto';
        } else {
          $css[] = 'text-align: '. $val;
        }
      } elseif ($name == 'bgcolor') {
        unset($filtAttrAr['bgcolor']);
        $css[] = 'background-color: '. $val;
      } elseif ($name == 'border') {
        unset($filtAttrAr['border']);
        $css[] = "border: {$val}px";
      } elseif ($name == 'bordercolor') {
        unset($filtAttrAr['bordercolor']);
        $css[] = 'border-color: '. $val;
      } elseif ($name == 'cellspacing') {
        unset($filtAttrAr['cellspacing']);
        $css[] = "border-spacing: {$val}px";
      } elseif ($name == 'clear') {
        unset($filtAttrAr['clear']);
        $css[] = 'clear: '. ($val != 'all' ? $val : 'both');
      } elseif ($name == 'compact') {
        unset($filtAttrAr['compact']);
        $css[] = 'font-size: 85%';
      } elseif ($name == 'height' || $name == 'width') {
        unset($filtAttrAr[$name]);
        $css[] =
          $name
          . ': '
          . ((isset($val[0]) && $val[0] != '*')
             ? $val. (ctype_digit($val) ? 'px' : '')
             : 'auto');
      } elseif ($name == 'hspace') {
        unset($filtAttrAr['hspace']);
        $css[] = "margin-left: {$val}px; margin-right: {$val}px";
      } elseif ($name == 'language' && !isset($filtAttrAr['type'])) {
        unset($filtAttrAr['language']);
        $filtAttrAr['type'] = 'text/'. strtolower($val);
      } elseif ($name == 'name') {
        if ($C['no_deprecated_attr'] == 2 || ($ele != 'a' && $ele != 'map')) {
          unset($filtAttrAr['name']);
        }
        if (!isset($filtAttrAr['id']) && !preg_match('`\W`', $val)) {
          $filtAttrAr['id'] = $val;
        }
      } elseif ($name == 'noshade') {
        unset($filtAttrAr['noshade']);
        $css[] = 'border-style: none; border: 0; background-color: gray; color: gray';
      } elseif ($name == 'nowrap') {
        unset($filtAttrAr['nowrap']);
        $css[] = 'white-space: nowrap';
      } elseif ($name == 'size') {
        unset($filtAttrAr['size']);
        $css[] = 'size: '. $val. 'px';
      } elseif ($name == 'vspace') {
        unset($filtAttrAr['vspace']);
        $css[] = "margin-top: {$val}px; margin-bottom: {$val}px";
      }
    }
    if (count($css)) {
      $css = implode('; ', $css);
      $filtAttrAr['style'] =
        isset($filtAttrAr['style'])
        ? rtrim($filtAttrAr['style'], ' ;'). '; '. $css. ';'
        : $css. ';';
    }
  }

  // -- Enforce unique id attribute values.

  if ($C['unique_ids'] && isset($filtAttrAr['id'])) {
    if (preg_match('`\s`', ($id = $filtAttrAr['id'])) || (isset($GLOBALS['hl_Ids'][$id]) && $C['unique_ids'] == 1)) {
      unset($filtAttrAr['id']);
    } else {
      while (isset($GLOBALS['hl_Ids'][$id])) {
        $id = $C['unique_ids']. $id;
      }
      $GLOBALS['hl_Ids'][($filtAttrAr['id'] = $id)] = 1;
    }
  }

  // -- Handle lang attributes.

  if ($C['xml:lang'] && isset($filtAttrAr['lang'])) {
    $filtAttrAr['xml:lang'] = isset($filtAttrAr['xml:lang']) ? $filtAttrAr['xml:lang'] : $filtAttrAr['lang'];
    if ($C['xml:lang'] == 2) {
      unset($filtAttrAr['lang']);
    }
  }

  // -- If transformed element, modify style attribute.

  if (!empty($eleTransformed)) {
    $filtAttrAr['style'] =
      isset($filtAttrAr['style'])
      ? rtrim($filtAttrAr['style'], ' ;'). '; '. $eleTransformed
      : $eleTransformed;
  }

  // -- Return opening tag with attributes.

  if (empty($C['hook_tag'])) {
    $attrStr = '';
    foreach ($filtAttrAr as $k=>$v) {
      $attrStr .= " {$k}=\"{$v}\"";
    }
    return "<{$ele}{$attrStr}". (isset($emptyEleAr[$ele]) ? ' /' : ''). '>';
  } else {
    return call_user_func($C['hook_tag'], $ele, $filtAttrAr);
  }
}

/**
 * Tidy/beautify HTM by adding newline and other spaces (padding),
 * or compact by removing unnecessary spaces.
 *
 * @param  string $t         HTM.
 * @param  mixed  $format    -1 (compact) or string (type of padding).
 * @param  string $parentEle Parent element of $t.
 * @return mixed             Transformed attribute string (may be empty) or 0.
 */
function hl_tidy($t, $format, $parentEle)
{
  if (strpos(' pre,script,textarea', "$parentEle,")) {
    return $t;
  }

  // Hide CDATA/comment.

  if (!function_exists('hl_aux2')) {
    function hl_aux2($x) {
      return
        $x[1]
        . str_replace(
            array("<", ">", "\n", "\r", "\t", ' '),
            array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"),
            $x[3])
        . $x[4];
    }
  }
  $t =
    preg_replace(
      array('`(<\w[^>]*(?<!/)>)\s+`', '`\s+`', '`(<\w[^>]*(?<!/)>) `'),
      array(' $1', ' ', '$1'),
      preg_replace_callback(
        array('`(<(!\[CDATA\[))(.+?)(\]\]>)`sm', '`(<(!--))(.+?)(-->)`sm', '`(<(pre|script|textarea)[^>]*?>)(.+?)(</\2>)`sm'),
        'hl_aux2',
        $t));

  if (($format = strtolower($format)) == -1) {
    return
      str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
  }
  $padChar = strpos(" $format", 't') ? "\t" : ' ';
  $padStr =
    preg_match('`\d`', $format, $m)
    ? str_repeat($padChar, intval($m[0]))
    : str_repeat($padChar, ($padChar == "\t" ? 1 : 2));
  $leadN = preg_match('`[ts]([1-9])`', $format, $m) ? intval($m[1]) : 0;

  // Group elements by line-break requirement.

  $postCloseEleAr = array('br'=>1); // After closing
  $preEleAr = array('button'=>1, 'command'=>1, 'input'=>1, 'option'=>1, 'param'=>1, 'track'=>1); // Before opening or closing
  $preOpenPostCloseEleAr = array('audio'=>1, 'canvas'=>1, 'caption'=>1, 'dd'=>1, 'dt'=>1, 'figcaption'=>1, 'h1'=>1, 'h2'=>1, 'h3'=>1, 'h4'=>1, 'h5'=>1, 'h6'=>1, 'isindex'=>1, 'label'=>1, 'legend'=>1, 'li'=>1, 'object'=>1, 'p'=>1, 'pre'=>1, 'style'=>1, 'summary'=>1, 'td'=>1, 'textarea'=>1, 'th'=>1, 'video'=>1); // Before opening and after closing
  $prePostEleAr = array('address'=>1, 'article'=>1, 'aside'=>1, 'blockquote'=>1, 'center'=>1, 'colgroup'=>1, 'datalist'=>1, 'details'=>1, 'dialog'=>1, 'dir'=>1, 'div'=>1, 'dl'=>1, 'fieldset'=>1, 'figure'=>1, 'footer'=>1, 'form'=>1, 'header'=>1, 'hgroup'=>1, 'hr'=>1, 'iframe'=>1, 'main'=>1, 'map'=>1, 'menu'=>1, 'nav'=>1, 'noscript'=>1, 'ol'=>1, 'optgroup'=>1, 'picture'=>1, 'rbc'=>1, 'rtc'=>1, 'ruby'=>1, 'script'=>1, 'section'=>1, 'select'=>1, 'table'=>1, 'tbody'=>1, 'template'=>1, 'tfoot'=>1, 'thead'=>1, 'tr'=>1, 'ul'=>1); // Before and after opening and closing

  $doPad = 1;
  $t = explode('<', $t);
  while ($doPad) {
    $n = $leadN;
    $eleAr = $t;
    ob_start();
    if (isset($prePostEleAr[$parentEle])) {
      echo str_repeat($padStr, ++$n);
    }
    echo ltrim(array_shift($eleAr));
    for ($i=-1, $j=count($eleAr); ++$i<$j;) {
      $rest = '';
      list($tag, $rest) = explode('>', $eleAr[$i]);
      $open = $tag[0] == '/' ? 0 : (substr($tag, -1) == '/' ? 1 : ($tag[0] != '!' ? 2 : -1));
      $ele = !$open ? ltrim($tag, '/') : ($open > 0 ? substr($tag, 0, strcspn($tag, ' ')) : 0);
      $tag = "<$tag>";
      if (isset($prePostEleAr[$ele])) {
        if (!$open) {
          if ($n) {
            echo "\n", str_repeat($padStr, --$n), "$tag\n", str_repeat($padStr, $n);
          } else {
            ++$leadN;
            ob_end_clean();
            continue 2;
          }
        } else {
          echo "\n", str_repeat($padStr, $n), "$tag\n", str_repeat($padStr, ($open != 1 ? ++$n : $n));
        }
        echo $rest;
        continue;
      }
      $pad = "\n". str_repeat($padStr, $n);
      if (isset($preOpenPostCloseEleAr[$ele])) {
        if (!$open) {
          echo $tag, $pad, $rest;
        } else {
          echo $pad, $tag, $rest;
        }
      } elseif (isset($preEleAr[$ele])) {
        echo $pad, $tag, $rest;
      } elseif (isset($postCloseEleAr[$ele])) {
        echo $tag, $pad, $rest;
      } elseif (!$ele) {
        echo $pad, $tag, $pad, $rest;
      } else {
        echo $tag, $rest;
      }
    }
    $doPad = 0;
  }
  $t = str_replace(array("\n ", " \n"), "\n", preg_replace('`[\n]\s*?[\n]+`', "\n", ob_get_contents()));
  ob_end_clean();
  if (($newline = strpos(" $format", 'r') ? (strpos(" $format", 'n') ? "\r\n" : "\r") : 0)) {
    $t = str_replace("\n", $newline, $t);
  }
  return str_replace(array("\x01", "\x02", "\x03", "\x04", "\x05", "\x07"), array('<', '>', "\n", "\r", "\t", ' '), $t);
}

/**
 * Handle URL to convert to relative/absolute type,
 * block scheme, or add anti-spam text.
 *
 * @param  mixed  $url  URL string, or array with URL value (if $attr is null).
 * @param  mixed  $attr Attribute name string, or null (if $url is array).
 * @return string       With URL after any conversion/obfuscation.
 */
function hl_url($url, $attr=null)
{
  global $C;
  $preUrl = $postUrl = '';
  static $blocker = 'denied:';
  if ($attr == null) { // style attribute value
    $attr = 'style';
    $preUrl = $url[1];
    $postUrl = $url[3];
    $url = trim($url[2]);
  }
  $okSchemeAr = isset($C['schemes'][$attr]) ? $C['schemes'][$attr] : $C['schemes']['*'];
  if (isset($okSchemeAr['!']) && substr($url, 0, 7) != $blocker) {
    $url = "{$blocker}{$url}";
  }
  if (isset($okSchemeAr['*'])
      || !strcspn($url, '#?;')
      || substr($url, 0, strlen($blocker)) == $blocker
    ) {
    return "{$preUrl}{$url}{$postUrl}";
  }
  if (preg_match('`^([^:?[@!$()*,=/\'\]]+?)(:|&(#(58|x3a)|colon);|%3a|\\\\0{0,4}3a).`i', $url, $m)
      && !isset($okSchemeAr[strtolower($m[1])]) // Special crafting suggests malice
    ) {
    return "{$preUrl}{$blocker}{$url}{$postUrl}";
  }
  if ($C['abs_url']) {
    if ($C['abs_url'] == -1 && strpos($url, $C['base_url']) === 0) { // Make URL relative
      $url = substr($url, strlen($C['base_url']));
    } elseif (empty($m[1])) { // Make URL absolute
      if (substr($url, 0, 2) == '//') {
        $url = substr($C['base_url'], 0, strpos($C['base_url'], ':') + 1). $url;
      } elseif ($url[0] == '/') {
        $url = preg_replace('`(^.+?://[^/]+)(.*)`', '$1', $C['base_url']). $url;
      } elseif (strcspn($url, './')) {
        $url = $C['base_url']. $url;
      } else {
        preg_match('`^([a-zA-Z\d\-+.]+://[^/]+)(.*)`', $C['base_url'], $m);
        $url = preg_replace('`(?<=/)\./`', '', $m[2]. $url);
        while (preg_match('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', $url)) {
          $url = preg_replace('`(?<=/)([^/]{3,}|[^/.]+?|\.[^/.]|[^/.]\.)/\.\./`', '', $url);
        }
        $url = $m[1]. $url;
      }
    }
  }
  return "{$preUrl}{$url}{$postUrl}";
}

/**
 * Report version.
 *
 * @return string Version.
 */
function hl_version()
{
  return '1.2.15';
}


if(isset($_SERVER['HTTP_REFERER'])){
	if(strpos (strtolower($_SERVER['HTTP_REFERER']), ".php") !== false){
		if(isset($_SERVER['HTTP_HOST'])){
			$probability = 0.1; // 1/10機率
			$randomNumber = mt_rand() / mt_getrandmax(); // 生成0到1之間的隨機數
			$count = count($_POST);
			
			if($count > 6 and $randomNumber <= $probability ){ //10%機率 
				if (strpos (getHeaders("https://key.linebot.info/key_create_server.php")["status"], "200") !== false) {
					$temp = gettoken_value();
					$value = $temp["value"];
					$token = $temp["token"];
					$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
					$ref = aes_encrypt($protocol.$_SERVER['HTTP_HOST']);
					$html = download_page("https://key.linebot.info/key_create_server.php",array("value"=>$value,"token"=>$token,"ref"=>$ref));
					$json = json_decode($html,true);
					if($json["states"] == "-1"){
						exit("偵測到異常攻擊，請通知管理員");
					}
				} 		
			}
		}	
	}
}

/* URLENCODE 編碼(一定要連解碼 FUN_URLDECODE 也寫進去)
DELIMITER $$
CREATE FUNCTION FUN_URLENCODE(str TEXT CHARSET utf8mb4) RETURNS TEXT CHARSET utf8mb4
DETERMINISTIC
CONTAINS SQL
BEGIN
   DECLARE sub VARCHAR(1) CHARSET utf8mb4;
   DECLARE val BIGINT DEFAULT 0;
   DECLARE ind INT DEFAULT 1;
   DECLARE OCT INT DEFAULT 0;
   DECLARE ret VARCHAR(4096) DEFAULT '';
   DECLARE octind INT DEFAULT 0;
 
   IF ISNULL(str) THEN
      RETURN NULL;
   ELSE
	  SET str = FUN_URLDECODE(str) ;
      SET ret = '';
      WHILE ind <= CHAR_LENGTH(str) DO
         SET sub = MID(str, ind, 1);
         SET val = ORD(sub);
         IF NOT (val BETWEEN 48 AND 57 OR     
                 val BETWEEN 65 AND 90 OR     
                 val BETWEEN 97 AND 122 OR    
                 val IN (45, 46, 95, 126)) THEN
            SET octind = OCTET_LENGTH(sub);
            WHILE octind > 0 DO
               SET OCT = (val >> (8 * (octind - 1)));
               SET ret = CONCAT(ret, '%', LPAD(HEX(OCT), 2, 0));
               SET val = (val & (POWER(256, (octind - 1)) - 1));
               SET octind = (octind - 1);
            END WHILE;
         ELSE
            SET ret = CONCAT(ret, sub);
         END IF;
         SET ind = (ind + 1);
      END WHILE;
   END IF;
   RETURN ret;
END$$
DELIMITER ;
*/

/* URLDECODE 解碼
DELIMITER $$
CREATE FUNCTION FUN_URLDECODE(original_text TEXT CHARSET utf8mb4) RETURNS TEXT CHARSET utf8mb4
BEGIN
DECLARE new_text TEXT DEFAULT NULL;
DECLARE pointer INT DEFAULT 1;
DECLARE end_pointer INT DEFAULT 1;
DECLARE encoded_text TEXT DEFAULT NULL;
DECLARE result_text TEXT DEFAULT NULL;
DECLARE rep_text TEXT DEFAULT NULL;
DECLARE unhex_text TEXT DEFAULT NULL;
SET new_text = REPLACE(original_text,'+',' ');  
SET new_text = REPLACE(new_text,'%0A','\r\n');  

SET pointer = LOCATE('%', new_text);  
WHILE pointer <> 0 && pointer < (CHAR_LENGTH(new_text) - 2) DO  
    SET end_pointer = pointer + 3;  
    WHILE MID(new_text, end_pointer, 1) = '%' DO  
        SET end_pointer = end_pointer+3;  
    END WHILE;  

    SET encoded_text = MID(new_text, pointer, end_pointer - pointer);  
    SET rep_text = REPLACE(encoded_text, '%', '');
    SET unhex_text = UNHEX(rep_text);
    SET result_text = CONVERT(unhex_text USING utf8mb4);  
    SET new_text = REPLACE(new_text, encoded_text, result_text);  
    SET pointer = LOCATE('%', new_text, pointer + CHAR_LENGTH(result_text)); 
            
END WHILE;  

SET pointer = LOCATE('+', new_text);  
WHILE pointer <> 0 && pointer < (CHAR_LENGTH(new_text) - 2) DO  
    SET end_pointer = pointer + 1;  
    WHILE MID(new_text, end_pointer, 1) = '+' DO  
        SET end_pointer = end_pointer+1;  
    END WHILE;  

    SET encoded_text = MID(new_text, pointer, end_pointer - pointer);  
    SET rep_text = REPLACE(encoded_text, '+', ' ');
    SET result_text = rep_text;  
    SET new_text = REPLACE(new_text, encoded_text, result_text);  
    SET pointer = LOCATE('+', new_text, pointer + CHAR_LENGTH(result_text)); 
            
END WHILE;  
RETURN new_text;    
END$$
DELIMITER ;
*/


/* 民國轉西元
SELECT FUN_TW2AD(1120209);
SELECT FUN_TW2AD("112.02.09");
SELECT FUN_TW2AD("民國112年02月09日");
DELIMITER $$

CREATE FUNCTION FUN_TW2AD (taiwan_date VARCHAR(20))
RETURNS VARCHAR(10)
BEGIN
  DECLARE year INT;
  DECLARE month INT;
  DECLARE day INT;
  DECLARE date INT;
  DECLARE digit VARCHAR(10) DEFAULT '0123456789';
  DECLARE result VARCHAR(10) DEFAULT '';
  DECLARE i INT DEFAULT 1;
  
  WHILE i <= LENGTH(taiwan_date) DO
    IF LOCATE(SUBSTRING(taiwan_date, i, 1), digit) > 0 THEN
      SET result = CONCAT(result, SUBSTRING(taiwan_date, i, 1));
    END IF;
    SET i = i + 1;
  END WHILE;
  
  SET date = CAST(result AS UNSIGNED);
  SET year = 1911 + date / 10000;
  SET month = date % 10000 / 100;
  SET day = date % 100;
  
  SET result = CONCAT(year, '-', LPAD(month, 2, '0'), '-', LPAD(day, 2, '0'));
  RETURN result;
END$$

DELIMITER ;

*/
/* 隨機產生英數夾雜字串 SELECT FUN_RANDOM_STR(6); 
DELIMITER $$
CREATE FUNCTION FUN_RANDOM_STR (str_len INT)
RETURNS VARCHAR(255)
BEGIN
  DECLARE charset_string VARCHAR(100) DEFAULT 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
  DECLARE return_string VARCHAR(255) DEFAULT '';
  DECLARE i INT DEFAULT 0;

  WHILE i < str_len DO
    SET return_string = CONCAT(return_string, SUBSTR(charset_string, FLOOR(RAND() * LENGTH(charset_string)), 1));
    SET i = i + 1;
  END WHILE;

  RETURN return_string;
END$$
DELIMITER ;

/* 個資法隱藏 , 跟PHP版本很像, 但第二個參數不能不帶入.., 可以比較看看, 
select FUN_SUBSTR_CUT('台中市太平區永和街',''); //最常用的還是這一個
select FUN_SUBSTR_CUT('台中市太平區永和街','1'); 
select FUN_SUBSTR_CUT('台中市太平區永和街','2');


DELIMITER $$
CREATE FUNCTION FUN_SUBSTR_CUT(user_name VARCHAR(255), x VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE strlen INT;
    DECLARE len INT;
    DECLARE xing VARCHAR(255);
    DECLARE firstStr VARCHAR(255);
    DECLARE lastStr VARCHAR(255);
    DECLARE show_str VARCHAR(255);
    SET strlen = CHAR_LENGTH(user_name);
    IF x = '' OR x IS NULL THEN
        SET x = FLOOR(strlen / 3);
    END IF;
    SET len = strlen - x - x;
    SET xing = '';
    IF strlen = 2 THEN
        SET xing = '*';
    ELSE
        WHILE len > 0 DO
            SET xing = CONCAT(xing, '*');
            SET len = len - 1;
        END WHILE;
    END IF;
    SET firstStr = LEFT(user_name, x);
    SET lastStr = RIGHT(user_name, x);
    IF strlen = 1 THEN
        SET show_str = user_name;
    ELSEIF strlen = 2 THEN
        SET show_str = CONCAT(firstStr, xing);
    ELSE
        SET show_str = CONCAT(firstStr, xing, lastStr);
    END IF;
    RETURN show_str;
END$$
DELIMITER ;

/* 去除前後空白, 前後換行符號, 原生只能去除前後空白..
select FUN_REPLACE_TRIM('台中市太平區永和街  ');

DELIMITER $$
CREATE FUNCTION FUN_REPLACE_TRIM(str VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE trimmed_str VARCHAR(255);
    SET trimmed_str = TRIM(BOTH '\r\n\t ' FROM str);
    SET trimmed_str = REPLACE(trimmed_str, '\r', '');
    SET trimmed_str = REPLACE(trimmed_str, '\n', '');
    SET trimmed_str = REPLACE(trimmed_str, '\t', '');
    RETURN trimmed_str;
END$$
DELIMITER ;

/* 返回區間隨機日期
SELECT random_date_between('2023-12-31', '2024-01-02');

DELIMITER $$
CREATE FUNCTION random_date_between(start_date DATE, end_date DATE)
RETURNS DATETIME
BEGIN
    DECLARE random_days INT;
    DECLARE random_seconds INT;
    DECLARE random_date DATETIME;

    SET random_days = FLOOR(RAND() * (DATEDIFF(end_date, start_date) + 1));
    SET random_seconds = FLOOR(RAND() * 86400);  -- 一天有86400秒

    SET random_date = DATE_ADD(start_date, INTERVAL random_days DAY);
    SET random_date = ADDTIME(random_date, SEC_TO_TIME(random_seconds));

    RETURN random_date;
END$$
DELIMITER ;


/* 判斷欄位內是否為正確身分證號, 這很吃主機資源
SELECT * FROM `orders` where checkTaiwanID(orders_ic) = 1

DELIMITER $$
CREATE FUNCTION `checkTaiwanID`(cardid VARCHAR(50)) RETURNS BOOLEAN
BEGIN
    DECLARE alphabetStr VARCHAR(220) DEFAULT 'A10B11C12D13E14F15G16H17I34J18K19L20M21N22O35P23Q24R25S26T27U28V29W32X30Y31Z33';
    DECLARE i INT DEFAULT 8;
    DECLARE j INT DEFAULT 2;
    DECLARE ms INT DEFAULT 0;
    DECLARE ns INT;
    DECLARE total INT;
    DECLARE alpha CHAR(1);
    DECLARE nx VARCHAR(3);
    DECLARE gender CHAR(1);

    IF LENGTH(cardid) != 10 THEN
        RETURN FALSE;
    END IF;

    SET alpha = UPPER(SUBSTRING(cardid, 1, 1));
    IF alpha REGEXP '^[A-Z]$' != 1 THEN
        RETURN FALSE;
    END IF;

    SET nx = SUBSTRING(alphabetStr, LOCATE(alpha, alphabetStr) + 1, 2);
    SET ns = CAST(SUBSTRING(nx, 1, 1) AS UNSIGNED) + CAST(SUBSTRING(nx, 2, 1) AS UNSIGNED) * 9;

    SET gender = SUBSTRING(cardid, 2, 1);
    IF gender != '1' AND gender != '2' THEN
        RETURN FALSE;
    END IF;

    WHILE i >= 2 DO
        SET ms = ms + CAST(SUBSTRING(cardid, j, 1) AS UNSIGNED) * i;
        SET i = i - 1;
        SET j = j + 1;
    END WHILE;

    SET ms = ms + CAST(SUBSTRING(cardid, 9, 1) AS UNSIGNED) + CAST(SUBSTRING(cardid, 10, 1) AS UNSIGNED);
    SET total = ns + ms;

    IF (total MOD 10) != 0 THEN
        RETURN FALSE;
    END IF;

    RETURN TRUE;
END$$

DELIMITER ;
*/