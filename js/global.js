document.addEventListener("touchstart", function() {}, false);

setTimeout(function(){
	
	if((location.pathname).indexOf("admin") < 0){
		
		$(document).on('change','[type=file]',function(e){	
			if(e.target.value.length > 0){
				var file_type = e.currentTarget.files[0].type;
				var file_size = round(e.currentTarget.files[0].size / 1024 / 1024,1);
				var max_mb = $(this).attr('max_mb');
				if (typeof max_mb == 'undefined' || max_mb == false) {
					max_mb = 10.0;
				}else{
					max_mb = round(max_mb,1);
				}
				if(file_type.indexOf("image") < 0){
					if(file_size > max_mb){
						showAlert("選取失敗，您選擇的檔案大小為"+file_size+"MB，系統允許上限為"+max_mb+"MB");
						$(this).val("");
					}				
				}				
			}
		});
		
		$("table:not(.no_autoresize)").each(function(i,n){
			if( $(this).parents(".no_autoresize").length == 0  ){
				var me_width = parseFloat($(this).css("width"));
				var parent_width = parseFloat($(this).parent().css("width"));
				if(me_width >= parent_width){
					$(this).wrap( "<div style='-webkit-overflow-scrolling: touch;max-width:100%;overflow-x:auto'></div>" );
				}					
			}
		})		
		

		
		$("img:not(.no_autoresize)").attr("style",function(){
			var this_style = $(this).attr("style");
			if (typeof this_style !== typeof undefined && this_style !== false) {
				var style = (parseFloat(this_style.indexOf("max-width") >= 0) || parseFloat(this_style.indexOf("left") >= 0)) ? this_style : this_style+";max-width:100%;height: auto;";
				return style			
			}else{
				return "max-width:100%;height: auto;";
			}
		})
				
		$("iframe:not(.no_autoresize)").attr("style",function(){
			var this_style = $(this).attr("style");
			if (typeof this_style !== typeof undefined && this_style !== false) {
				var style = (parseFloat(this_style.indexOf("max-width") >= 0) || parseFloat(this_style.indexOf("left") >= 0)) ? this_style : this_style+";max-width:100%;";
				return style			
			}else{
				return "max-width:100%;";
			}
		})
		
		if((location.pathname).indexOf(".php") >= 0){
			$("form").each(function(i,n){
				var method = $(this).attr("method");
				var action = $(this).attr("action");
				if (typeof method == typeof undefined || method.toLowerCase() == "get") {
					if( !$(this).hasClass("gsc-search-box") ){ //google自訂搜尋要排除
						alert("表單禁止使用GET方式傳送任何資料");
						return false;
					}
				}
				if (typeof action == typeof undefined || action.toLowerCase() == "") {
					if( !$(this).hasClass("gsc-search-box") ){ //google自訂搜尋要排除
						alert("表單禁止無action方式");
						return false;
					}
				}			
			})
		}
		
		if( $("form[target=_blank]").length > 0 ){
			alert("表單禁止以另開視窗方式執行");
		}

		var app_arr = [];
		$("a[target=_blank]").each(function(){			
			var temp_onclick = $(this).attr("onclick");
			var temp_href = $(this).attr("href");
			if (typeof $(this).attr("onclick") !== typeof undefined && $(this).attr("onclick") !== false) {
				if($(this).attr("onclick").indexOf("open_window") >= 0 || $(this).attr("onclick").indexOf("papago") >= 0){
					app_arr.push("open_window、papago禁止與_blank同時使用，onclick event");
				}			
			}
			if (typeof $(this).attr("href") !== typeof undefined && $(this).attr("href") !== false) {
				if($(this).attr("href").indexOf("open_window") >= 0 || $(this).attr("href").indexOf("papago") >= 0){
					app_arr.push("open_window、papago禁止與_blank同時使用，href event");
				}			
			}		
		})
		if(app_arr.length > 0){
			app_arr = app_arr.toString();
			alert(app_arr);
		}
	}
},1500);
/*
2022.05.24
自動生效: 副檔名是PHP時, form 一定要有action與method這兩個參數且要有值
* 擴充 dateCollections ，會返回日明細 FullDayList DayList，可用在 [日]下拉選單上
* 重構 pintech_localControl, 使用方式有改變, 但同時解決 ios app localstorage 會遺失問題
* 新增 captcha64_reload，數字驗證碼使用

2022.04.26
新增 pintech_localControl 可用在我的最愛或觀看記錄/紀錄, localstorage

2022.03.21
產生區間隨機數
generateRandomInt(min,max);
更換目前網址參數並變更瀏覽器歷史紀錄，LIFF跟APP會很常用到
add_history(key, value); //add_history("scrolltop", "300");

前台自動限制檔案大小，預設為10MB，如果要手動限制, 可參考如下
<input type="file"> //預設是10MB	
<input type="file" max_mb="2"> 	//指定上限為2MB
	
2022.02.07
table表格會自適應
旋轉 base64 圖片, 可用 base64_Orientation

2021.12.29
新增 date_to_weekJson

/*2021.10.04
擴充 ajax_pub_adv, option可使用emoji符號 {"emoji":true}, 但root_global還是記得用none去接收才能正常使用emoji
擴充 ajax_pub_adv, option可使用aes加密 {"encrypt":"aes"}, 解密使用新版root_global去接收會自動解密
https://docs.google.com/document/d/1mepsgjYF5S3GU_b7hguWfuPfTscfdGKB7gavXmKadSk/edit?usp=sharing

/* 2021.03.29
修正papago與LINE版本異常問題
當img有no_autoresize這class時，就不讓global.js自動最佳化他的寬高，非必要不要用


/* 2021.02.24
新增appversion_check函數，能在cordova內使用，當app version版本號(AndroidManifest.xml versionName)比線上舊時，就更新

/* 2021.02.04
調整 city_related, 輸出的縣市都改為大寫[臺],預設值如果是[台XX]也能支援, 返回callback包含區域中心點座標

/*2020.11.24
擴充 ajax_pub_adv, 能支援async 與 timeout 兩種參數

/*
2020.08.20
新增 createGeoJSONCircle, 地圖上可畫圓
新增 city_related, 縣市行政區選單連動

/*
2020.05.11
台灣統一編號驗證, 修正ajax_pub_adv success相容錯字(RD照常使用即可)
email驗證調整


/* 2020.04.17 調整項目
修正ajax_pub_adv IE BUG

/* 2020.03.31 調整項目
ajax_pub_adv 金鑰產生方式

/* 2019.10.22 調整項目
DateDiff() 異動, 可返回小數
getUrlParam() 異動
ajax_pub_adv() 異動
*/

/*
旋轉base64圖片, 3轉180度 ; 6右轉90度 ; 8左轉90度 ; 教學如下
https://docs.google.com/document/d/1mu54jLPpkxWGuG21CNil2iVunuYEffre_60RsNX43q8/edit?usp=sharing
base64_Orientation(base64_str, 8, function(resetBase64Image) {					
	console.log(resetBase64Image)
});
*/

//數字驗證碼，使用教學如下
//https://docs.google.com/document/d/1FQm1SiGLrAtvZeYJZ5VbqARcd_A_WgIIld7StjQ9w98/edit?usp=sharing
function captcha64_reload(selector,endpoint){
	var event_arr = [];
	//以下success必填, 其餘選填
	event_arr["success"] = function(data){
		if(data.state == "0"){
			$(selector).attr("src",data.data);
		}
		
	}	
	ajax_pub_adv(endpoint, {}, event_arr ,{"async":true,"timeout":8000})	
}

//pintech_localControl 可用在我的最愛或觀看記錄/紀錄, localstorage
//教學 https://docs.google.com/document/d/1CqYc4FBPJlaYNYdor8IDMxzoMn5zJ8_ym04Yzrm9i1I/edit?usp=sharing
function pintech_localControl(item_arr){
	var pintech_favorite = [];
	var checked_temp = [];
	var callback = [];
	var save = 1;
	var cloud_deferredObj = $.Deferred();
	
	callback["state"] = "-1";
	callback["msg"] = "未帶入任何參數";
	if (typeof item_arr !== 'undefined' && typeof item_arr !== null ) {
		
		if(typeof item_arr["model"] !== 'undefined'){
			if(item_arr["model"] == "clearAll"){
				pintech_favorite = [];
				localStorage.clear();
				callback["state"] = "1";
				callback["msg"] = "清空所有資料";	
				cloud_deferredObj.resolve();	
			}else if(item_arr["model"] == "print" || item_arr["model"] == "toggle" || item_arr["model"] == "checked" || item_arr["model"] == "addonly" || item_arr["model"] == "clear"){
				if (typeof item_arr["table"] !== 'undefined'){
					if (localStorage.getItem(item_arr["table"]) !== 'undefined' && localStorage.getItem(item_arr["table"]) !== null ) {					
						try {
						  pintech_favorite = $.parseJSON(localStorage.getItem(item_arr["table"]));
						}
						catch (err) {
						  pintech_favorite = $.parseJSON("[]");
						}		
					}	
							
					if(pintech_favorite.length == 0){
						
						if (typeof device !== typeof undefined && device !== false) {
							if (typeof device.platform !== typeof undefined && device.platform !== false) {
								if(device.platform == "iOS"){
									var event_arr = [];
									event_arr["success"] = function(data){
										if(data.state == "1" || data.state == "0"){	
											try {
											  pintech_favorite = $.parseJSON(data.data);
											}
											catch (err) {
											  pintech_favorite = $.parseJSON("[]");
											}		
											localStorage.setItem(item_arr["table"], JSON.stringify(pintech_favorite));
								
										}else{
											showAlert(data.message);
										}		
										cloud_deferredObj.resolve();
									}						
									ajax_pub_adv("https://key.linebot.info/localcloud_query.php", {"device_id":device.uuid,"name":item_arr["table"]}, event_arr,{"timeout":8000,"emoji":false,"encrypt":"aes"});											
								}else{
									cloud_deferredObj.resolve();
								}
							}else{
								cloud_deferredObj.resolve();
							}		
						}else{
							cloud_deferredObj.resolve();
						}
					}else{
					
						cloud_deferredObj.resolve();
					}
					
					
					cloud_deferredObj.done(function(){

						if(item_arr["model"] == "print"){
							callback["state"] = "1";
							callback["msg"] = "列出所有資料";

							
						}else if(item_arr["model"] == "clear"){
							pintech_favorite = [];
							callback["state"] = "1";
							callback["msg"] = "清空單一資料表";
						}else if(item_arr["model"] == "checked" || item_arr["model"] == "toggle" || item_arr["model"] == "addonly"){
							if (typeof item_arr["table_pk"] !== 'undefined' && typeof item_arr["table_pk"] !== null ) {
								if(item_arr["model"] == "checked"){
									var item_exist = 0;
									
									$.each(pintech_favorite,function(i,n){
										if(n[item_arr["table_pk"]] == item_arr["table_arr"][item_arr["table_pk"]]){
											checked_temp.unshift(n);
											item_exist = 1;
											return false; 
										}					
									})	
									if(item_exist == 0){
										callback["state"] = "0";
										callback["msg"] = "暫存中無此筆資料";	
										save = 0;
									}else{
										callback["state"] = "1";
										callback["msg"] = "暫存中有此筆資料";
										save = 0;
									}	
								}else if(item_arr["model"] == "toggle" || item_arr["model"] == "addonly"){
									var item_exist = 0;
									$.each(pintech_favorite,function(i,n){
										if(n[item_arr["table_pk"]] == item_arr["table_arr"][item_arr["table_pk"]]){
											item_exist = 1;
											pintech_favorite.splice(i,1);
											return false; 
										}					
									})	
									if(item_exist == 0){
										callback["state"] = "1";
										callback["msg"] = "已加入";		
										if(pintech_favorite.length == 0){
											pintech_favorite.unshift(item_arr["table_arr"]);
										}else{
											if(Object.keys(pintech_favorite[0]).sort().join() == Object.keys(item_arr["table_arr"]).sort().join()){
												pintech_favorite.unshift(item_arr["table_arr"]);
											}else{
												callback["state"] = "-1";
												callback["msg"] = "新陣列與舊陣列KEY值不相符";	
											}   
										}
											
									}else{
										callback["state"] = "1";
										callback["msg"] = "已更新";
										
										if(item_arr["model"] == "addonly"){
											
											if(pintech_favorite.length == 0){
												pintech_favorite.unshift(item_arr["table_arr"]);
											}else{
												if(Object.keys(pintech_favorite[0]).sort().join() == Object.keys(item_arr["table_arr"]).sort().join()){
													pintech_favorite.unshift(item_arr["table_arr"]);
												}else{
													callback["state"] = "-1";
													callback["msg"] = "新陣列與舊陣列KEY值不相符";	
												}   
											}
											
										}else{
											callback["msg"] = "已移除";
										}	
									}								
								}
							
							}else{
								callback["msg"] = "缺少table_pk";
							}
						}				
					});						
				}else{
					callback["msg"] = "缺少table";
					cloud_deferredObj.resolve();					
				}
				
			}
			
		}else{
			callback["msg"] = "model異常";
			cloud_deferredObj.resolve();			
		}		
	}else{
		cloud_deferredObj.resolve();
	}
	cloud_deferredObj.done(function(){
		if(save == 1){			
			if(item_arr["model"] == "toggle" || item_arr["model"] == "addonly"){
				var init = 0;
				if(typeof item_arr["table_quota"] !== 'undefined'){
					init = parseInt(item_arr["table_quota"]);
				}	
				if(init > 0){
					pintech_favorite.splice(init, 9999);  
				}	
			}		
			localStorage.setItem(item_arr["table"], JSON.stringify(pintech_favorite));	
			callback["data"] = pintech_favorite;
		}else{ //如果是checked模式就不用寫回
			callback["data"] = checked_temp;
		}
		
		//** 如果不是ios app 都立刻執行, 如果是ios app就等傳完	
		if (typeof device !== typeof undefined && device !== false) {
			
			if (typeof device.platform !== typeof undefined && device.platform !== false) {
				if(device.platform == "iOS"){
					if(item_arr["model"] == "toggle" || item_arr["model"] == "addonly" || item_arr["model"] == "clear"){ 			
						var event_arr = [];
							event_arr["success"] = function(data){
							if(data.state == "0"){									
								item_arr["callback"](callback);			
							}						
						}						
						ajax_pub_adv("https://key.linebot.info/localcloud_add.php", {"device_id":device.uuid,"name":item_arr["table"],"content":JSON.stringify(pintech_favorite)}, event_arr ,{"timeout":8000,"emoji":false,"encrypt":"aes"});	
					}else if(item_arr["model"] == "clearAll"){
						var event_arr = [];
							event_arr["success"] = function(data){
							if(data.state == "0"){	
								item_arr["callback"](callback);				
							}						
						}						
						ajax_pub_adv("https://key.linebot.info/localcloud_clear.php", {"device_id":device.uuid}, event_arr ,{"timeout":8000,"emoji":false,"encrypt":"aes"});				
					}else if(item_arr["model"] == "checked" || item_arr["model"] == "print"){
						item_arr["callback"](callback);					
					}						
								
				}else{
					item_arr["callback"](callback);	
				}
			}		
		}else{
			item_arr["callback"](callback);	
		}
		
					
	})	

	
	
	//return callback
}


