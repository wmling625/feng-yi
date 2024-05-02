<?php
include_once(dirname(__FILE__)."/./PHPMailerAutoload.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
mb_internal_encoding('UTF-8');


function sendmail($title,$content,$tomail,$file = ""){

    $mail_arr = array( array("notify1@mail-service.tw"=>"whodjgahnapktebw"), array("notify2@mail-service.tw"=>"frwnxnepxrmjvkus"));
    $one_mail = $mail_arr[array_rand($mail_arr,1)];
    $mail_key = key($one_mail);
    $mail_pwd = $one_mail[$mail_key];



    // 產生 Mailer 實體
    $mail = new PHPMailer();

    // 設定為 SMTP 方式寄信
    $mail->IsSMTP();
//
    // SMTP 伺服器的設定，以及驗證資訊
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";
    //$mail->SMTPDebug = 2;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // SMTP 驗證的使用者資訊
    $mail->Host = "smtp.gmail.com"; //此處請填寫您的郵件伺服器位置,通常是mail.網址。如果您MX指到外地，那這邊填入www.XXX.com 即可
    //$mail->Port = 465; //ServerZoo主機的郵件伺服器port為 25
    $mail->Port = 587;

    // 信件內容的編碼方式
    $mail->CharSet = "utf-8";

    // 信件處理的編碼方式
    $mail->Encoding = "base64";

    // SMTP 驗證的使用者資訊
    $mail->Username = $mail_key;  // 此處為驗証電子郵件帳號,就是您在ServerZoo主機上新增的電子郵件帳號，＠後面請務必一定要打。
    $mail->Password = $mail_pwd;  //此處為上方電子郵件帳號的密碼 (一定要正確不然會無法寄出)

    // 信件內容設定
    $mail->From = $mail_key; //此處為寄出後收件者顯示寄件者的電子郵件 (請設成與上方驗証電子郵件一樣的位址)
    $mail->FromName = $mail_key; //這裡不能是中文～此處為寄出後收件者顯示寄件者的名稱

    $mail->AddReplyTo($mail_key, $mail_key);
    $mail->Subject = mb_encode_mimeheader(str_replace("&amp;","&",$title), "UTF-8");

    $mail->Body = STRIPSLASHES($content."<br/><br/>********************************************************<br/><br/>本信件自動發出請勿回信");   //信件內容
    $mail->IsHTML(true);
    $mail->ClearAddresses();
    $mail->ClearAttachments();
    // 收件人
    //$mail->AddAddress($tomail, $tomail); //此處為收件者的電子信箱及顯示名稱
    $mail->AddAddress($mail_key);

    $file_arr = preg_split("/[,;]/", $file);
    foreach($file_arr as $key => $value){
        if (file_exists($value)) {
            $mail->addAttachment($value);
        }
    }


    $tomail = replace_trim($tomail,"any");
    $pieces = preg_split("/[,;]/", $tomail);

    if (count($pieces) > 0) {
        $avg_arr = array_chunk($pieces, 100);
        foreach ($avg_arr as $key => $value) {
            foreach ($value as $key1 => $value1) {
                if(filter_var($value1, FILTER_VALIDATE_EMAIL)){
                    $mail->AddBCC($value1);
                }
            }
            $mail->Send();
            $mail->ClearBCCs();
        }
    }
    return true;

    /*
    if(!$mail->Send()) {
        return false;
        //return "Mail error: " . $mail->ErrorInfo;
    }else{
        return true;
    }
    */
}
?>