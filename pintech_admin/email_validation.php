<?php

include_once(dirname(__FILE__)."/../phplibs/front_head.php");

@$email = params_security($_POST["email"]);
@$email_encrypted = params_security($_POST["email_encrypted"]);

$err_msg = [];

if(empty($email) || empty($email_decrypted)){
   array_push($err_msg, "參數未完整");
}else{
    if($email == aes_decrypt($email_encrypted)){

    }
}