function add_history(key, value){
	history.replaceState({}, 0, add_UrlParam(key, value));
}

function add_UrlParam(key, value){
	var obj = new window.URL(window.location.href);
	obj.searchParams.set(key, value);
	return obj.href;
}	

function generateRandomInt(min,max){
    return Math.floor((Math.random() * (max-min)) +min);
}


function base64_Orientation(srcBase64, srcOrientation, callback) {
	var img = new Image();	

	img.onload = function() {
  	var width = img.width,
    		height = img.height,
        canvas = document.createElement('canvas'),
	  		ctx = canvas.getContext("2d");

	if (4 < srcOrientation && srcOrientation < 9) {
		canvas.width = height;
		canvas.height = width;
    } else {
		canvas.width = width;
		canvas.height = height;
    }
	
  	// transform context before drawing image
	switch (srcOrientation) {
      case 2: ctx.transform(-1, 0, 0, 1, width, 0); break;
      case 3: ctx.transform(-1, 0, 0, -1, width, height ); break;
      case 4: ctx.transform(1, 0, 0, -1, 0, height ); break;
      case 5: ctx.transform(0, 1, 1, 0, 0, 0); break;
      case 6: ctx.transform(0, 1, -1, 0, height , 0); break;
      case 7: ctx.transform(0, -1, -1, 0, height , width); break;
      case 8: ctx.transform(0, -1, 1, 0, 0, width); break;
      default: break;
    }
    ctx.drawImage(img, 0, 0);
	callback(canvas.toDataURL());
  };
	img.src = srcBase64;
}


//日期轉星期, 輸入"2016-01-01"格式, 返回 {week_tw: '二', week_num: 2, week_en: 'Tue'}
function date_to_weekJson(date){
    var w = new Date(date).getDay();
	var weekday = [];	
	weekday["tw"] = [];
	weekday["en"] = [];
	weekday["tw"][0]="日";
	weekday["tw"][1]="一";
	weekday["tw"][2]="二";
	weekday["tw"][3]="三";
	weekday["tw"][4]="四";
	weekday["tw"][5]="五";
    weekday["tw"][6]="六";	
	weekday["en"][0]="Sun";
	weekday["en"][1]="Mon";
	weekday["en"][2]="Tue";
	weekday["en"][3]="Wed";
	weekday["en"][4]="Thu";
	weekday["en"][5]="Fri";
    weekday["en"][6]="Sat";	
	return {"week_tw":weekday["tw"][w],"week_num":w,"week_en":weekday["en"][w]};
}
/*
pintech_trim("字串"), 用來取代 $.trim(字串)
var option = [];
option["emoji"] = true; 
var msg = pintech_trim(" 我是字串  ",option); //這樣表示要保留emoji, 去除前後空白
var msg = pintech_trim(" 我是字串  "); //這樣表示要去除emoji + 去除前後空白
*/
function pintech_trim(str,option){
	var emoji = false;
	if(typeof(option) !== "undefined"){
		if(typeof(option["emoji"]) !== "undefined"){
			if(option["emoji"] == true){
				emoji = true;
			}
		}			
	}
	
	if(emoji == false){
		return $.trim(str).replace(/([\uE000-\uF8FF]|\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDDFF]|\uFE0F)/g, '');
	}else{
		return $.trim(str);
	}

}

//教學 https://docs.google.com/document/d/1084SleTva6D9Tt6LiaiqAY9bDTP7PUbKiQnynA4gdyU/edit?usp=sharing
//需安裝此套件 https://github.com/sampart/cordova-plugin-app-version
function appversion_check(arr){
	if (cordova.getAppVersion){				
		if( (device.platform == "Android" && !arr["android_link"] ) ||  (device.platform == "iOS" && !arr["ios_link"])){
			arr["callback"]( {"status":"-1","message":"沒有相對應網址"} );
		}if ( (device.platform == "Android" && arr["android_link"] == "" ) ||  (device.platform == "iOS" && arr["ios_link"] == "") ){
			arr["callback"]( {"status":"-1","message":"相對應網址不能為空"} );
		}else{
			cordova.getAppVersion.getVersionNumber(function (app_version) { 					
				var app_link = (device.platform == "Android") ?	arr["android_link"]:arr["ios_link"]; 				
				var event_arr = [];
				event_arr["success"] = function(data){
					arr["callback"]( data );						
				}	
				ajax_pub_adv("https://key.linebot.info/app_version.php", {"app_version":app_version,"app_link":app_link}, event_arr);
			});						
		}						
	}else{
		arr["callback"]( {"status":"-1","message":"無法辨識cordova.getAppVersion"} );			
	}			
}

//教學 https://docs.google.com/document/d/1P6QCp6hb-PV9EalsYP1AvdLTMusM653X4YhpM7iZ-lU/edit?usp=sharing
function city_related(city_param) {
    var city_arr = [];
	var region_arr = [];
    $.getJSON("https://key.linebot.info/load_file.php?types=city_loc", function (data) {
		
        var str = "<option value=''>請選擇縣市</option>";
        city_arr = data;
        $.each(city_arr, function (entryIndex, entry) {
            str += '<option value=' + entryIndex + '>' + entryIndex + '</option>';
        })
        $('[name='+city_param["city_name"]+']').html(str);
        var defaults = city_param["city_default"];
        if (typeof defaults !== typeof undefined && defaults !== false && defaults !== "") {
			defaults = defaults.replace('台', '臺');
            $('[name='+city_param["city_name"]+']').find("option[value=" + defaults + "]").attr('selected', 'selected').end().change();
        }
        $('[name='+city_param["region_name"]+']').html("<option value='' zipcode=''>請選擇區域</option>");
        $('[name='+city_param["city_name"]+']').change(function () {
            var str = "<option value='' zipcode=''>請選擇區域</option>";
            var city = $(this).children("option:selected").val();
			region_arr = [];
            $.each(city_arr, function (entryIndex, entry) {
                if (entryIndex == city) {
                    $.each(entry, function (secIndex, item) {
                        str += '<option value=' + secIndex + ' zipcode=' + item["zipcode"] + ' lat=' + item["lat"] + ' lng=' + item["lng"] + '  countycode=' + item["countycode"] + ' towncode=' + item["towncode"] + ' >' + secIndex + '</option>';
						region_arr.push([item["zipcode"],secIndex])
                    })
                }
            })
            $('[name='+city_param["region_name"]+']').html(str);
			
            var defaults = city_param["region_default"];
            if (typeof defaults !== typeof undefined && defaults !== false && defaults !== "") {
                $('select[name='+city_param["region_name"]+']').find("option[value=" + defaults + "]").attr('selected', 'selected').end().change();
            }else{
				var center = ["",""];
				var code = ["",""];		
				var zipcode = "";	
				temp_fun(center,zipcode,code);
			}													  
        }).change();
        $('[name='+city_param["region_name"]+']').change(function () {
			
            var zipcode = $(this).children("option:selected").attr("zipcode");
			var countycode = $(this).children("option:selected").attr("countycode");
			var towncode = $(this).children("option:selected").attr("towncode");
			var lat = parseFloat($(this).children("option:selected").attr("lat"));
			var lng = parseFloat($(this).children("option:selected").attr("lng"));
			var center = ["",""];
			var code = ["",""];
			if(zipcode != ""){				
				center = [lat,lng];
				code = [countycode,towncode];
			}
            $("[name="+city_param["zipcode_name"]+"]").val(zipcode);
			temp_fun(center,zipcode,code);
        }).change();


    })
	
	function temp_fun(center,zipcode,code){
		var temp = [];
		temp["city"] = $('[name='+city_param["city_name"]+']').val();
		temp["region"] = [zipcode,$('[name='+city_param["region_name"]+']').val()];
		temp["center"] = center;
		temp["code"] = code;
		temp["region_list"] = region_arr;
		if (typeof city_param["callback"] !== typeof undefined && city_param["callback"] !== false) {
			city_param["callback"](temp);
		}
				
	}
}


/*
var radius = 1.0;
var Circle_arr = createGeoJSONCircle([data["lng"], data["lat"]], radius);
map.addSource("Circle", Circle_arr[0]);
map.addLayer({
    "id": "Circle_id",
    "type": "fill",
    "source": "Circle",
    "paint": {
        "fill-color": "#4472EA",
        "fill-opacity": 0.2
    }
});
map.addSource("Circle_label", Circle_arr[1]);
map.addLayer({
    "id": "Circle_label_id",
    "type": "symbol",
    "source": "Circle_label",
    layout: {
        'text-allow-overlap': true,
        'text-field': '半徑 ' + radius + ' 公里範圍',
        'text-offset': [0, 0],
        'text-size': 15
    },
    paint: {
        'text-color': '#4472EA',
        'text-halo-color': '#fff',
        'text-halo-width': 2
    }
});
返回geojson格式的圓形
*/
function createGeoJSONCircle(center, radiusInKm, points) {
    if(!points) points = 64;

    var coords = {
        latitude: parseFloat(center[1]),
        longitude: parseFloat(center[0])
    };

    var km = radiusInKm;

    var ret = [];
    var distanceX = km/(111.320*Math.cos(coords.latitude*Math.PI/180));
    var distanceY = km/110.574;

    var theta, x, y;
    for(var i=0; i<points; i++) {
        theta = (i/points)*(2*Math.PI);
        x = distanceX*Math.cos(theta);
        y = distanceY*Math.sin(theta);

        ret.push([coords.longitude+x, coords.latitude+y]);
    }
    ret.push(ret[0]);

    return [{
        "type": "geojson",
        "data": {
            "type": "FeatureCollection",
            "features": [{
                "type": "Feature",
                "geometry": {
                    "type": "Polygon",
                    "coordinates": [ret]
                }
            }]
        }
    },{
        "type": "geojson",
        "data": {
            "type": "FeatureCollection",
            "features": [{
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": center
                }
            }]
        }
    }];
};


function company_validation(taxId) {
    var invalidList = "00000000,11111111";
    if (/^\d{8}$/.test(taxId) == false || invalidList.indexOf(taxId) != -1) {
        return false;
    }

    var validateOperator = [1, 2, 1, 2, 1, 2, 4, 1],
        sum = 0,
        calculate = function(product) { // 個位數 + 十位數
            var ones = product % 10,
                tens = (product - ones) / 10;
            return ones + tens;
        };
    for (var i = 0; i < validateOperator.length; i++) {
        sum += calculate(taxId[i] * validateOperator[i]);
    }

    return sum % 10 == 0 || (taxId[6] == "7" && (sum + 1) % 10 == 0);
};

