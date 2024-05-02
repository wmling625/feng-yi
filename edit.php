<!DOCTYPE html>
<html class="no-js">
<head>
	<meta charset="utf-8">
	<meta name="description" content="會員通知">
	<meta name="keywords" content="會員通知,移車QR CODE,移動條碼" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="google" content="notranslate" />
	<meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>綁定QR CODE-會員通知</title>
	<meta http-equiv="Content-Security-Policy" content="default-src * gap://ready file:; worker-src blob:; child-src blob: gap:; style-src * 'unsafe-inline'; script-src * 'unsafe-inline' 'unsafe-eval' blob:; img-src * 'self' data: blob:; connect-src * 'unsafe-inline'; font-src 'self' data: *;">
	<meta name="format-detection" content="telephone=no">

    <?php include_once(dirname(__FILE__) . "/phplibs/front_link.php") ?>
</head>

<body>
	<div id="main" class="main">
		<div class="page">
			<div class="title-box">
				<div class="container container-shrink">
					<div class="row justify-content-center no-gutters">
						<div class="col-lg-6">
							<h4 class="title font-weight-6">編輯QR Code</h4>
						</div>
					</div>
				</div>
			</div>
			<div class="container container-shrink">
				<div class="row justify-content-center no-gutters">
					<div class="col-lg-6">
						<form class="checkthisform" method="post" enctype="multipart/form-data" action="">
							<div class="st-1">
								<div class="form-item warning-box">
									<label for="">手機號碼：</label>
									<div class="input-group">
										<input id="mobile" type="tel" name="tel" class="form-control form-control-lg" placeholder="手機號碼" req="Y" value="0988888888">
									</div>
									<span class="alert-text">*請填寫正確格式的手機號碼</span>
								</div>
							</div>

							<div class="st-2">
								<div class="form-item warning-box">
									<label for="">暱稱：</label>
									<input type="text" class="form-control form-control-lg" placeholder="稱謂" req="Y" value="陳平果">
									<span class="alert-text">*此為必填欄位</span>
								</div>
								<div class="form-item warning-box">
									<label for="">姓名：</label><!--家屬-->
									<input type="text" class="form-control form-control-lg" placeholder="姓名" req="Y" value="3841XB">
									<span class="alert-text">*此為必填欄位</span>
								</div>
								
								<div class="row justify-content-center pt-4 pb-5">
									<div class="col-8">
										<button class="submit-btn btn btn-primary btn-block btn-shadow hover-down" type="button">儲存</button>
									</div>
								</div>	

							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="sms-success" data-keyboard="false" tabindex="-1"
			aria-labelledby="staticBackdropLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-body p-0">
						<h4 class="text-center my-5"><i class="las la-check"></i>手機綁定成功！</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- End of Main Container -->

	<!-- Scripts -->
    <?php include_once(dirname(__FILE__) . "/phplibs/front_script.php") ?>
</body>

</html>