<?php
include_once(dirname(__FILE__)."/../phplibs/root.php");
include_once(dirname(__FILE__)."/../phplibs/root_global.php");
@$value = params_security($_GET["value"]);
@$token = params_security($_GET["token"]);
/* 下面第16行需要載入JQ,如果遇到內網必須下載回本地 */
if(!token_validation($value, $token)){
	exit;
}
?>
<!DOCTYPE html>
<!--
Copyright (c) 2007-2018, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or https://ckeditor.com/sales/license/ckfinder
-->
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
	<title>CKFinder 3 - File Browser</title>
</head>
<body>

<script src="ckfinder.js"></script>
<script>
	CKFinder.start();
</script>

</body>
</html>

