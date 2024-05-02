<?php
include_once(dirname(__FILE__)."/../phplibs/backend_head.php");

if(array_key_exists('admin', $_SESSION) && !empty($_SESSION['admin'])){
    unset($_SESSION['admin']);
}

echo "<script>document.location.href = 'index.php'</script>";