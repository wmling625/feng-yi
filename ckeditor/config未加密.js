/**
 * @license Copyright (c) 2003-2018, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function(config) {
    // Define changes to default configuration here. For example:
    config.language = 'zh-tw';

    config.allowedContent = true;
    config.toolbar = [
        ['Source'],
        ['Link', 'Unlink'],
        ['Image', 'Table'],
        ['Bold', 'Italic', 'Underline'],

        ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
        ['TextColor', 'BGColor'],
        [ 'Styles', 'Format', 'Font', 'FontSize' ],
		[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'CopyFormatting', 'RemoveFormat' ],
		[ 'list', 'indent', 'blocks', 'align', 'bidi' ]
     //   ['ckeditor_wiris_formulaEditor', 'ckeditor_wiris_formulaEditorChemistry']
    ];
	config.disallowedContent = 'img{width,height};img[width,height]';
	config.font_names+='新細明體;標楷體;微軟正黑體';
	config.fontSize_defaultLabel = '12px'; 
	
	//如果是後台模式才能上傳檔案
	if( (window.location.href).indexOf("admin/") >= 0 ){
		config.filebrowserBrowseUrl = '../ckfinder/ckfinder_redirection.php';
		config.filebrowserUploadUrl = '../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
		
		config.filebrowserImageBrowseUrl = '../ckfinder/ckfinder_redirection.php?type=Images';
		config.filebrowserImageUploadUrl = '../ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'; //可上傳圖檔		
	}else{
		//如果是前台模式, 通常不允許上傳檔案, 但能上傳圖片; 下面路徑有不一樣~不要自己動 by 明宗
		config.filebrowserImageBrowseUrl = './ckfinder/ckfinder_redirection.php?type=Images';
		config.filebrowserImageUploadUrl = './ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'; //可上傳圖檔			
		
	}

    config.enterMode = CKEDITOR.ENTER_BR;
    config.shiftEnterMode = CKEDITOR.ENTER_P;
};

