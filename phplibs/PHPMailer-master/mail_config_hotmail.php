<?php
include_once(dirname(__FILE__)."/./PHPMailerAutoload.php");	
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
mb_internal_encoding('UTF-8'); 


function sendmail($title,$content,$tomail,$file = ""){
	// 產生 Mailer 實體
	$mail = new PHPMailer();

	// 設定為 SMTP 方式寄信
	$mail->IsSMTP();
//
	// SMTP 伺服器的設定，以及驗證資訊
	$mail->SMTPAuth = true; 
	//$mail->SMTPDebug = 2;

	// SMTP 驗證的使用者資訊
	$mail->Host = "smtp-mail.outlook.com"; //此處請填寫您的郵件伺服器位置,通常是mail.網址。如果您MX指到外地，那這邊填入www.XXX.com 即可
	$mail->Port = 587; //ServerZoo主機的郵件伺服器port為 25 

	// 信件內容的編碼方式       
	$mail->CharSet = "utf-8";

	// 信件處理的編碼方式
	$mail->Encoding = "base64";

	// SMTP 驗證的使用者資訊
	$mail->Username = "site-service@hotmail.com";  // 此處為驗証電子郵件帳號,就是您在ServerZoo主機上新增的電子郵件帳號，＠後面請務必一定要打。
	$mail->Password = "amt198455";  //此處為上方電子郵件帳號的密碼 (一定要正確不然會無法寄出)

	// 信件內容設定  
	$mail->From = "site-service@hotmail.com"; //此處為寄出後收件者顯示寄件者的電子郵件 (請設成與上方驗証電子郵件一樣的位址)
	$mail->FromName = "site-service@hotmail.com"; //這裡不能是中文～此處為寄出後收件者顯示寄件者的名稱
	
    $mail->AddReplyTo('site-service@hotmail.com', 'site-service@hotmail.com');
	$mail->Subject = mb_encode_mimeheader(str_replace("&amp;","&",$title), "UTF-8");
	
	$mail->Body = STRIPSLASHES($content);   //信件內容 
	$mail->IsHTML(true);
	$mail->ClearAddresses();
	$mail->ClearAttachments();
	// 收件人
	//$mail->AddAddress($tomail, $tomail); //此處為收件者的電子信箱及顯示名稱
	$mail->AddAddress("site-service@hotmail.com");
	
	$file_arr = preg_split("/[,;]/", $file);
    foreach($file_arr as $key => $value){ 
		if (file_exists($value)) {
			$mail->addAttachment($value);
		}
    } 
	
	

	$pieces = preg_split("/[,;]/", $tomail);
    foreach($pieces as $key => $value){ 
		if(filter_var($value, FILTER_VALIDATE_EMAIL)){
			$mail->AddBCC($value);
		}
    } 	

	// 顯示訊息
	if(!$mail->Send()) {     
		return false;   
		//return "Mail error: " . $mail->ErrorInfo;     
	}else{
		return true;  
	}
}
?>