function aes_encrypt(str,key){
  if (typeof(key) == "undefined"){
	  key = "3883136338831363";
  }		
  var key = CryptoJS.enc.Utf8.parse(key);	
  var encryptedData = CryptoJS.AES.encrypt(pintech_trim(str), key, {
    mode: CryptoJS.mode.ECB,
    padding: CryptoJS.pad.Pkcs7
  });
  //encryptedData = encryptedData.toString().replaceAll('+', '-');
  //encryptedData = encryptedData.replaceAll('/', '_');
  //encryptedData = encryptedData.replaceAll('=', '');
  
  encryptedData = encryptedData.toString().replace(/\+/g, '-');
  encryptedData = encryptedData.replace(/\//g, '_');
  encryptedData = encryptedData.replace(/=/g, '');
			
  return encryptedData;
}

function aes_decrypt(str,key){
  if (typeof(key) == "undefined"){
	  key = "3883136338831363";
  }		
  //str = str.replaceAll('_', '/');
  //str = str.replaceAll('-', '+');   
  str = str.replace(/_/g, '/');
  str = str.replace(/-/g, '+');
  var mod = (str.length) % 4;
  if (mod) {
	str += '===='.substr(mod);
  }  
  var key = CryptoJS.enc.Utf8.parse(key);	
  var decryptData = CryptoJS.AES.decrypt(pintech_trim(str), key, {
    mode: CryptoJS.mode.ECB,
    padding: CryptoJS.pad.Pkcs7
  });
   var decryptedStr = decryptData.toString(CryptoJS.enc.Utf8);
   return decryptedStr.toString();
}


function encrypt2json(str){
	if(typeof str === 'object'){
		return str;
	}else{
		if(str.indexOf("pintech.encrypt@") >= 0){
			//str = str.replaceAll('pintech.encrypt@', '');
			str = str.replace(/pintech.encrypt@/g, '');
			str = $.parseJSON(aes_decrypt(str));	
			return str;
		}		
	}
}
/*
async預設為true, 不建議改成false.
var event_arr = [];
//以下success必填, 其餘選填
event_arr["success"] = function(data){
	console.log(data)
}
event_arr["before"] = function(data){
	console.log("AJAX before");
}
event_arr["always"] = function(data){
	console.log("AJAX always");
}
event_arr["fail"] = function(data){		
	console.log(data)
}	
ajax_pub_adv("ping.php", {"name":"品科技","tel":"0972"}, event_arr ,{"async":true,"timeout":8000,"encrypt":"aes","emoji":false})
*/
function ajax_pub_adv(url, data, event_arr, option) {
	if('token' in data || 'value' in data){
		console.log("將使用新的token與value覆蓋原本參數");
	}
	if (typeof(event_arr) == "undefined"){
	  event_arr = []
	}

	if (typeof(option) == "undefined"){
	    option = {"async":true,"timeout":8000} 
	}
	if(typeof(option["async"]) == "undefined"){
		option["async"] = true;
	}
	if(typeof(option["timeout"]) == "undefined"){
		option["timeout"] = 8000;		
	}
	var aes = false;
	if(typeof(option["encrypt"]) !== "undefined"){
		if(option["encrypt"] == "aes"){
			aes = true;
		}
	}
	var emoji = false;
	if(typeof(option["emoji"]) !== "undefined"){
		if(option["emoji"] == true){
			emoji = true;
		}
	}	
	var trim_option = [];
	trim_option["emoji"] = emoji;
	
	var temp = gettoken_value();
	var value = temp.value;
	var token = temp.token;	
	
	
	$.each(data,function(i,n){
		if(aes == false){
			data[i] = pintech_trim(n,trim_option);	
		}else if(aes == true){
			if(pintech_trim(n,trim_option) != ""){
				data[i] = "pintech.encrypt@" + aes_encrypt(pintech_trim(n,trim_option));
			}else{
				data[i] = pintech_trim(n,trim_option);	
			}
		}
		
	})
	
var beforeSend = function(){};
if(typeof(event_arr["before"]) != "undefined" && $.isFunction(event_arr["before"])){
	beforeSend = event_arr["before"];
} 
$.ajax({
    url: "https://key.linebot.info/key_create.php",
    method: 'POST',
    dataType: 'json',
	data:{ value:value,token:token, ref: aes_encrypt(location.href) },
    success: function (data1) {
 		value = data1.value;
		token = data1.token;
		var obj2 = {"value":value,"token":token};
		
		var success = function(){};
		if(typeof(event_arr["success"]) != "undefined" && $.isFunction(event_arr["success"])){
			success = event_arr["success"];
		}else if(typeof(event_arr["scuess"]) != "undefined" && $.isFunction(event_arr["scuess"])){
			success = event_arr["scuess"];
		} 	
		

		var fail = function(data){console.log(data)};
		if(typeof(event_arr["fail"]) != "undefined" && $.isFunction(event_arr["fail"])){
			fail = event_arr["fail"];
		} 
		var always = function(){};
		if(typeof(event_arr["always"]) != "undefined" && $.isFunction(event_arr["always"])){
			always = event_arr["always"];
		} 	
		
		var promise = $.ajax({
				url: url,
				data: $.extend(data, obj2),
				async: option["async"], 
				timeout: option["timeout"],			
				method: "POST",
				dataType: "json"
			});
		promise.done(function(data){
			success(encrypt2json(data))
		});
		promise.fail(fail);
		promise.always(function(data){
			always(encrypt2json(data))
		});	 
    },
    beforeSend: beforeSend
})	

	/*
	$.post( "https://key.linebot.info/key_create.php", { value:value,token:token }, function( data1 ) {
		value = data1.value;
		token = data1.token;
		var obj2 = {"value":value,"token":token};
		
		var success = function(){};
		if(typeof(event_arr["success"]) != "undefined" && $.isFunction(event_arr["success"])){
			success = event_arr["success"];
		}else if(typeof(event_arr["scuess"]) != "undefined" && $.isFunction(event_arr["scuess"])){
			success = event_arr["scuess"];
		} 	
		
		var beforeSend = function(){};
		if(typeof(event_arr["before"]) != "undefined" && $.isFunction(event_arr["before"])){
			beforeSend = event_arr["before"];
		} 
		var fail = function(data){console.log(data)};
		if(typeof(event_arr["fail"]) != "undefined" && $.isFunction(event_arr["fail"])){
			fail = event_arr["fail"];
		} 
		var always = function(){};
		if(typeof(event_arr["always"]) != "undefined" && $.isFunction(event_arr["always"])){
			always = event_arr["always"];
		} 	
		
		var promise = $.ajax({
				url: url,
				data: $.extend(data, obj2),
				async: option["async"], 
				timeout: option["timeout"],			
				method: "POST",
				dataType: "json",
				beforeSend: beforeSend
			});
		promise.done(function(data){
			success(encrypt2json(data))
		});
		promise.fail(fail);
		promise.always(function(data){
			always(encrypt2json(data))
		});	

	}, "json");
	*/


}

/*
getUrlParam() //列出全部參數
getUrlParam("id") //列出id的值
getUrlParam("id","網址") //從網址裡面拉參數
*/
function getUrlParam(name,url) {
  if(typeof(name) != "undefined"){
	  var reg = RegExp ('[?&]' + name.replace (/([[\]])/, '\\$1') + '=([^&#]*)');
	  var r = (url) ? (url.match (reg) || ['', ''])[1] : decodeURI((window.location.href.match (reg) || ['', ''])[1]);
	  return r;	  
  }else{
    var r={};
    location.search.replace(/[?&;]+([^=]+)=([^&;]*)/gi,function(s,k,v){
        r[k] = decodeURI(v)
    })	
    return r;	
  }		
}




//返回日期區間, date_range("2018-09-24", "2018-09-27")
function date_range(startDate, stopDate ) {

var listDate = [];
var dateMove = new Date(startDate);
var strDate = startDate;
while (strDate < stopDate){
  var strDate = dateMove.toISOString().slice(0,10);
  listDate.push(strDate);
  dateMove.setDate(dateMove.getDate()+1);
};
return listDate
}


//驗證身份證字號
function ValidateID(id){
    var city = new Array(1, 10, 19, 28, 37, 46, 55, 64, 39, 73, 82, 2, 11, 20, 48, 29, 38, 47, 56, 65, 74, 83, 21, 3, 12, 30);
    id = id.toUpperCase();
    // 使用「正規表達式」檢驗格式
    if (!id.match(/^[A-Z]\d{9}$/) && !id.match(/^[A-Z][A-D]\d{8}$/)) {
		return false;
    }
    else {
        var total = 0;
        if (id.match(/^[A-Z]\d{9}$/)) { //身分證字號
            //將字串分割為陣列(IE必需這麼做才不會出錯)
            id = id.split('');
            //計算總分
            total = city[id[0].charCodeAt(0) - 65];
            for (var i = 1; i <= 8; i++) {
                total += eval(id[i]) * (9 - i);
            }
        } else { // 外來人口統一證號
            //將字串分割為陣列(IE必需這麼做才不會出錯)
            id = id.split('');
            //計算總分
            total = city[id[0].charCodeAt(0) - 65];
            // 外來人口的第2碼為英文A-D(10~13)，這裡把他轉為區碼並取個位數，之後就可以像一般身分證的計算方式一樣了。
            id[1] = id[1].charCodeAt(0) - 65;
            for (var i = 1; i <= 8; i++) {
                total += eval(id[i]) * (9 - i);
            }
        }
        //補上檢查碼(最後一碼)
        total += eval(id[9]);
        //檢查比對碼(餘數應為0);
        if (total % 10 == 0) {
            return true;
        }
        else {
            return false;
        }
    }
}


//修正:active部分機型有錯誤
$(document).on('click', "input[type=text],input[type=tel],input[type=search],input[type=number],input[type=email],textarea", function (event) {
  var target = this;
  setTimeout(function(){
	if (typeof target.scrollIntoViewIfNeeded !== "undefined"){
		target.scrollIntoViewIfNeeded();
	}     
  },400);
}).on('keyup', "input[type=text],input[type=tel],input[type=search],input[type=number],input[type=email]", function (event) {
  //keydown對android search沒作用	
	if (event.which == 13 && browser_version().mobile) {
		//能避免ios軟鍵盤縮不回去
		document.activeElement.blur();
	}
})


/*
token與value取得方式, P.S 注意ajax_pub已內建
var temp = gettoken_value();
var value = temp.value;
var token = temp.token;	
*/
function gettoken_value(){
	var str = guid();
	return {"value":str,"token":eval(function(p,a,c,k,e,d){e=function(c){return c};if(!''.replace(/^/,String)){while(c--){d[c]=k[c]||c}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('0(0(1))',2,2,'md5|str'.split('|'),0,{}))
	};
}


var Pintech_base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(input){var output="";var chr1,chr2,chr3,enc1,enc2,enc3,enc4;var i=0;input=Pintech_base64._utf8_encode(input);while(i<input.length){chr1=input.charCodeAt(i++);chr2=input.charCodeAt(i++);chr3=input.charCodeAt(i++);enc1=chr1>>2;enc2=((chr1&3)<<4)|(chr2>>4);enc3=((chr2&15)<<2)|(chr3>>6);enc4=chr3&63;if(isNaN(chr2)){enc3=enc4=64;}else if(isNaN(chr3)){enc4=64;}
output=output+
this._keyStr.charAt(enc1)+this._keyStr.charAt(enc2)+
this._keyStr.charAt(enc3)+this._keyStr.charAt(enc4);}
return output;},decode:function(input){var output="";var chr1,chr2,chr3;var enc1,enc2,enc3,enc4;var i=0;input=input.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(i<input.length){enc1=this._keyStr.indexOf(input.charAt(i++));enc2=this._keyStr.indexOf(input.charAt(i++));enc3=this._keyStr.indexOf(input.charAt(i++));enc4=this._keyStr.indexOf(input.charAt(i++));chr1=(enc1<<2)|(enc2>>4);chr2=((enc2&15)<<4)|(enc3>>2);chr3=((enc3&3)<<6)|enc4;output=output+String.fromCharCode(chr1);if(enc3!=64){output=output+String.fromCharCode(chr2);}
if(enc4!=64){output=output+String.fromCharCode(chr3);}}
output=Pintech_base64._utf8_decode(output);return output;},_utf8_encode:function(string){string=string.replace(/\r\n/g,"\n");var utftext="";for(var n=0;n<string.length;n++){var c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c);}
else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128);}
else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128);}}
return utftext;},_utf8_decode:function(utftext){var string="";var i=0;var c=c1=c2=0;while(i<utftext.length){c=utftext.charCodeAt(i);if(c<128){string+=String.fromCharCode(c);i++;}
else if((c>191)&&(c<224)){c2=utftext.charCodeAt(i+1);string+=String.fromCharCode(((c&31)<<6)|(c2&63));i+=2;}
else{c2=utftext.charCodeAt(i+1);c3=utftext.charCodeAt(i+2);string+=String.fromCharCode(((c&15)<<12)|((c2&63)<<6)|(c3&63));i+=3;}}
return string;}}


