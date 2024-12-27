var status = 0;
$(function () {
  if (
    location.pathname.indexOf("index") >= 0 ||
    location.pathname.substr(-1) == "/"
  ) {
    // 下拉選單如果有 default這屬性, 代表要預選, 一頁可能有多個select..., 所以用each
    // 如: <select defaults="value">
    $("select[defaults]").each(function () {
      var defaults = $(this).attr("defaults");
      $(this)
        .find("option")
        .each(function () {
          if ($(this).val() === defaults) {
            $(this).prop("selected", true);
          }
        });
    });
  }

  if (location.pathname.indexOf("login") >= 0) {
    // 2022-06-14 避免手機驗證後，頁面因資料輸入錯誤而返回，卻告知手機驗證碼失效。所以在返回時先做一次手機號碼判斷
    // var mobile = $('#mobile').val();
    // var code = $('#sms-input').val();
    // if (mobile !== "") {
    //     var event_arr = [];
    //     event_arr['success'] = function (data) {
    //         console.log(data)
    //         if (data.state == "-1" || data.state == "1") {
    //             alert(data.message);
    //             $('[name=code]').val('');
    //         }
    //
    //         if (parseInt(data.state) >= 0) {
    //             // 驗證成功轉址之類
    //             $('#mobile').attr('readonly', 'readonly');
    //             // $('.confirm_phone').hide();
    //             // $('.data_box').show('200').find('input').attr('req', 'Y');
    //         }
    //     }
    //     ajax_pub_adv("api/sms_init.php", {
    //         "mobile": aes_encrypt(mobile), "code": aes_encrypt(code)
    //     }, event_arr, {"async": true})
    // }

    $("#mobile").blur(function () {
      $(".progress-btn")
        .removeAttr("disabled")
        .removeClass("wait")
        .html("認證<span></span>");
      $("#mobileValidation")
        .removeClass("hide")
        .find("[name=code]")
        .attr("req", "Y");
      $("#mobile")
        .closest(".warning-box")
        .find(".alert-text-backend")
        .text("")
        .removeClass("show text-primary text-success");
    });

    $(".progress-btn").click(function () {
      $("#mobile")
        .closest(".warning-box")
        .find(".alert-text-backend")
        .text("")
        .removeClass("show text-primary text-success");
      var sum_arr = [];
      $(this)
        .closest(".input-group")
        .find("input")
        .each(function (i, n) {
          var title = $.trim($(this).attr("title"));
          if ($("#mobile").val() == undefined || $("#mobile").val() == "") {
            $(this)
              .closest(".warning-box")
              .find(".alert-text")
              .addClass("show");
            sum_arr.push(title);
          } else if (
            this.name.indexOf("tel") >= 0 &&
            ValidateMobile($(this).val()) === false
          ) {
            $(this)
              .closest(".warning-box")
              .find(".alert-text")
              .addClass("show");
            sum_arr.push("手機格式錯誤");
          }
        });
      if (sum_arr.length > 0) {
        sum_arr = $.unique(sum_arr); // 消除重複的陣列元素
        sum_arr = sum_arr.join("、");
      } else {
        // $(".progress-btn").prop('disabled', true);
        var mobile = $("#mobile").val();
        var userId = $("[name=userId]").val();
        var displayName = $("[name=displayName]").val();

        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state == "-1") {
            $("#mobile")
              .closest(".warning-box")
              .find(".alert-text-backend")
              .text(data.message)
              .addClass("show text-primary");
          } else if (data.state == "1") {
            // 簡訊已發送
            $("#mobile")
              .closest(".warning-box")
              .find(".alert-text-backend")
              .text(data.message)
              .addClass("show text-success");

            // 開始計時，60秒內不得再發送簡訊
            var progressBtn = $(".progress-btn");
            progressBtn.addClass("wait").attr("disabled", true);

            // var t = 60;
            // var intervalID = setInterval(function () {
            //     $(".progress-btn span").html('(' + t + ')');
            //     t = t - 1;
            //     if (t == "-1") {
            //         clearInterval(intervalID);
            //         $(".progress-btn span").html("");
            //         progressBtn.removeClass("wait");
            //         progressBtn.attr("disabled", false);
            //         return false;
            //     }
            // }, 1000);
          } else if (data.state == "2") {
            // 已註冊，無須發送簡訊
            // 將手機驗證碼input隱藏、必填移除
            $("[name=isLogin]").val(aes_encrypt(false));
            $(".progress-btn").attr("disabled", true).text(data.message);
            $("#mobileValidation")
              .addClass("hide")
              .find("[name=code]")
              .removeAttr("req");
          }
        };
        ajax_pub_adv(
          "api/sms_notify.php",
          {
            mobile: mobile,
            userId: userId,
            displayName: displayName,
          },
          event_arr,
          { async: true }
        );
      }
    });

    // 縣市區域
    var city_param = [];
    var city_default = "";
    var region_default = "";

    city_default = $("[name=city]").attr("defaults");
    region_default = $("[name=region]").attr("defaults");

    city_param["city_name"] = "city";
    city_param["region_name"] = "region";
    city_param["city_default"] = city_default;
    city_param["region_default"] = region_default;
    city_related(city_param);
  }

  if (location.pathname.indexOf("fill_in") >= 0) {
    $("#mobile").blur(function () {
      if ($(this).val() !== "") {
        $(".progress-btn")
          .removeAttr("disabled")
          .removeClass("disabled wait")
          .html("認證<span></span>");
      }
      $("#mobileValidation")
        .removeClass("hide")
        .find("[name=code]")
        .attr("req", "Y");
      $("#mobile")
        .closest(".warning-box")
        .find(".alert-text-backend")
        .text("")
        .removeClass("show text-primary text-success");
    });

    $(".progress-btn").click(function () {
      $("#mobile")
        .closest(".warning-box")
        .find(".alert-text-backend")
        .text("")
        .removeClass("show text-primary text-success");
      var sum_arr = [];
      $(this)
        .closest(".input-group")
        .find("input")
        .each(function (i, n) {
          var title = $.trim($(this).attr("title"));
          if ($("#mobile").val() == undefined || $("#mobile").val() == "") {
            $(this)
              .closest(".warning-box")
              .find(".alert-text")
              .addClass("show");
            sum_arr.push(title);
          } else if (
            this.name.indexOf("tel") >= 0 &&
            ValidateMobile($(this).val()) == false
          ) {
            $(this)
              .closest(".warning-box")
              .find(".alert-text")
              .addClass("show");
            sum_arr.push("手機格式錯誤");
          }
        });
      if (sum_arr.length > 0) {
        sum_arr = $.unique(sum_arr); //消除重複的陣列元素
        sum_arr = sum_arr.join("、");
      } else {
        var mobile = $("#mobile").val();

        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state == "-1") {
            // 有錯誤
            $("#mobile")
              .closest(".warning-box")
              .find(".alert-text")
              .text(data.message)
              .addClass("show text-primary");
          } else if (data.state == "1") {
            // 簡訊已發送
            $("#mobile")
              .closest(".warning-box")
              .find(".alert-text")
              .text(data.message)
              .addClass("show text-success");

            // 開始計時，60秒內不得再發送簡訊
            var progressBtn = $(".progress-btn");
            progressBtn.addClass("wait").attr("disabled", true);

            // var t = 60;
            // var intervalID = setInterval(function () {
            //     $(".progress-btn span").html('(' + t + ')');
            //     t = t - 1;
            //     if (t == "-1") {
            //         clearInterval(intervalID);
            //         $(".progress-btn span").html("");
            //         progressBtn.removeClass("wait");
            //         progressBtn.attr("disabled", false);
            //         return false;
            //     }
            // }, 1000);
          } else if (data.state == "2") {
            // 手機已存在會員，無須重新驗證
            $("#mobile, #sms-input")
              .closest(".warning-box")
              .find(".alert-text")
              .text("");
            $("#sms-input").removeAttr("req");
            $("#sms-success").modal("show");
            setTimeout(function () {
              $("#sms-success").modal("hide");
            }, 1200);
          }
        };
        ajax_pub_adv("api/sms_bind.php", { mobile: mobile }, event_arr, {
          async: true,
        });
      }

      $("#sms-success").on("hidden.bs.modal", function () {
        $("#mobile").attr("readonly", true);
        $("#sms-input").attr("readonly", true);
        $(".st-2").removeClass("hidden");
        $(".progress-btn").addClass("hidden");

        $("#mobile").addClass("okay");
        $(".okay-text").addClass("show");
        $(".sms-form-item").addClass("hidden");
      });
    });

    $(document).on("click", "#sms-btn", function () {
      var sumarr = [];
      $(".st-1")
        .find("input")
        .each(function () {
          if ($(this).val() == "") {
            $(this)
              .closest(".warning-box")
              .find(".alert-text")
              .addClass("show");
            $(this).addClass("alert-border");
            sumarr.push("有空白欄位");
          }
        });
      if (sumarr.length > 0) {
        sumarr = sumarr.join("、");
      } else {
        var mobile = $("#mobile").val();
        var code = $("#sms-input").val();

        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state == "-1" || data.state == "1") {
            $("#sms-input")
              .closest(".warning-box")
              .find(".alert-text")
              .text(data.message)
              .addClass("show text-primary");
            // 失敗通常要有訊息, 但成功不見得要有, 如果是APP專案, 每次更新前端語言都要送審, 乾脆從後端輸出 0/1 來決定要不要alert
          }

          if (parseInt(data.state) >= 0) {
            // 驗證成功轉址之類
            $("#mobile, #sms-input")
              .closest(".warning-box")
              .find(".alert-text")
              .text("");
            $("#sms-success").modal("show");
            setTimeout(function () {
              $("#sms-success").modal("hide");
            }, 1200);
          }
        };
        ajax_pub_adv(
          "api/sms_validate.php",
          { mobile: mobile, code: code },
          event_arr,
          { async: true }
        );
      }

      $("#sms-success").on("hidden.bs.modal", function () {
        $("#mobile").attr("readonly", true);
        $("#sms-input").attr("readonly", true);
        $(".st-2").removeClass("hidden");
        $(".progress-btn").addClass("hidden");

        $("#mobile").addClass("okay");
        $(".okay-text").addClass("show");
        $(".sms-form-item").addClass("hidden");
      });
    });
  }

  if (location.pathname.indexOf("code_list.") >= 0) {
    // 註銷 QRCode
    $(document).on("click", ".del-btn", function () {
      var info = $(this).data("info");
      var id = $(this).data("id");
      var $this = $(this);

      if (confirm("確認註銷此筆QR CODE \n#" + info + "?") === false) {
        return false;
      } else {
        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state == "1") {
            alert(data.message);
            // $this.closest(".item").remove();
            window.location.reload();
          }
        };

        ajax_pub_adv("api/code_end.php", { qrcode_id: id }, event_arr, {
          async: true,
        });
      }
    });

    $("[name=clearSession]").click(function () {
      var event_arr = [];
      event_arr["success"] = function (data) {
        if (data.state == "1") {
          // alert(data.message)
          // $this.closest(".item").remove();
          document.location.href = "https://liff.line.me/1657192181-OgEgXVG0";
        }
      };

      ajax_pub_adv("api/session_clean.php", {}, event_arr, { async: true });
    });
  }
  if (
    location.pathname.indexOf("/info.php") >= 0 ||
    location.pathname.indexOf("/big_info.php") >= 0
  ) {
    var geo_options = {
      enableHighAccuracy: false, //值為 true時，表示使用高精度定位 (預設為false)
      maximumAge: 30000, //設定上一次取得之位置資訊的有效期限 (單位毫秒, 預設為 0)
      timeout: 8000, //逾時計時器 (單位毫秒), 預設無限大 (infinity), 若超過此時間仍未取得位置資訊, 將會觸發失敗function.
    };
    //navigator.geolocation.getCurrentPosition(成功function, 失敗function, 參數);
    navigator.geolocation.getCurrentPosition(
      function (pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        $("[name=lat]").val(aes_encrypt(lat));
        $("[name=lng]").val(aes_encrypt(lng));
      },
      function (err) {
        alert("定位失敗, 請晚些再試");
      },
      geo_options
    );
  }

  // 列表批次選取
  $("input[name=box_toggle]").click(function (event) {
    if (this.checked) {
      $("input[name=box_list]").each(function () {
        //loop through each checkbox
        $(this).prop("checked", true); //check
      });
    } else {
      $("input[name=box_list]").each(function () {
        //loop through each checkbox
        $(this).prop("checked", false); //uncheck
      });
    }
  });

  $("button[id=lineNotify]").click(function (event) {
    // 找到所有被勾選的id，並合併成,字串
    var id = $("input[name=box_list]:checked")
      .map(function () {
        return $(this).val();
      })
      .get()
      .join(",");

    if (id !== "") {
      // 發送的Modal資訊帶入
      var memberModal = $("#lineNotifyModal");
      memberModal.modal("show");
      memberModal.on("shown.bs.modal", function (e) {
        // do something...
        $("[name=ids]").val(id);

        $("button[id=lineNotifyConfirm]").click(function (event) {
          var this_form = Boolean($(this).parents("form"))
            ? $(this).parents("form")
            : $("#" + $(this).attr("form"));

          var ans = prompt("請輸入 確定推播 四個字");
          if (pintech_trim(ans) === "確定推播") {
            $("button[id=lineNotifyConfirm]").prop("disabled", true);
            $("button[id=lineNotifyConfirm]").append(
              ' <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>'
            );
            $("input[name=box_list]").each(function () {
              // loop through each checkbox
              $(this).prop("checked", false); // uncheck
            });

            this_form.submit();
          }
        });
      });
    } else {
      alert("請先勾選欲推播的項目");
      return false;
    }
  });
});

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  }
}

function showPosition(position) {
  $("[name=lat]").val(aes_encrypt(position.coords.latitude));
  $("[name=lng]").val(aes_encrypt(position.coords.longitude));
}