//HTML 內建 base64編碼
function utf8_to_b64(str) {
    return Pintech_base64.encode(str)
}

//HTML 內建 base64解碼
function b64_to_utf8(str) {
    return Pintech_base64.decode(str)
}




/* 如果上傳圖檔功能是給手機專門用的, 需要特別處理safari旋轉問題
<input type="file" onchange="selectFileImage(this,1024,1024,'textarea[name=file0]','[name=photo2]','mobile',function(){callback()})"  />
selectFileImage(this,最大寬,最大高,'存放base64文字的選擇器','預覽base64縮圖的選擇器','rwd就是保留原本照片/mobile就是刪除原本照片',縮圖後要執行的function)
如果要用這涵數, 必須額外載入exif.js
這函數匯出base64後, 就會把原本的file清除
*/
function selectFileImage(fileObj,max_width,max_height,dom,preview,device,events) {  
	var deferredObj = $.Deferred();
    var file = fileObj.files['0'];  
    //图片方向角 added by lzk  
    var Orientation = null;  
	
      
    if (file) {   
        var rFilter = /^(image\/jpeg|image\/png)$/i; // 检查图片格式  
        if (!rFilter.test(file.type)) {  
			showAlert("請上傳JPG或PNG圖檔");
			return false
        }  
		if (typeof EXIF == "undefined" || typeof MegaPixImage == "undefined"){
			showAlert("請載入新版exif.js");
			return false
		}
	
			
   
          
        var oReader = new FileReader();  
        oReader.onload = function(e) {  
            var image = new Image();  
            image.src = e.target.result;  
            image.onload = function() {  
                var expectWidth = this.naturalWidth;  
                var expectHeight = this.naturalHeight;  
               
                if (this.naturalWidth > this.naturalHeight && this.naturalWidth > max_width) {
                    expectWidth = max_width;  
                    expectHeight = expectWidth * this.naturalHeight / this.naturalWidth;  
                } else if (this.naturalHeight > this.naturalWidth && this.naturalHeight > max_height) {
                    expectHeight = max_height;  
                    expectWidth = expectHeight * this.naturalWidth / this.naturalHeight;  
                }  else{ //如果傳上來的是正方形
                    expectHeight = max_height;  
                    expectWidth = expectHeight * this.naturalWidth / this.naturalHeight;  					
				}
				
                var canvas = document.createElement("canvas");  
                var ctx = canvas.getContext("2d");  
                canvas.width = expectWidth;  
                canvas.height = expectHeight;  
                ctx.drawImage(this, 0, 0, expectWidth, expectHeight);  
    
                  
                var base64 = null;  
			
				if(browser_version().android || browser_version().ios){
					var mpImg = new MegaPixImage(image);
					EXIF.getData(file, function() {  
						mpImg.render(canvas, {  						
							maxWidth: max_width,  
							maxHeight: max_height,  
							quality: 0.92,  
							orientation: EXIF.getTag(this, 'Orientation')
						});					
						//EXIF.getAllTags(this);    
						//Orientation = EXIF.getTag(this, 'Orientation'); 
					});
                }      
                base64 = canvas.toDataURL("image/jpeg", 0.92); 
				$(dom).val(base64);
				deferredObj.resolve();
				
                if(typeof events == "function"){					
					deferredObj.done(function(){
						events();
					});
				}				
				if(preview){
					if($(preview)[0].tagName == "IMG")
						$(preview).attr("src",base64);
					else
						$(preview).css({"background-image":"url("+base64+")"});
				}
            };  
        };  
        oReader.readAsDataURL(file);
		if(device == "mobile"){
			$(fileObj).val("");
		}
				
    }  
}  

//有的XML或JSON在串接時, 來源帶有編碼, 可以用這樣轉回來
function htmlDecodeByRegExp(str){  
    var s = "";
    if(str.length == 0) return "";
    s = str.replace(/&amp;/g,"&");
    s = s.replace(/&lt;/g,"<");
    s = s.replace(/&gt;/g,">");
    s = s.replace(/&nbsp;/g," ");
    s = s.replace(/&#39;/g,"\'");
    s = s.replace(/&quot;/g,"\"");
    return s;  
}

/*
if(browser_version().line || browser_version().fbapp || browser_version().weixin ){
	if(browser_version().line){
		alert("您正使用LINE瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}else if(browser_version().fbapp){
		alert("您正使用FB瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}else if(browser_version().fbapp){
		alert("您正使用微信瀏覽器，建議使用系統內建瀏覽器。否則有些功能無法正常使用");
	}
}
*/
function browser_version(){
    var u = navigator.userAgent, app = navigator.appVersion;
    var ua = navigator.userAgent.toLowerCase();

    return { //偵測移動端瀏覽器版本信息
        trident: u.indexOf('Trident') > -1, //IE 核心
        presto: u.indexOf('Presto') > -1, //opera 核心
        webKit: u.indexOf('AppleWebKit') > -1, //Apple, google 核心
        gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //Firefox 核心
        mobile: !!u.match(/AppleWebKit.*Mobile.*/), //行動裝置
        ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios
        android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android或uc瀏覽器
        iPhone: u.indexOf('iPhone') > -1, //是否為iPhone或者QQHD瀏覽器
        iPad: u.indexOf('iPad') > -1, //是否iPad
        webApp: u.indexOf('Safari') == -1, //是否web应该程序，没有头部与底部
        iosv: u.substr(u.indexOf('iPhone OS') + 9, 3),//ios版本
        weixin: ua.match(/MicroMessenger/i) == "micromessenger",//微信瀏覽器
        fbapp: u.indexOf('FBAV') > -1,//Facebook App內瀏覽器
        line: u.indexOf('Line') > -1,//Line內瀏覽器
		chrome: u.indexOf('Chrome') > -1//chrome瀏覽器
    };
}


//密碼複雜度,至少有英數，至少8碼
function ValidatePasswd(str){
    var re = /^(?=.*[a-z])(?=.*\d).{8,}$/;
    return re.test(str);
}

//個資法, 如果有傳X，代表前後各要保留幾位數
function substr_cut(str,x) { 
	var x = (x) ? x : round(str.length/3,0);
	var len = str.length-x-x;
	var xing = '';
	for (var i=0;i<len;i++) {
	xing+='*';
	}
	return (str.length == 2) ? str.substring(0,1)+"*":str.substring(0,x)+xing+str.substring(str.length-x);
}

//新增項目至下拉選單
//addOption("pc_select","pc","電腦")
function addOption(selectID,value,text) {
    var obj = $("#" + selectID + "");
    $("<option></option>").val(value).text(text).appendTo(obj);
}

//參數可以是空的(預設今天)，或者指定日期如2016-06-06。會返回本週日,本週六,本月首日,本月末日,上月首日
function dateCollections(date){
	var arr =(date) ? date.split("-") : [];
	var nowDate = (date) ? new Date(arr[0],arr[1]-1,arr[2]) : new Date();
    var nowDay = nowDate.getDay(); 
    nowDay = nowDay === 0 ? 7 : nowDay;
	var timestampOfDay = 1000*60*60*24;
    var fullYear = nowDate.getFullYear();
    var month = nowDate.getMonth();
    var date = nowDate.getDate();
    var endOfMonth = new Date(fullYear, month+1, 0).getDate(); 

	var this_Sunday = (nowDay == 7) ? getFullDate(nowDate) : getFullDate( +nowDate - (nowDay)*timestampOfDay ); //本週日
	var this_Saturday = GetDateStr(+6,this_Sunday); //本週六
	var StartOfMonth = getFullDate( nowDate.setDate(1) );
	var EndOfMonth = getFullDate( nowDate.setDate(endOfMonth) );
	
    if(month==0){
        month = 12;
        fullYear = fullYear - 1;
    }else if (month < 10) {
        month = "0" + month;
    }	
	var pre_StartOfMonth = fullYear + "-" + month + "-" + "01";//上個月第一天
	//上個月最後一天：本月最後天(上個月第一天)

	
	var FullDayList = date_range(StartOfMonth, EndOfMonth);

	
	var Start_day = parseInt(getISODateTime(StartOfMonth,"dd"));
	var End_day = parseInt(getISODateTime(EndOfMonth,"dd"));
	var DayList = [];
    for (var i = Start_day ; i <= End_day; i++) {
		i = (i < 10) ? "0"+i : i;
		DayList.push(i.toString());
    }	

	
	//var Fullday_list = date_range(StartOfMonth, EndOfMonth);
    return { this_Sunday: this_Sunday, this_Saturday: this_Saturday, StartOfMonth: StartOfMonth,EndOfMonth: EndOfMonth,pre_StartOfMonth:pre_StartOfMonth,FullDayList:FullDayList,DayList:DayList};
}

//N天後或N天前, GetDateStr(+-5) 或 特定日期 GetDateStr(+-5,"2016-05-05");
function GetDateStr(AddDayCount,date) {
	var arr =(date) ? date.split("-") : [];
    var dd = (date) ? new Date(arr[0],arr[1]-1,arr[2]) : new Date();
    dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
	//console.log(datetime_to_unix("2016-01-01 12:00:00").getDate())
    var y = dd.getFullYear();

    var m = (dd.getMonth()+1 < 10) ? "0"+(dd.getMonth()+1) : dd.getMonth()+1 ;
    var d = (dd.getDate() < 10) ? "0"+dd.getDate() : dd.getDate() ;
    return y+"-"+m+"-"+d;
}

//取得上一頁網址，在cordova可使用
function previouspage(){
	var previouspage = ""; 
	if (localStorage.getItem("page") !== 'undefined' && localStorage.getItem("page") !== null ) {
		previouspage = localStorage.getItem("page");
	}
	var currentPage = getfilename()+getfilename("urlParam");
	localStorage.setItem('page', currentPage);
	return previouspage;
}

//時間差 DateDiff("2015-01-01 18:00:00","2015-01-01 20:55:00", "h");
//第二個參數如果小於第一個參數, 返回負值; 第二個參數如果大於第一個參數, 返回正值
function DateDiff(startTime, endTime, diffType) {

    startTime = startTime.replace(/\-/g, "/");
    endTime = endTime.replace(/\-/g, "/");
 

    diffType = diffType.toLowerCase();
    var sTime = new Date(startTime); 
    var eTime = new Date(endTime); 
    var divNum = 1;
    switch (diffType) {
        case "s":
            divNum = 1000;
            break;
        case "i":
            divNum = 1000 * 60;
            break;
        case "h":
            divNum = 1000 * 3600;
            break;
        case "d":
            divNum = 1000 * 3600 * 24;
            break;
        default:
            break;
    }
    return Number((eTime.getTime() - sTime.getTime()) / parseInt(divNum)).toFixed(2);
}


//預設勾選方塊, box_default(要比對的字串,checkbox的name,間格符號);
//box_default("A,B,C","city","@")
function box_default(str,box_dom,icon){
	var icon = (icon) ? icon : "," ; 
	if(typeof str !== "undefined"){
		var str_arr = str.split(icon)
		$("input[type=checkbox][name="+box_dom+"]").each(function(){
			if(str_arr.indexOf($(this).val()) >= 0){
				$(this).prop('checked', true);	
			}	
		})
	}
}	
		

//dist=1為10KM，給經緯度與角度(0-360)後，推算新地點
//如果原地標沒有角度資訊，heading填90新地點會在正右方，heading填180新地點會在正下方
//如果原地標有角度資訊，代表視角是面對原地標，heading可填入Math.abs(角度-180)，這樣剛好就是對面原地標往後退
function getlocationforheading(lat,lng,heading, dist) {
   dist = dist / 6371;  
   heading = radians(heading);  

   var lat1 = radians(lat), lon1 = radians(lng);

   var lat2 = Math.asin(Math.sin(lat1) * Math.cos(dist) + 
                        Math.cos(lat1) * Math.sin(dist) * Math.cos(heading));

   var lon2 = lon1 + Math.atan2(Math.sin(heading) * Math.sin(dist) *
                                Math.cos(lat1), 
                                Math.cos(dist) - Math.sin(lat1) *
                                Math.sin(lat2));

   if (isNaN(lat2) || isNaN(lon2)) return null;
   return { lat:  degrees(lat2), lng: degrees(lon2) }
}

//兩點之間的方向，可以做離線指針定位
function getBearing(startLat,startLong,endLat,endLong){
  startLat = radians(startLat);
  startLong = radians(startLong);
  endLat = radians(endLat);
  endLong = radians(endLong);

  var dLong = endLong - startLong;

  var dPhi = Math.log(Math.tan(endLat/2.0+Math.PI/4.0)/Math.tan(startLat/2.0+Math.PI/4.0));
  if (Math.abs(dLong) > Math.PI){
    if (dLong > 0.0)
       dLong = -(2.0 * Math.PI - dLong);
    else
       dLong = (2.0 * Math.PI + dLong);
  }

  return (degrees(Math.atan2(dLong, dPhi)) + 360.0) % 360.0;
}

function radians(n) {
  return n * (Math.PI / 180);
}
function degrees(n) {
  return n * (180 / Math.PI);
}



//取的目前檔案名稱或所有參數
//檔名 getfilename() -> 123.html
//參數列 getfilename("urlParam") -> ?name=999
function getfilename(x){
	var page ;
	if(!x){
		var path = window.location.pathname;
		page = path.split("/").pop();	
	}else if(x == "urlParam"){
		page = decodeURI(document.location.search);
	}
	return page;
}


//取得md5
function md5(str){
	(function($){'use strict';function safe_add(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF),msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF);}
	function bit_rol(num,cnt){return(num<<cnt)|(num>>>(32-cnt));}
	function md5_cmn(q,a,b,x,s,t){return safe_add(bit_rol(safe_add(safe_add(a,q),safe_add(x,t)),s),b);}
	function md5_ff(a,b,c,d,x,s,t){return md5_cmn((b&c)|((~b)&d),a,b,x,s,t);}
	function md5_gg(a,b,c,d,x,s,t){return md5_cmn((b&d)|(c&(~d)),a,b,x,s,t);}
	function md5_hh(a,b,c,d,x,s,t){return md5_cmn(b^c^d,a,b,x,s,t);}
	function md5_ii(a,b,c,d,x,s,t){return md5_cmn(c^(b|(~d)),a,b,x,s,t);}
	function binl_md5(x,len){x[len>>5]|=0x80<<((len)%32);x[(((len+64)>>>9)<<4)+14]=len;var i,olda,oldb,oldc,oldd,a=1732584193,b=-271733879,c=-1732584194,d=271733878;for(i=0;i<x.length;i+=16){olda=a;oldb=b;oldc=c;oldd=d;a=md5_ff(a,b,c,d,x[i],7,-680876936);d=md5_ff(d,a,b,c,x[i+1],12,-389564586);c=md5_ff(c,d,a,b,x[i+2],17,606105819);b=md5_ff(b,c,d,a,x[i+3],22,-1044525330);a=md5_ff(a,b,c,d,x[i+4],7,-176418897);d=md5_ff(d,a,b,c,x[i+5],12,1200080426);c=md5_ff(c,d,a,b,x[i+6],17,-1473231341);b=md5_ff(b,c,d,a,x[i+7],22,-45705983);a=md5_ff(a,b,c,d,x[i+8],7,1770035416);d=md5_ff(d,a,b,c,x[i+9],12,-1958414417);c=md5_ff(c,d,a,b,x[i+10],17,-42063);b=md5_ff(b,c,d,a,x[i+11],22,-1990404162);a=md5_ff(a,b,c,d,x[i+12],7,1804603682);d=md5_ff(d,a,b,c,x[i+13],12,-40341101);c=md5_ff(c,d,a,b,x[i+14],17,-1502002290);b=md5_ff(b,c,d,a,x[i+15],22,1236535329);a=md5_gg(a,b,c,d,x[i+1],5,-165796510);d=md5_gg(d,a,b,c,x[i+6],9,-1069501632);c=md5_gg(c,d,a,b,x[i+11],14,643717713);b=md5_gg(b,c,d,a,x[i],20,-373897302);a=md5_gg(a,b,c,d,x[i+5],5,-701558691);d=md5_gg(d,a,b,c,x[i+10],9,38016083);c=md5_gg(c,d,a,b,x[i+15],14,-660478335);b=md5_gg(b,c,d,a,x[i+4],20,-405537848);a=md5_gg(a,b,c,d,x[i+9],5,568446438);d=md5_gg(d,a,b,c,x[i+14],9,-1019803690);c=md5_gg(c,d,a,b,x[i+3],14,-187363961);b=md5_gg(b,c,d,a,x[i+8],20,1163531501);a=md5_gg(a,b,c,d,x[i+13],5,-1444681467);d=md5_gg(d,a,b,c,x[i+2],9,-51403784);c=md5_gg(c,d,a,b,x[i+7],14,1735328473);b=md5_gg(b,c,d,a,x[i+12],20,-1926607734);a=md5_hh(a,b,c,d,x[i+5],4,-378558);d=md5_hh(d,a,b,c,x[i+8],11,-2022574463);c=md5_hh(c,d,a,b,x[i+11],16,1839030562);b=md5_hh(b,c,d,a,x[i+14],23,-35309556);a=md5_hh(a,b,c,d,x[i+1],4,-1530992060);d=md5_hh(d,a,b,c,x[i+4],11,1272893353);c=md5_hh(c,d,a,b,x[i+7],16,-155497632);b=md5_hh(b,c,d,a,x[i+10],23,-1094730640);a=md5_hh(a,b,c,d,x[i+13],4,681279174);d=md5_hh(d,a,b,c,x[i],11,-358537222);c=md5_hh(c,d,a,b,x[i+3],16,-722521979);b=md5_hh(b,c,d,a,x[i+6],23,76029189);a=md5_hh(a,b,c,d,x[i+9],4,-640364487);d=md5_hh(d,a,b,c,x[i+12],11,-421815835);c=md5_hh(c,d,a,b,x[i+15],16,530742520);b=md5_hh(b,c,d,a,x[i+2],23,-995338651);a=md5_ii(a,b,c,d,x[i],6,-198630844);d=md5_ii(d,a,b,c,x[i+7],10,1126891415);c=md5_ii(c,d,a,b,x[i+14],15,-1416354905);b=md5_ii(b,c,d,a,x[i+5],21,-57434055);a=md5_ii(a,b,c,d,x[i+12],6,1700485571);d=md5_ii(d,a,b,c,x[i+3],10,-1894986606);c=md5_ii(c,d,a,b,x[i+10],15,-1051523);b=md5_ii(b,c,d,a,x[i+1],21,-2054922799);a=md5_ii(a,b,c,d,x[i+8],6,1873313359);d=md5_ii(d,a,b,c,x[i+15],10,-30611744);c=md5_ii(c,d,a,b,x[i+6],15,-1560198380);b=md5_ii(b,c,d,a,x[i+13],21,1309151649);a=md5_ii(a,b,c,d,x[i+4],6,-145523070);d=md5_ii(d,a,b,c,x[i+11],10,-1120210379);c=md5_ii(c,d,a,b,x[i+2],15,718787259);b=md5_ii(b,c,d,a,x[i+9],21,-343485551);a=safe_add(a,olda);b=safe_add(b,oldb);c=safe_add(c,oldc);d=safe_add(d,oldd);}
	return[a,b,c,d];}
	function binl2rstr(input){var i,output='';for(i=0;i<input.length*32;i+=8){output+=String.fromCharCode((input[i>>5]>>>(i%32))&0xFF);}
	return output;}
	function rstr2binl(input){var i,output=[];output[(input.length>>2)-1]=undefined;for(i=0;i<output.length;i+=1){output[i]=0;}
	for(i=0;i<input.length*8;i+=8){output[i>>5]|=(input.charCodeAt(i/8)&0xFF)<<(i%32);}
	return output;}
	function rstr_md5(s){return binl2rstr(binl_md5(rstr2binl(s),s.length*8));}
	function rstr_hmac_md5(key,data){var i,bkey=rstr2binl(key),ipad=[],opad=[],hash;ipad[15]=opad[15]=undefined;if(bkey.length>16){bkey=binl_md5(bkey,key.length*8);}
	for(i=0;i<16;i+=1){ipad[i]=bkey[i]^0x36363636;opad[i]=bkey[i]^0x5C5C5C5C;}
	hash=binl_md5(ipad.concat(rstr2binl(data)),512+data.length*8);return binl2rstr(binl_md5(opad.concat(hash),512+128));}
	function rstr2hex(input){var hex_tab='0123456789abcdef',output='',x,i;for(i=0;i<input.length;i+=1){x=input.charCodeAt(i);output+=hex_tab.charAt((x>>>4)&0x0F)+
	hex_tab.charAt(x&0x0F);}
	return output;}
	function str2rstr_utf8(input){return unescape(encodeURIComponent(input));}
	function raw_md5(s){return rstr_md5(str2rstr_utf8(s));}
	function hex_md5(s){return rstr2hex(raw_md5(s));}
	function raw_hmac_md5(k,d){return rstr_hmac_md5(str2rstr_utf8(k),str2rstr_utf8(d));}
	function hex_hmac_md5(k,d){return rstr2hex(raw_hmac_md5(k,d));}
	jQuery.md5=function(string,key,raw){if(!key){if(!raw){return hex_md5(string);}else{return raw_md5(string);}}
	if(!raw){return hex_hmac_md5(key,string);}else{return raw_hmac_md5(key,string);}};}(typeof jQuery==='function'?jQuery:this));return jQuery.md5(str);
}


//去除json鎮列重複,以某節點為依據, 用法如下
// uniqueByKey($.parseJSON(total_item), "name")
function uniqueByKey(array, key) {
	var categories = [];	
	$.each(array, function(index, value) {	
		if ($.inArray(value[key], categories) === -1) {
			categories.push(value[key]);
		}
	});
	return categories;
}

//碼錶功能，將秒轉為時分秒
function secondsToTime(secs){
	
    var hours = Math.floor(secs / (60 * 60));
   
    var divisor_for_minutes = secs % (60 * 60);
    var minutes = Math.floor(divisor_for_minutes / 60);
 
    var divisor_for_seconds = divisor_for_minutes % 60;
    var seconds = Math.ceil(divisor_for_seconds);
   
    var obj = {
        "h": (hours < 10) ? "0"+hours : hours,
        "m": (minutes < 10) ? "0"+minutes : minutes,
        "s": (seconds < 10) ? "0"+seconds : seconds
    };
    return (secs <= 0) ? {"h":"00","m":"00","s":"00"} :obj;
}

//產生GUUID
function guid() {
  function s4() {
    return Math.floor((1 + Math.random()) * 0x10000)
      .toString(16)
      .substring(1);
  }
  return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
    s4() + '-' + s4() + s4() + s4();
}

//清除ckeditor內的dom style，用法: remove_ckeditor_style('textareaDefault1','a,p,img,div');
function remove_ckeditor_style(id,tag){
	var ckeditor_text = CKEDITOR.instances[id].getData();
	var dom = $("<div>").append($.parseHTML(ckeditor_text));
	dom.find(tag).removeAttr('style');
	dom = dom.html();
	CKEDITOR.instances[id].setData(dom) 	
}

/*
表單驗證方式
if(validateEmail(參數) == false)
event.preventDefault();	
*/	
//驗證EMAIL
function validateEmail(email) {
    //var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email.toLowerCase());
}
//驗證數字，如果有傳floats這參數，代表浮點數也可以過
function ValidateNumber(pnumber,floats){
    var re = (floats) ? /^[+\-]?\d+(.\d+)?$/ : /^\d+$/;
    return re.test(pnumber);
}

//驗證手機格式
function ValidateMobile(pnumber){
    var re = /^[09]{2}[0-9]{8}$/;
    return re.test(pnumber);
}

//驗證日期
function ValidateYYYYMMDD(str) {
	//var date_regex = /^(0[1-9]|1[0-2])\/(0[1-9]|1\d|2\d|3[01])\/(19|20)\d{2}$/ ;
	var date_regex = /^(19|20)\d{2}\-(0[1-9]|1[0-2])\-(0[1-9]|1\d|2\d|3[01])$/ ;
	//if(!(date_regex.test(str)))
	return (date_regex.test(str))
	//else
	//return true;
}

//物件，從value反找key
// var max_types = {"A": "小明", "B": "小強", "C": "小華"};
// max_types.getKeyByValue("小明"); // 傳回A
/* 暫停使用，在深度後台會有衝突
Object.prototype.getKeyByValue = function( value ) {
	for( var prop in this ) {
		if( this.hasOwnProperty( prop ) ) {
			if( this[ prop ] === value )
				return prop;
			}
		}
}	
*/
					
//計算ibeacon距離，txPower要問廠商，通常是-59，rssi也帶負值即可
function distance_ibeacon(txPower, rssi) { 	
    var ratio = rssi*1.0/txPower;
	if (ratio < 1.0) {
		return Math.pow(ratio,10);
	}
	else {
		var accuracy =  (0.89976)*Math.pow(ratio,7.7095) + 0.111;    
		return accuracy;
	}	
}
	

function showAlert(message,title,submits) {
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確認";
    if (navigator.notification && navigator.notification.alert)
    {
        navigator.notification.alert(
            message,
            function() {
            },
            title,
            submits);
    }
    else
    {
		window.alert(message);
    }
}



//去除特殊字元,連到google map app的title要處理
function stripscript(s){ 
	var pattern = new RegExp("[`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）——|{}【】‘；：”“'。，、？]") 
	var rs = ""; 
	for (var i = 0; i < s.length; i++) { 
		rs = rs+s.substr(i, 1).replace(pattern, ''); 
	} 
	return rs; 
}
function escape_new(str){  
    return escape(str).replace(/\+/g,'%2B').replace(/\#/g,'%23').replace(/\&/g,'%26');  
}  
// getTime() 方法可返回距 1970 年 1 月 1 日之間的毫秒數
// getFullDate( (new Date()).getTime() ); //不常用到 
function getFullDate(targetDate) {
    var D, y, m, d;
    if (targetDate) {
        D = new Date(targetDate);
        y = D.getFullYear();
        m = D.getMonth() + 1;
        d = D.getDate();
    } else {
        y = fullYear;
        m = month;
        d = date;
    }
    m = m > 9 ? m : '0' + m;
    d = d > 9 ? d : '0' + d;

    return y + '-' + m + '-' + d;
}
	
//unix轉日期中文
function unix_to_datetime(unix) {
    var now = new Date(parseInt(unix) * 1000);
    return now.toLocaleString().replace(/年|月/g, "-").replace(/日/g, " ");
}

//日期轉星期, 輸入"2016-01-01"格式
function date_to_week(date){
    var w = new Date(date).getDay();
	var weekday = new Array(7);
	weekday[0]="日";
	weekday[1]="一";
	weekday[2]="二";
	weekday[3]="三";
	weekday[4]="四";
	weekday[5]="五";
    weekday[6]="六";	
	return weekday[w];
}

//格式化日期 TO unix，輸入格式2014-06-09 18:00:00
function datetime_to_unix(datetime){
    var tmp_datetime = datetime.replace(/:/g,'-');
    tmp_datetime = tmp_datetime.replace(/ /g,'-');
    var arr = tmp_datetime.split("-");
    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
    return parseInt(now.getTime()/1000);
}

//var d = new Date("October 13, 2014 11:13:00");
//var d = new Date();
//var n = d.getTime();
//console.log(now_time(d))
//返回yyyy-MM-dd HH:mm:ss，也可以傳入new Date()格式，如果不傳入就是今天時間
function now_time(d){
	Number.prototype.padLeft = function(base,chr){
	   var  len = (String(base || 10).length - String(this).length)+1;
	   return len > 0? new Array(len).join(chr || '0')+this : this;
	}
  var d = (d) ? d : new Date();
  dformat = [ d.getFullYear(),(d.getMonth()+1).padLeft(),d.getDate().padLeft()].join('-')+' ' +[ d.getHours().padLeft(),d.getMinutes().padLeft(),d.getSeconds().padLeft()].join(':');	
  return dformat;
}


//使用方式 getISODateTime("這邊要輸入yyyy-MM-dd HH:mm:ss完整格式", "yyyy-MM-dd")
function getISODateTime(date, format){
	if(date.split(" ").length == 1){ 
		var tmp_datetime = date.replace(/:/g,'-');
		tmp_datetime = tmp_datetime.replace(/ /g,'-');
		var arr = tmp_datetime.split("-");
		date = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],00,00,00));		
	}	

    if (!date) return;
    if (!format) format = "yyyy-MM-dd";
    switch(typeof date) {		
        case "string":			
            date = new Date(date.replace(/\-/g, "/"));
            break;
        case "number":
            date = new Date(date);
            break;
    } 
	
    if (!date instanceof Date) return;
    var dict = {
        "yyyy": date.getFullYear(),
        "M": date.getMonth() + 1,
        "d": date.getDate(),
        "H": date.getHours(),
        "m": date.getMinutes(),
        "s": date.getSeconds(),
        "MM": ("" + (date.getMonth() + 101)).substr(1),
        "dd": ("" + (date.getDate() + 100)).substr(1),
        "HH": ("" + (date.getHours() + 100)).substr(1),
        "mm": ("" + (date.getMinutes() + 100)).substr(1),
        "ss": ("" + (date.getSeconds() + 100)).substr(1)
    };    
	
    return format.replace(/(yyyy|MM?|dd?|HH?|ss?|mm?)/g, function() {
        return dict[arguments[0]];
    }); 
}
/*
第三個參數[選填] 標題. 可為空值
第四個參數[選填] d開車/r採用大眾運輸/w步行方式/b騎行方式

如果是經緯度有3種用法
papago(24.279617,120.621191);
papago(24.279617,120.621191,"顯示標題");
papago(24.279617,120.621191,"顯示標題","d");
如果是地址有2種用法
papago(地址);
papago(地址,"d")
*/

function papago(var1,var2,var3,var4){
	if (typeof(var1) !== "undefined"){
		var1 = $.trim(var1);
	}
	if (typeof(var2) !== "undefined"){
		var2 = $.trim(var2);
	}
	if (typeof(var3) !== "undefined"){
		var3 = $.trim(var3);
	}
	if (typeof(var4) !== "undefined"){
		var4 = $.trim(var4);
	}	
	var regex = /^[0-9.]+$/;
	var mode = var4 || "";
	var temp1 = (mode == "") ? "q":"daddr";
	var app = document.URL.indexOf( 'http://' ) === -1 && document.URL.indexOf( 'https://' ) === -1;
	var label = var3 || var1+","+var2;
	var map = "https://maps.google.com/maps?"+temp1+"="+var1+","+var2+"("+label+")&dirflg="+mode+"&openExternalBrowser=1";
	if(!var2){
		map = "https://maps.google.com/maps?q="+var1+"&openExternalBrowser=1";
	}else if(!regex.test(var1) && !regex.test(var2)){
		map = "https://maps.google.com/maps?daddr="+var1+"&dirflg="+var2+"&openExternalBrowser=1";
	}

	
	if (app){	
		if(device.platform == "Android" && mode == ""){
			if(!var2){
				map = "geo:0,0?q="+var1;
			}else{
				map = "geo:0,0?q="+var1+","+var2+"("+label+")";
			}
		}else if(device.platform == "Android" && mode != ""){
			if(!regex.test(var1) && !regex.test(var2)){
				map = "google.navigation:q="+var1+"&mode="+mode;
			}else{
				map = "google.navigation:q="+var1+","+var2+"&mode="+mode;
			}		
		}
		window.open = cordova.InAppBrowser.open;
		var ref = window.open(encodeURI(map), '_system', 'location=yes');	
		
	}else{
		if (typeof liff == "undefined"){
			
			window.open(encodeURI(map))
		}else{				
			
			try {
				liff.openWindow({url: encodeURI(map),external: false});			
			} catch (err) {
				/*
				liff.init({
				  liffId: '1655675449-48pr7Qzk'
				}).then(function() {			
					liff.openWindow({url: encodeURI(map),external: false});				  
				}).catch(function(error) {
				  alert(error)
				});	
				*/
				document.location.href = encodeURI(map);

			}
			
		
		}	
	}
}
//只要有傳入none_bar，一定網址列
function open_window(url,method,none_bar,exitcallback,startcallback,stopcallback,hardwareback){
	//hardwareback效果在外連結才有效果, no表示按實體鍵時直接關閉瀏覽器, yes表示在瀏覽器內上一步
	var app = document.URL.indexOf( 'http://' ) === -1 && document.URL.indexOf( 'https://' ) === -1;
	url = decodeURI(url);
	if (app){
		if (navigator.notification){
			if(!method)	
			var method = (/(http)/.test(url)) ? (device.platform == "Android") ? '_system':'_blank' :'_blank'
			var url = (/(http)/.test(url)) ? url : (device.platform == "Android") ? 'file:///android_asset/www/'+url : ''+url
			var hardwareback = (hardwareback) ? hardwareback : "no";
			var option = (none_bar) ? "location=no,toolbar=yes,enableViewportScale=yes,transitionstyle=crossdissolve,hardwareback="+hardwareback+",clearcache=yes,zoom=no" : (/(http)/.test(url)) ? "location=yes,enableViewportScale=yes,hardwareback="+hardwareback+",clearcache=yes" : "location=no,toolbar=yes,enableViewportScale=yes,transitionstyle=crossdissolve,hardwareback="+hardwareback+",clearcache=yes,zoom=no"
			var ref = cordova.InAppBrowser.open(encodeURI(url), method, option);			
			if (exitcallback){
				ref.addEventListener('exit', function(event) { 
					exitcallback();
				});
			}
			if(startcallback){
				ref.addEventListener('loadstart', function(event) { 
					startcallback();
				});			
			}
			if(stopcallback){
				ref.addEventListener('loadstop', function(event) { 
					stopcallback();
				});			
			}
		}else{
			var url = (url.indexOf("?") >= 0) ? url+"&openExternalBrowser=1" : url+"?openExternalBrowser=1";
			window.open(encodeURI(url))
		}	
	}else{
		var url = (url.indexOf("?") >= 0) ? url+"&openExternalBrowser=1" : url+"?openExternalBrowser=1";
		
		if (typeof liff == "undefined"){			
			window.open(encodeURI(url))
		}else{
			liff.openWindow({url: encodeURI(url),external: true});
		}	
	}	
	
}
//showPrompt("請輸入解鎖密碼", function(data) { console.log(data)})
function showPrompt(message, callbackOnOK, callbackOnCancel,title,submits,cancel)
{
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確定";
    var cancel = (cancel) ? cancel : "取消";
    if (navigator.notification && navigator.notification.prompt)
    {
        navigator.notification.prompt(
            message, // message
            function(results) {
		
                if (results.buttonIndex === 1)
                {
                    if (callbackOnOK)
                    {
                        callbackOnOK(results.input1);
                    }
                }
                else
                {
                    if (callbackOnCancel)
                    {
                        callbackOnCancel();
                    }
                }
            },
            title,
            [submits, cancel]);
    }
    else
    {
		var answer = prompt(message);
        if (answer)
        {
            if (callbackOnOK)
            {
                callbackOnOK(answer);
            }
        }
        else
        {
            if (callbackOnCancel)
            {
                callbackOnCancel();
            }
        }
    }
}

function showConfirm(message, callbackOnOK, callbackOnCancel,title,submits,cancel)
{
	var title = (title) ? title : "提示訊息";
    var submits = (submits) ? submits : "確定";
    var cancel = (cancel) ? cancel : "取消";	
    if (navigator.notification && navigator.notification.confirm)
    {
        navigator.notification.confirm(
            message, // message
            function(buttonIndex) {
                if (buttonIndex === 1)
                {
                    if (callbackOnOK)
                    {
                        callbackOnOK();
                    }
                }
                else
                {
                    if (callbackOnCancel)
                    {
                        callbackOnCancel();
                    }else{
						return false
					}
                }
            },
            title,
            [submits, cancel]);
    }
    else
    {


        if (window.confirm(message))
        {
            if (callbackOnOK)
            {
                callbackOnOK();
            }
        }
        else
        {
            if (callbackOnCancel)
            {
                callbackOnCancel();
            }else{
				return false
			}
        }		
    }
}

function checkConnection() {
    var networkState = navigator.connection.type;

    var states = {};
    states[Connection.UNKNOWN]  = 'Unknown connection';
    states[Connection.ETHERNET] = 'Ethernet connection';
    states[Connection.WIFI]     = 'WiFi connection';
    states[Connection.CELL_2G]  = 'Cell 2G connection';
    states[Connection.CELL_3G]  = 'Cell 3G connection';
    states[Connection.CELL_4G]  = 'Cell 4G connection';
    states[Connection.CELL]     = 'Cell generic connection';
    states[Connection.NONE]     = 'No network connection';

    return states[networkState];
}
//陣列去除重複
/*
Array.prototype.unique = function()
{
	var n = {},r=[]; //n为hash表，r为临时数组
	for(var i = 0; i < this.length; i++) //遍历当前数组
	{
		if (!n[this[i]]) //如果hash表中没有当前项
		{
			n[this[i]] = true; //存入hash表
			r.push(this[i]); //把当前数组的当前项push到临时数组里面
		}
	}
	return r;
}
*/
//11/23 目前JS四捨五入較好的方式 round(1.005, 2);  小數點第二位
function round(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}

// 棄用...
function FloatFixed(string,n){
	return (parseInt(this * Math.pow( 10, n ) + 0.5)/ Math.pow( 10, n )).toString();
}

//浮點數相加
function FloatAdd(arg1, arg2)
{
  var r1, r2, m;
  try { r1 = arg1.toString().split(".")[1].length; } catch (e) { r1 = 0; }
  try { r2 = arg2.toString().split(".")[1].length; } catch (e) { r2 = 0; }
  m = Math.pow(10, Math.max(r1, r2));
  return (FloatMul(arg1, m) + FloatMul(arg2, m)) / m;
}
//浮點數相減
function FloatSubtraction(arg1, arg2)
{
  var r1, r2, m, n;
  try { r1 = arg1.toString().split(".")[1].length } catch (e) { r1 = 0 }
  try { r2 = arg2.toString().split(".")[1].length } catch (e) { r2 = 0 }
  m = Math.pow(10, Math.max(r1, r2));
  n = (r1 >= r2) ? r1 : r2;
  return ((arg1 * m - arg2 * m) / m).toFixed(n);
}
//浮點數相乘
function FloatMul(arg1, arg2)
{
  var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
  try { m += s1.split(".")[1].length; } catch (e) { }
  try { m += s2.split(".")[1].length; } catch (e) { }
  return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}
//浮點數相除
function FloatDiv(arg1, arg2)
{
  var t1 = 0, t2 = 0, r1, r2;
  try { t1 = arg1.toString().split(".")[1].length } catch (e) { }
  try { t2 = arg2.toString().split(".")[1].length } catch (e) { }
  with (Math)
  {
    r1 = Number(arg1.toString().replace(".", ""))
    r2 = Number(arg2.toString().replace(".", ""))
    return (r1 / r2) * pow(10, t2 - t1);
  }
}

//去除html
function stripHTML(input) {
     var output = '';
     if (typeof (input) == 'string') {
     var output = input.replace(/(<([^>]+)>)/ig, "");
     }
    return output;
}
function streewview(lat,lng){
    var map = (device.platform == "Android") ? "google.streetview:cbll="+lat+","+lng+"" : "https://maps.google.com/maps?q=&layer=c&cbll="+lat+","+lng+"&cbp=12,270"
	var method = (device.platform == "Android") ? '_system':'_blank'
	if (typeof liff == "undefined"){
		window.open(encodeURI(map), method, 'location=yes');
	}else{
		liff.openWindow({url: encodeURI(map),external: true});
	}		
}

//json排序
function sortByKey(array, key, method) {
    return array.sort(function(a, b) {
        var x = a[key]; var y = b[key];
		if(method == "asc" || !method)
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
		else if(method == "desc")
		return ((x > y) ? -1 : ((x < y) ? 1 : 0));				
    });
}

//算出兩點距離
//使用方法var distance = calcDistance([lat, lng],[poi_lat, poi_lng]);
function calcDegree(d){
	return d*Math.PI/180.0 ;
}
function calcDistance(f,t){
	var FINAL = 6378137.0 ; 
	var flat = calcDegree(f[0]) ;
	var flng = calcDegree(f[1]) ;
	var tlat = calcDegree(t[0]) ;
	var tlng = calcDegree(t[1])	 ;
				
	var result = Math.sin(flat)*Math.sin(tlat) ;
	result += Math.cos(flat)*Math.cos(tlat)*Math.cos(flng-tlng) ;
	return (Math.acos(result)*FINAL/1000).toFixed(2) ;
}


/*
CryptoJS v3.1.2
code.google.com/p/crypto-js
(c) 2009-2013 by Jeff Mott. All rights reserved.
code.google.com/p/crypto-js/wiki/License
*/
var CryptoJS=CryptoJS||function(u,p){var d={},l=d.lib={},s=function(){},t=l.Base={extend:function(a){s.prototype=this;var c=new s;a&&c.mixIn(a);c.hasOwnProperty("init")||(c.init=function(){c.$super.init.apply(this,arguments)});c.init.prototype=c;c.$super=this;return c},create:function(){var a=this.extend();a.init.apply(a,arguments);return a},init:function(){},mixIn:function(a){for(var c in a)a.hasOwnProperty(c)&&(this[c]=a[c]);a.hasOwnProperty("toString")&&(this.toString=a.toString)},clone:function(){return this.init.prototype.extend(this)}},
r=l.WordArray=t.extend({init:function(a,c){a=this.words=a||[];this.sigBytes=c!=p?c:4*a.length},toString:function(a){return(a||v).stringify(this)},concat:function(a){var c=this.words,e=a.words,j=this.sigBytes;a=a.sigBytes;this.clamp();if(j%4)for(var k=0;k<a;k++)c[j+k>>>2]|=(e[k>>>2]>>>24-8*(k%4)&255)<<24-8*((j+k)%4);else if(65535<e.length)for(k=0;k<a;k+=4)c[j+k>>>2]=e[k>>>2];else c.push.apply(c,e);this.sigBytes+=a;return this},clamp:function(){var a=this.words,c=this.sigBytes;a[c>>>2]&=4294967295<<
32-8*(c%4);a.length=u.ceil(c/4)},clone:function(){var a=t.clone.call(this);a.words=this.words.slice(0);return a},random:function(a){for(var c=[],e=0;e<a;e+=4)c.push(4294967296*u.random()|0);return new r.init(c,a)}}),w=d.enc={},v=w.Hex={stringify:function(a){var c=a.words;a=a.sigBytes;for(var e=[],j=0;j<a;j++){var k=c[j>>>2]>>>24-8*(j%4)&255;e.push((k>>>4).toString(16));e.push((k&15).toString(16))}return e.join("")},parse:function(a){for(var c=a.length,e=[],j=0;j<c;j+=2)e[j>>>3]|=parseInt(a.substr(j,
2),16)<<24-4*(j%8);return new r.init(e,c/2)}},b=w.Latin1={stringify:function(a){var c=a.words;a=a.sigBytes;for(var e=[],j=0;j<a;j++)e.push(String.fromCharCode(c[j>>>2]>>>24-8*(j%4)&255));return e.join("")},parse:function(a){for(var c=a.length,e=[],j=0;j<c;j++)e[j>>>2]|=(a.charCodeAt(j)&255)<<24-8*(j%4);return new r.init(e,c)}},x=w.Utf8={stringify:function(a){try{return decodeURIComponent(escape(b.stringify(a)))}catch(c){throw Error("Malformed UTF-8 data");}},parse:function(a){return b.parse(unescape(encodeURIComponent(a)))}},
q=l.BufferedBlockAlgorithm=t.extend({reset:function(){this._data=new r.init;this._nDataBytes=0},_append:function(a){"string"==typeof a&&(a=x.parse(a));this._data.concat(a);this._nDataBytes+=a.sigBytes},_process:function(a){var c=this._data,e=c.words,j=c.sigBytes,k=this.blockSize,b=j/(4*k),b=a?u.ceil(b):u.max((b|0)-this._minBufferSize,0);a=b*k;j=u.min(4*a,j);if(a){for(var q=0;q<a;q+=k)this._doProcessBlock(e,q);q=e.splice(0,a);c.sigBytes-=j}return new r.init(q,j)},clone:function(){var a=t.clone.call(this);
a._data=this._data.clone();return a},_minBufferSize:0});l.Hasher=q.extend({cfg:t.extend(),init:function(a){this.cfg=this.cfg.extend(a);this.reset()},reset:function(){q.reset.call(this);this._doReset()},update:function(a){this._append(a);this._process();return this},finalize:function(a){a&&this._append(a);return this._doFinalize()},blockSize:16,_createHelper:function(a){return function(b,e){return(new a.init(e)).finalize(b)}},_createHmacHelper:function(a){return function(b,e){return(new n.HMAC.init(a,
e)).finalize(b)}}});var n=d.algo={};return d}(Math);
(function(){var u=CryptoJS,p=u.lib.WordArray;u.enc.Base64={stringify:function(d){var l=d.words,p=d.sigBytes,t=this._map;d.clamp();d=[];for(var r=0;r<p;r+=3)for(var w=(l[r>>>2]>>>24-8*(r%4)&255)<<16|(l[r+1>>>2]>>>24-8*((r+1)%4)&255)<<8|l[r+2>>>2]>>>24-8*((r+2)%4)&255,v=0;4>v&&r+0.75*v<p;v++)d.push(t.charAt(w>>>6*(3-v)&63));if(l=t.charAt(64))for(;d.length%4;)d.push(l);return d.join("")},parse:function(d){var l=d.length,s=this._map,t=s.charAt(64);t&&(t=d.indexOf(t),-1!=t&&(l=t));for(var t=[],r=0,w=0;w<
l;w++)if(w%4){var v=s.indexOf(d.charAt(w-1))<<2*(w%4),b=s.indexOf(d.charAt(w))>>>6-2*(w%4);t[r>>>2]|=(v|b)<<24-8*(r%4);r++}return p.create(t,r)},_map:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/="}})();
(function(u){function p(b,n,a,c,e,j,k){b=b+(n&a|~n&c)+e+k;return(b<<j|b>>>32-j)+n}function d(b,n,a,c,e,j,k){b=b+(n&c|a&~c)+e+k;return(b<<j|b>>>32-j)+n}function l(b,n,a,c,e,j,k){b=b+(n^a^c)+e+k;return(b<<j|b>>>32-j)+n}function s(b,n,a,c,e,j,k){b=b+(a^(n|~c))+e+k;return(b<<j|b>>>32-j)+n}for(var t=CryptoJS,r=t.lib,w=r.WordArray,v=r.Hasher,r=t.algo,b=[],x=0;64>x;x++)b[x]=4294967296*u.abs(u.sin(x+1))|0;r=r.MD5=v.extend({_doReset:function(){this._hash=new w.init([1732584193,4023233417,2562383102,271733878])},
_doProcessBlock:function(q,n){for(var a=0;16>a;a++){var c=n+a,e=q[c];q[c]=(e<<8|e>>>24)&16711935|(e<<24|e>>>8)&4278255360}var a=this._hash.words,c=q[n+0],e=q[n+1],j=q[n+2],k=q[n+3],z=q[n+4],r=q[n+5],t=q[n+6],w=q[n+7],v=q[n+8],A=q[n+9],B=q[n+10],C=q[n+11],u=q[n+12],D=q[n+13],E=q[n+14],x=q[n+15],f=a[0],m=a[1],g=a[2],h=a[3],f=p(f,m,g,h,c,7,b[0]),h=p(h,f,m,g,e,12,b[1]),g=p(g,h,f,m,j,17,b[2]),m=p(m,g,h,f,k,22,b[3]),f=p(f,m,g,h,z,7,b[4]),h=p(h,f,m,g,r,12,b[5]),g=p(g,h,f,m,t,17,b[6]),m=p(m,g,h,f,w,22,b[7]),
f=p(f,m,g,h,v,7,b[8]),h=p(h,f,m,g,A,12,b[9]),g=p(g,h,f,m,B,17,b[10]),m=p(m,g,h,f,C,22,b[11]),f=p(f,m,g,h,u,7,b[12]),h=p(h,f,m,g,D,12,b[13]),g=p(g,h,f,m,E,17,b[14]),m=p(m,g,h,f,x,22,b[15]),f=d(f,m,g,h,e,5,b[16]),h=d(h,f,m,g,t,9,b[17]),g=d(g,h,f,m,C,14,b[18]),m=d(m,g,h,f,c,20,b[19]),f=d(f,m,g,h,r,5,b[20]),h=d(h,f,m,g,B,9,b[21]),g=d(g,h,f,m,x,14,b[22]),m=d(m,g,h,f,z,20,b[23]),f=d(f,m,g,h,A,5,b[24]),h=d(h,f,m,g,E,9,b[25]),g=d(g,h,f,m,k,14,b[26]),m=d(m,g,h,f,v,20,b[27]),f=d(f,m,g,h,D,5,b[28]),h=d(h,f,
m,g,j,9,b[29]),g=d(g,h,f,m,w,14,b[30]),m=d(m,g,h,f,u,20,b[31]),f=l(f,m,g,h,r,4,b[32]),h=l(h,f,m,g,v,11,b[33]),g=l(g,h,f,m,C,16,b[34]),m=l(m,g,h,f,E,23,b[35]),f=l(f,m,g,h,e,4,b[36]),h=l(h,f,m,g,z,11,b[37]),g=l(g,h,f,m,w,16,b[38]),m=l(m,g,h,f,B,23,b[39]),f=l(f,m,g,h,D,4,b[40]),h=l(h,f,m,g,c,11,b[41]),g=l(g,h,f,m,k,16,b[42]),m=l(m,g,h,f,t,23,b[43]),f=l(f,m,g,h,A,4,b[44]),h=l(h,f,m,g,u,11,b[45]),g=l(g,h,f,m,x,16,b[46]),m=l(m,g,h,f,j,23,b[47]),f=s(f,m,g,h,c,6,b[48]),h=s(h,f,m,g,w,10,b[49]),g=s(g,h,f,m,
E,15,b[50]),m=s(m,g,h,f,r,21,b[51]),f=s(f,m,g,h,u,6,b[52]),h=s(h,f,m,g,k,10,b[53]),g=s(g,h,f,m,B,15,b[54]),m=s(m,g,h,f,e,21,b[55]),f=s(f,m,g,h,v,6,b[56]),h=s(h,f,m,g,x,10,b[57]),g=s(g,h,f,m,t,15,b[58]),m=s(m,g,h,f,D,21,b[59]),f=s(f,m,g,h,z,6,b[60]),h=s(h,f,m,g,C,10,b[61]),g=s(g,h,f,m,j,15,b[62]),m=s(m,g,h,f,A,21,b[63]);a[0]=a[0]+f|0;a[1]=a[1]+m|0;a[2]=a[2]+g|0;a[3]=a[3]+h|0},_doFinalize:function(){var b=this._data,n=b.words,a=8*this._nDataBytes,c=8*b.sigBytes;n[c>>>5]|=128<<24-c%32;var e=u.floor(a/
4294967296);n[(c+64>>>9<<4)+15]=(e<<8|e>>>24)&16711935|(e<<24|e>>>8)&4278255360;n[(c+64>>>9<<4)+14]=(a<<8|a>>>24)&16711935|(a<<24|a>>>8)&4278255360;b.sigBytes=4*(n.length+1);this._process();b=this._hash;n=b.words;for(a=0;4>a;a++)c=n[a],n[a]=(c<<8|c>>>24)&16711935|(c<<24|c>>>8)&4278255360;return b},clone:function(){var b=v.clone.call(this);b._hash=this._hash.clone();return b}});t.MD5=v._createHelper(r);t.HmacMD5=v._createHmacHelper(r)})(Math);
(function(){var u=CryptoJS,p=u.lib,d=p.Base,l=p.WordArray,p=u.algo,s=p.EvpKDF=d.extend({cfg:d.extend({keySize:4,hasher:p.MD5,iterations:1}),init:function(d){this.cfg=this.cfg.extend(d)},compute:function(d,r){for(var p=this.cfg,s=p.hasher.create(),b=l.create(),u=b.words,q=p.keySize,p=p.iterations;u.length<q;){n&&s.update(n);var n=s.update(d).finalize(r);s.reset();for(var a=1;a<p;a++)n=s.finalize(n),s.reset();b.concat(n)}b.sigBytes=4*q;return b}});u.EvpKDF=function(d,l,p){return s.create(p).compute(d,
l)}})();
CryptoJS.lib.Cipher||function(u){var p=CryptoJS,d=p.lib,l=d.Base,s=d.WordArray,t=d.BufferedBlockAlgorithm,r=p.enc.Base64,w=p.algo.EvpKDF,v=d.Cipher=t.extend({cfg:l.extend(),createEncryptor:function(e,a){return this.create(this._ENC_XFORM_MODE,e,a)},createDecryptor:function(e,a){return this.create(this._DEC_XFORM_MODE,e,a)},init:function(e,a,b){this.cfg=this.cfg.extend(b);this._xformMode=e;this._key=a;this.reset()},reset:function(){t.reset.call(this);this._doReset()},process:function(e){this._append(e);return this._process()},
finalize:function(e){e&&this._append(e);return this._doFinalize()},keySize:4,ivSize:4,_ENC_XFORM_MODE:1,_DEC_XFORM_MODE:2,_createHelper:function(e){return{encrypt:function(b,k,d){return("string"==typeof k?c:a).encrypt(e,b,k,d)},decrypt:function(b,k,d){return("string"==typeof k?c:a).decrypt(e,b,k,d)}}}});d.StreamCipher=v.extend({_doFinalize:function(){return this._process(!0)},blockSize:1});var b=p.mode={},x=function(e,a,b){var c=this._iv;c?this._iv=u:c=this._prevBlock;for(var d=0;d<b;d++)e[a+d]^=
c[d]},q=(d.BlockCipherMode=l.extend({createEncryptor:function(e,a){return this.Encryptor.create(e,a)},createDecryptor:function(e,a){return this.Decryptor.create(e,a)},init:function(e,a){this._cipher=e;this._iv=a}})).extend();q.Encryptor=q.extend({processBlock:function(e,a){var b=this._cipher,c=b.blockSize;x.call(this,e,a,c);b.encryptBlock(e,a);this._prevBlock=e.slice(a,a+c)}});q.Decryptor=q.extend({processBlock:function(e,a){var b=this._cipher,c=b.blockSize,d=e.slice(a,a+c);b.decryptBlock(e,a);x.call(this,
e,a,c);this._prevBlock=d}});b=b.CBC=q;q=(p.pad={}).Pkcs7={pad:function(a,b){for(var c=4*b,c=c-a.sigBytes%c,d=c<<24|c<<16|c<<8|c,l=[],n=0;n<c;n+=4)l.push(d);c=s.create(l,c);a.concat(c)},unpad:function(a){a.sigBytes-=a.words[a.sigBytes-1>>>2]&255}};d.BlockCipher=v.extend({cfg:v.cfg.extend({mode:b,padding:q}),reset:function(){v.reset.call(this);var a=this.cfg,b=a.iv,a=a.mode;if(this._xformMode==this._ENC_XFORM_MODE)var c=a.createEncryptor;else c=a.createDecryptor,this._minBufferSize=1;this._mode=c.call(a,
this,b&&b.words)},_doProcessBlock:function(a,b){this._mode.processBlock(a,b)},_doFinalize:function(){var a=this.cfg.padding;if(this._xformMode==this._ENC_XFORM_MODE){a.pad(this._data,this.blockSize);var b=this._process(!0)}else b=this._process(!0),a.unpad(b);return b},blockSize:4});var n=d.CipherParams=l.extend({init:function(a){this.mixIn(a)},toString:function(a){return(a||this.formatter).stringify(this)}}),b=(p.format={}).OpenSSL={stringify:function(a){var b=a.ciphertext;a=a.salt;return(a?s.create([1398893684,
1701076831]).concat(a).concat(b):b).toString(r)},parse:function(a){a=r.parse(a);var b=a.words;if(1398893684==b[0]&&1701076831==b[1]){var c=s.create(b.slice(2,4));b.splice(0,4);a.sigBytes-=16}return n.create({ciphertext:a,salt:c})}},a=d.SerializableCipher=l.extend({cfg:l.extend({format:b}),encrypt:function(a,b,c,d){d=this.cfg.extend(d);var l=a.createEncryptor(c,d);b=l.finalize(b);l=l.cfg;return n.create({ciphertext:b,key:c,iv:l.iv,algorithm:a,mode:l.mode,padding:l.padding,blockSize:a.blockSize,formatter:d.format})},
decrypt:function(a,b,c,d){d=this.cfg.extend(d);b=this._parse(b,d.format);return a.createDecryptor(c,d).finalize(b.ciphertext)},_parse:function(a,b){return"string"==typeof a?b.parse(a,this):a}}),p=(p.kdf={}).OpenSSL={execute:function(a,b,c,d){d||(d=s.random(8));a=w.create({keySize:b+c}).compute(a,d);c=s.create(a.words.slice(b),4*c);a.sigBytes=4*b;return n.create({key:a,iv:c,salt:d})}},c=d.PasswordBasedCipher=a.extend({cfg:a.cfg.extend({kdf:p}),encrypt:function(b,c,d,l){l=this.cfg.extend(l);d=l.kdf.execute(d,
b.keySize,b.ivSize);l.iv=d.iv;b=a.encrypt.call(this,b,c,d.key,l);b.mixIn(d);return b},decrypt:function(b,c,d,l){l=this.cfg.extend(l);c=this._parse(c,l.format);d=l.kdf.execute(d,b.keySize,b.ivSize,c.salt);l.iv=d.iv;return a.decrypt.call(this,b,c,d.key,l)}})}();
(function(){for(var u=CryptoJS,p=u.lib.BlockCipher,d=u.algo,l=[],s=[],t=[],r=[],w=[],v=[],b=[],x=[],q=[],n=[],a=[],c=0;256>c;c++)a[c]=128>c?c<<1:c<<1^283;for(var e=0,j=0,c=0;256>c;c++){var k=j^j<<1^j<<2^j<<3^j<<4,k=k>>>8^k&255^99;l[e]=k;s[k]=e;var z=a[e],F=a[z],G=a[F],y=257*a[k]^16843008*k;t[e]=y<<24|y>>>8;r[e]=y<<16|y>>>16;w[e]=y<<8|y>>>24;v[e]=y;y=16843009*G^65537*F^257*z^16843008*e;b[k]=y<<24|y>>>8;x[k]=y<<16|y>>>16;q[k]=y<<8|y>>>24;n[k]=y;e?(e=z^a[a[a[G^z]]],j^=a[a[j]]):e=j=1}var H=[0,1,2,4,8,
16,32,64,128,27,54],d=d.AES=p.extend({_doReset:function(){for(var a=this._key,c=a.words,d=a.sigBytes/4,a=4*((this._nRounds=d+6)+1),e=this._keySchedule=[],j=0;j<a;j++)if(j<d)e[j]=c[j];else{var k=e[j-1];j%d?6<d&&4==j%d&&(k=l[k>>>24]<<24|l[k>>>16&255]<<16|l[k>>>8&255]<<8|l[k&255]):(k=k<<8|k>>>24,k=l[k>>>24]<<24|l[k>>>16&255]<<16|l[k>>>8&255]<<8|l[k&255],k^=H[j/d|0]<<24);e[j]=e[j-d]^k}c=this._invKeySchedule=[];for(d=0;d<a;d++)j=a-d,k=d%4?e[j]:e[j-4],c[d]=4>d||4>=j?k:b[l[k>>>24]]^x[l[k>>>16&255]]^q[l[k>>>
8&255]]^n[l[k&255]]},encryptBlock:function(a,b){this._doCryptBlock(a,b,this._keySchedule,t,r,w,v,l)},decryptBlock:function(a,c){var d=a[c+1];a[c+1]=a[c+3];a[c+3]=d;this._doCryptBlock(a,c,this._invKeySchedule,b,x,q,n,s);d=a[c+1];a[c+1]=a[c+3];a[c+3]=d},_doCryptBlock:function(a,b,c,d,e,j,l,f){for(var m=this._nRounds,g=a[b]^c[0],h=a[b+1]^c[1],k=a[b+2]^c[2],n=a[b+3]^c[3],p=4,r=1;r<m;r++)var q=d[g>>>24]^e[h>>>16&255]^j[k>>>8&255]^l[n&255]^c[p++],s=d[h>>>24]^e[k>>>16&255]^j[n>>>8&255]^l[g&255]^c[p++],t=
d[k>>>24]^e[n>>>16&255]^j[g>>>8&255]^l[h&255]^c[p++],n=d[n>>>24]^e[g>>>16&255]^j[h>>>8&255]^l[k&255]^c[p++],g=q,h=s,k=t;q=(f[g>>>24]<<24|f[h>>>16&255]<<16|f[k>>>8&255]<<8|f[n&255])^c[p++];s=(f[h>>>24]<<24|f[k>>>16&255]<<16|f[n>>>8&255]<<8|f[g&255])^c[p++];t=(f[k>>>24]<<24|f[n>>>16&255]<<16|f[g>>>8&255]<<8|f[h&255])^c[p++];n=(f[n>>>24]<<24|f[g>>>16&255]<<16|f[h>>>8&255]<<8|f[k&255])^c[p++];a[b]=q;a[b+1]=s;a[b+2]=t;a[b+3]=n},keySize:8});u.AES=p._createHelper(d)})();

CryptoJS.pad.ZeroPadding = {
    pad: function (data, blockSize) {
        // Shortcut
        var blockSizeBytes = blockSize * 4;

        // Pad
        data.clamp();
        data.sigBytes += blockSizeBytes - ((data.sigBytes % blockSizeBytes) || blockSizeBytes);
    },

    unpad: function (data) {
        // Shortcut
        var dataWords = data.words;

        // Unpad
        var i = data.sigBytes - 1;
        while (!((dataWords[i >>> 2] >>> (24 - (i % 4) * 8)) & 0xff)) {
            i--;
        }
        data.sigBytes = i + 1;
    }
};

CryptoJS.mode.ECB=function(){var a=CryptoJS.lib.BlockCipherMode.extend();a.Encryptor=a.extend({processBlock:function(a,b){this._cipher.encryptBlock(a,b)}});a.Decryptor=a.extend({processBlock:function(a,b){this._cipher.decryptBlock(a,b)}});return a}();
