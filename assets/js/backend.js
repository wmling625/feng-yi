$(function () {
  if (location.pathname.indexOf("admin") >= 0) {
    //###############以下為通用fun#######################//

    //避免任何場合下ENTER送出
    $(document).on("keypress", "form", function (e) {
      var code = e.keyCode || e.which;
      if (
        code == 13 &&
        !$(e.target).is('textarea,input[type="submit"],input[type="button"]')
      ) {
        e.preventDefault ? e.preventDefault() : (e.returnValue = false);
        return false;
      }
    });

    const tooltipTriggerList = document.querySelectorAll(
      '[data-bs-toggle="tooltip"]'
    );
    const tooltipList = [...tooltipTriggerList].map(
      (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
    );

    // $('[data-mask]').inputmask()

    //Initialize Select2 Elements
    $(".select2bs4").select2({
      theme: "bootstrap4",
    });

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

    //###############以下為特定頁面fun#######################//
    if (location.pathname.indexOf("member_list.php") >= 0) {
      $("[name=member_qr]").click(function () {
        var id = $(this).data("id");
        var title = $(this).data("title");
        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state === 1) {
            document.location.href = "qrcode_list.php?member_id=" + id;
          } else {
            alert("會員(" + title + ") " + data.message);
          }
        };

        ajax_pub_adv(
          "../api/get_member_qr.php",
          {
            id: id,
          },
          event_arr,
          { async: true }
        );
      });

      $("[name=member_qr_big]").click(function () {
        var id = $(this).data("id");
        var title = $(this).data("title");
        var event_arr = [];
        event_arr["success"] = function (data) {
          if (data.state === 1) {
            document.location.href = "qrcode_big_list.php?member_id=" + id;
          } else {
            alert("會員(" + title + ") " + data.message);
          }
        };

        ajax_pub_adv(
          "../api/get_member_qr_big.php",
          {
            id: id,
          },
          event_arr,
          { async: true }
        );
      });
    }

    if (location.pathname.indexOf("member_mang.php") >= 0) {
      // 縣市區域
      var city_param = [
        {
          city_name: "",
          region_name: "",
          city_default: "",
          region_default: "",
        },
        {
          city_name: "",
          region_name: "",
          city_default: "",
          region_default: "",
        },
        {
          city_name: "",
          region_name: "",
          city_default: "",
          region_default: "",
        },
      ];

      city_param[0]["city_name"] = "city0";
      city_param[0]["region_name"] = "region0";
      city_param[0]["city_default"] = $("[name=city0]").attr("defaults");
      city_param[0]["region_default"] = $("[name=region0]").attr("defaults");
      city_related(city_param[0]);

      city_param[1]["city_name"] = "city1";
      city_param[1]["region_name"] = "region1";
      city_param[1]["city_default"] = $("[name=city1]").attr("defaults");
      city_param[1]["region_default"] = $("[name=region1]").attr("defaults");
      city_related(city_param[1]);

      city_param[2]["city_name"] = "city2";
      city_param[2]["region_name"] = "region2";
      city_param[2]["city_default"] = $("[name=city2]").attr("defaults");
      city_param[2]["region_default"] = $("[name=region2]").attr("defaults");
      city_related(city_param[2]);

      $("#same_as_top").change(function () {
        var checked = $(this).prop("checked");
        var address0 = $("[name=address0]").val();
        var address1 = $("[name=address1]");
        if (checked) {
          $("[name=city1]").attr("readonly", "readonly");
          $("[name=region1]").attr("readonly", "readonly");
          address1.attr("readonly", "readonly");
          city_param[1]["city_default"] = $("[name=city0]").val();
          city_param[1]["region_default"] = $("[name=region0]").val();
          address1.val(address0);
          city_related(city_param[1]);
        } else {
          $("[name=city1]").removeAttr("readonly");
          $("[name=region1]").removeAttr("readonly");
          $("[name=address1]").removeAttr("readonly");
        }
      });

      // 開業執照
      $(document).on("change", "[name^=certificate2_]", function (e) {
        // 宣告物件
        var box_arr = {};
        $(this)
          .parents("td:first")
          .find("[name^=certificate2_]")
          .each(function (i, n) {
            var key = $(this).val();
            var title = $(this).attr("name");

            box_arr[title] = key;
          });
        var str = JSON.stringify(box_arr);
        $(this).parents("td:first").find("[box_arr]").val(str);
      });
      $("[name^=certificate2_]").change(); //自主觸發

      $("input[name=dob]").daterangepicker({
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
          format: "YYYY-MM-DD",
          applyLabel: "確定",
          cancelLabel: "取消",
          fromLabel: "開始日期",
          toLabel: "結束日期",
          customRangeLabel: "自訂日期區間",
          daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
          monthNames: [
            "1月",
            "2月",
            "3月",
            "4月",
            "5月",
            "6月",
            "7月",
            "8月",
            "9月",
            "10月",
            "11月",
            "12月",
          ],
          firstDay: 1,
        },
      });

      $("input[name=dob]").on("apply.daterangepicker", function (ev, picker) {
        $(this).val(picker.startDate.format("YYYY-MM-DD"));
      });

      $("input[name=renew_date]").daterangepicker({
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: true,
        locale: {
          format: "YYYY-MM-DD",
          applyLabel: "確定",
          cancelLabel: "取消",
          fromLabel: "開始日期",
          toLabel: "結束日期",
          customRangeLabel: "自訂日期區間",
          daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
          monthNames: [
            "1月",
            "2月",
            "3月",
            "4月",
            "5月",
            "6月",
            "7月",
            "8月",
            "9月",
            "10月",
            "11月",
            "12月",
          ],
          firstDay: 1,
        },
      });

      $("input[name=renew_date]").on(
        "apply.daterangepicker",
        function (ev, picker) {
          $(this).val(picker.startDate.format("YYYY-MM-DD"));
        }
      );

      // 根據會員身份 制定不同必填
      $("[name=types_option4]")
        .on("change", function () {
          var qu = $("option:selected", this).data("qu");
          var required = {
            p0: [],
            p1: [14, 15, 16, 18, 19, 20, 21, 22, 23, 24, 25], // 開業會員
            p2: [14, 15, 16, 19, 20, 21, 22, 23, 24, 25], // 執業會員
            p3: [14, 15, 24, 25], // 待業會員
            p4: [21, 24], // 贊助會員
          };
          $("body")
            .find("[data-id]")
            .each(function (index, item) {
              $(this).removeAttr("req");
              $(this).parents("tr:first").find(".required").text("");
              var id = $(this).data("id"); // 問題id
              required[qu].forEach((qid) => {
                if (id === qid) {
                  $(this).attr("req", "Y");
                  $(this).parents("tr:first").find(".required").text("*");
                }
              });
            });
        })
        .change();
    }

    if (location.pathname.indexOf("advertisement_mang.php") >= 0) {
      // $('#reservationtime').daterangepicker({
      //     autoUpdateInput: false,
      //     timePicker: true,
      //     timePickerIncrement: 30,
      //     locale: {
      //         format: 'YYYY/MM/DD hh:mm:ss'
      //     }
      // })

      // 根據廣告類型 制定不同必填
      $("[name=types_option]")
        .on("change", function () {
          var qu = $("option:selected", this).data("qu");
          var required = {
            p0: [],
            p1: [3], // 影片廣告
            p2: [0, 1, 2], // 圖文連結廣告
            p3: [1], // 彈出式單一圖片廣告
          };
          $("body")
            .find("[data-id]")
            .each(function (index, item) {
              $(this).parents("tr:first").addClass("hide");
              $(this).removeAttr("req");
              $(this).parents("tr:first").find(".required").text("");
              var id = $(this).data("id"); // 問題id
              required[qu].forEach((qid) => {
                if (id === qid) {
                  $(this).parents("tr:first").removeClass("hide");
                  $(this).attr("req", "Y");
                  $(this).parents("tr:first").find(".required").text("*");
                  if (id == 1) {
                    if (qu === "p2") {
                      $("#adviseSize").html("建議尺寸490px*368px");
                    } else if (qu === "p3") {
                      $("#adviseSize").html(
                        "建議尺寸380px*452px<br/>(約95:113的比例)"
                      );
                    }
                  }
                }
              });
            });
        })
        .change();
    }

    if (location.pathname.indexOf("change_") >= 0) {
      // 縣市區域
      var city_param = [
        {
          city_name: "",
          region_name: "",
          city_default: "",
          region_default: "",
        },
        {
          city_name: "",
          region_name: "",
          city_default: "",
          region_default: "",
        },
      ];

      city_param[0]["city_name"] = "city0";
      city_param[0]["region_name"] = "region0";
      city_param[0]["city_default"] = $("[name=city0]").attr("defaults");
      city_param[0]["region_default"] = $("[name=region0]").attr("defaults");
      city_related(city_param[0]);

      city_param[1]["city_name"] = "city1";
      city_param[1]["region_name"] = "region1";
      city_param[1]["city_default"] = $("[name=city1]").attr("defaults");
      city_param[1]["region_default"] = $("[name=region1]").attr("defaults");
      city_related(city_param[1]);

      // 根據辦理項目 制定不同必填
      $("[name=types_option0]")
        .on("change", function () {
          var qu = $("option:selected", this).data("qu");
          var required = {
            p1: [1, 3], // 執業異動-執業
            p5: [1, 3, 4], // 執業異動-變更
            p8: [1, 3, 4], // 機構異動-開業
            p10: [6], // 機構異動-變更
          };
          $("body")
            .find("[data-id]")
            .each(function (index, item) {
              $(this).removeAttr("req");
              $(this).parents("tr:first").find(".required").text("");
              var id = $(this).data("id"); // 問題id
              if (required[qu] !== undefined) {
                required[qu].forEach((qid) => {
                  if (id === qid) {
                    $(this).attr("req", "Y");
                    $(this).parents("tr:first").find(".required").text("*");
                  }
                });
              }
            });

          if ($("#changeitems") !== undefined) {
            if ($(this).val() === "變更") {
              $("#changeitems").removeClass("hide");
            } else {
              $("#changeitems").addClass("hide");
            }
          }
        })
        .change();

      $("[name=types_option1]")
        .on("change", function () {
          var value = $("option:selected", this).val();
          $("[name=city1]").removeAttr("req");
          $("[name=city1]").parents("tr:first").find(".required").text("");
          if (value === "郵寄") {
            $("#sendbypost").removeClass("hide");
            $("[name=city1]").attr("req", "Y");
            $("[name=region1]").attr("req", "Y");
            $("[name=address1]").attr("req", "Y");
            $("[name=city1]").parents("tr:first").find(".required").text("*");
          } else {
            $("#sendbypost").addClass("hide");
          }
        })
        .change();

      $("input[type=checkbox][name^=types_option2_]")
        .on("click", function () {
          var ids = [];
          $.each(
            $("input[type=checkbox][name^=types_option2_]"),
            function (index, item) {
              if ($(this).prop("checked")) {
                ids.push($(this).data("id"));
              }
            }
          );
          $("body")
            .find("[data-id]")
            .each(function (index, item) {
              $(this).removeAttr("req");
              $(this).parents("tr:first").find(".required").text("");
              var id = $(this).data("id"); // 問題id
              ids.forEach((dataId) => {
                if (id === dataId) {
                  $(this).attr("req", "Y");
                  $(this).parents("tr:first").find(".required").text("*");
                }
              });
            });
        })
        .change();
    }

    if (location.pathname.indexOf("qrcode_mang") >= 0) {
      $("[name=orders]")
        .on("change", function () {
          var numberArea = $("#numberArea");
          var value = $("option:selected", this).val();
          $("[id^=bindArea]")
            .find("input, select")
            .each(function () {
              $(this).removeAttr("req");
              $(this).parents("tr:first").find(".required").text("");
            });

          numberArea.removeClass("hide");
          numberArea.find("input:first,select:first").attr("req", "Y").val(1);
          numberArea.find(".required:first").text("*");

          if (value === "1") {
            $("[id^=bindArea]").each(function () {
              $(this).removeClass("hide");
              $(this).find("input:first,select:first").attr("req", "Y");
              $(this).find(".required:first").text("*");
            });
          } else {
            $("[id^=bindArea]").each(function () {
              $(this).addClass("hide");
            });
          }

          if (value === "1" || value == "-2") {
            numberArea.addClass("hide");
            numberArea
              .find("input:first,select:first")
              .removeAttr("req")
              .val(0);
            numberArea.find(".required:first").text("");
          } else {
            numberArea.removeClass("hide");
            numberArea.find("input:first,select:first").attr("req", "Y").val(1);
            numberArea.find(".required:first").text("*");
          }
        })
        .change();

      $("[name=code]").change(function () {
        var code = $(this).val();
        var qrcode_id = $("[name=qrcode_id]").val();
        if (code == "") {
          $("#code-warning").text("");
        } else {
          var event_arr = [];
          event_arr["success"] = function (data) {
            $("#code-warning").text(data.message);
            if (parseInt(data.state) < 0) {
              $("#code-warning")
                .removeClass("text-success")
                .addClass("text-danger");
            }

            if (parseInt(data.state) >= 0) {
              $("#code-warning")
                .removeClass("text-danger")
                .addClass("text-success");
            }
          };

          ajax_pub_adv(
            "../api/qrcode_validate.php",
            {
              qrcode_id: qrcode_id,
              code: code,
            },
            event_arr,
            { async: true }
          );
        }
      });
    }

    if (location.pathname.indexOf("qr_type_") >= 0) {
      // 推播訊息給擁有該標籤QRCode的所有會員
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
    }

    if (location.pathname.indexOf("verify") >= 0) {
      // 推播訊息給擁有該標籤QRCode的所有會員
      $("button[id=smsNotify]").click(function (event) {
        // 找到所有被勾選的id，並合併成,字串

        // 發送的Modal資訊帶入
        var memberModal = $("#smsNotifyModal");
        memberModal.modal("show");
        memberModal.on("shown.bs.modal", function (e) {
          // do something...
          $("button[id=smsNotifyConfirm]").click(function (event) {
            var this_form = Boolean($(this).parents("form"))
              ? $(this).parents("form")
              : $("#" + $(this).attr("form"));

            var ans = prompt("請輸入 確定新增 四個字");
            if (pintech_trim(ans) === "確定新增") {
              $("button[id=smsNotifyConfirm]").prop("disabled", true);
              $("button[id=smsNotifyConfirm]").append(
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
      });
    }

    if (location.pathname.indexOf("qrcode_list") >= 0) {
      // 列表儲存排序
      $("button[name=save_qr]").click(function (event) {
        var orders = $(this).parents("tr").find("[name=orders]").val();
        var id = $(this).parents("tr").find("[name=box_list]").val();
        // 找到 update sql語法，並將orders(?1)、id(?2)帶入參數，再加密
        var sql = $("[name=orders_sql]").val();
        if (orders === "-1") {
          sql = $("[name=orders_cancel_sql]").val();
        } else if (orders === "1") {
          alert("綁定須填寫相關資料");
          document.location.href =
            "qrcode_mang.php?model=update&qrcode_id=" + id;
          return false;
        }
        var cmd = aes_decrypt(sql).replace("?1", orders);
        cmd = cmd.replace("?2", id);
        cmd = aes_encrypt(cmd);

        var temp = gettoken_value();
        var value = temp.value;
        var token = temp.token;
        document.location.href =
          "sp_command.php?cmd=" +
          cmd +
          "&value=" +
          value +
          "&token=" +
          token +
          "";
      });

      // 列表批次發送qrcode給會員
      $("button[id=sendToMember]").click(function (event) {
        // 找到所有被勾選的id，並合併成,字串
        var id = $("input[name=box_list]:checked")
          .map(function () {
            return $(this).val();
          })
          .get()
          .join(",");

        if (id !== "") {
          // 發送的Modal資訊帶入
          var memberModal = $("#sendToMemberModal");
          memberModal.modal("show");
          memberModal.on("shown.bs.modal", function (e) {
            // do something...
            $("button[id=sendToMemberConfirm]").click(function (event) {
              var memberId = $("[name=memberId]").val();
              var ans = prompt("請輸入 確定發送 四個字");
              if (pintech_trim(ans) === "確定發送") {
                $("input[name=box_list]").each(function () {
                  //loop through each checkbox
                  $(this).prop("checked", false); //uncheck
                });
                // 找到send sql解密後，帶入欲刪除的id
                var cmd = aes_decrypt($("[name=send_sql]").val()).replaceAll(
                  "?2",
                  id
                );
                cmd = cmd.replace("?1", memberId);
                // 再加密一次，送進sp_command.php
                cmd = aes_encrypt(cmd);
                var temp = gettoken_value();
                var value = temp.value;
                var token = temp.token;
                document.location.href =
                  "sp_command.php?cmd=" +
                  cmd +
                  "&value=" +
                  value +
                  "&token=" +
                  token +
                  "";
              }
            });
          });
        } else {
          alert("請先勾選欲發送的QRCode");
        }
      });
    }

    if (location.pathname.indexOf("gallery_photo_mang.php") >= 0) {
      function add_file(id, file) {
        var template =
          '<li class="media" id="uploadFile' +
          id +
          '">\n' +
          '        <div class="media-body mb-1">\n' +
          '          <p class="mb-2">\n' +
          "            <strong>" +
          file.name +
          '</strong><br/>Status: <span class="text-muted">Waiting</span>\n' +
          "          </p>\n" +
          '          <div class="progress mb-2">\n' +
          '            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" \n' +
          '              role="progressbar"\n' +
          '              style="width: 0%" \n' +
          '              aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">\n' +
          "            </div>\n" +
          "          </div>\n" +
          '          <hr class="mt-1 mb-1" />\n' +
          "        </div>\n" +
          "      </li>";

        $("#fileList").find("li.empty").fadeOut();
        $("#fileList").append(template);
      }

      function update_file_status(id, status, message) {
        $("#uploadFile" + id)
          .find("span")
          .html(message)
          .prop("class", "status text-" + status);
      }

      function update_file_progress(id, percent, color, active) {
        color = typeof color === "undefined" ? false : color;
        active = typeof active === "undefined" ? true : active;

        var bar = $("#uploadFile" + id).find("div.progress-bar");

        bar.width(percent + "%").attr("aria-valuenow", percent);
        bar.toggleClass("progress-bar-striped progress-bar-animated", active);

        if (percent === 0) {
          bar.html("");
        } else {
          bar.html(percent + "%");
        }

        if (color !== false) {
          bar.removeClass("bg-success bg-info bg-warning bg-danger");
          bar.addClass("bg-" + color);
        }
      }

      // Upload Plugin itself
      $("#drag-and-drop-zone").dmUploader({
        url: "sp_gallery_photo.php",
        dataType: "json",
        fieldName: "file0", //這裡是自己設定的
        allowedTypes: "image/*",
        extraData: {
          gallery_id: $("[name=gallery_id]").val(), //其他參數
          model: "add", //其他參數
        } /*extFilter: 'jpg;png;gif',*/,
        onInit: function () {
          // console.log('Penguin initialized :)');
        },
        onBeforeUpload: function (id) {
          // console.log('Starting the upload of #' + id);
          update_file_status(id, "uploading", "Uploading...");
        },
        onNewFile: function (id, file) {
          // console.log('New file added to queue #' + id);
          if (typeof FileReader !== "undefined") {
            var reader = new FileReader();
            var img = $('<img class="mr-3 mb-2 preview-img"/>');

            reader.onload = function (e) {
              img.attr("src", e.target.result);
            };
            /* ToDo: do something with the img! */
            reader.readAsDataURL(file);
          }
          add_file(id, file);
          $("#uploadFile" + id).prepend(img);
        },
        onComplete: function () {
          // console.log('All pending tranfers finished');
          // alert("上傳完成，請返回列表或繼續上傳")
        },
        onUploadProgress: function (id, percent) {
          // var percentStr = percent + '%';
          update_file_progress(id, percent);
        },
        onUploadSuccess: function (id, data) {
          // console.log('Upload of file #' + id + ' completed');
          // console.log('Server Response for file #' + id + ': ' + JSON.stringify(data));
          var state_tw = data.status;
          if (state_tw == 1) {
            update_file_status(id, "success", "Upload Complete");
            // update_file_progress(id, '100%');
            update_file_progress(id, 100, "success", false);
          } else {
            update_file_status(id, "danger", "Upload Error");
            // update_file_progress(id, '0%');
            update_file_progress(id, 0, "danger", false);
          }
        },
        onUploadError: function (id, data) {
          // console.log('Failed to Upload file #' + id + ': ' + JSON.stringify(data));

          update_file_status(id, "danger", "Upload Error");
          update_file_progress(id, 0, "danger", false);
        },
        onFileTypeError: function (file) {
          // console.log('File \'' + file.name + '\' cannot be added: must be an image');
        },
        onFileSizeError: function (file) {
          // console.log('File \'' + file.name + '\' cannot be added: size excess limit');
        } /*onFileExtError: function(file){
                  $.danidemo.addLog('#demo-debug', 'error', 'File \'' + file.name + '\' has a Not Allowed Extension');
                },*/,
        onFallbackMode: function (message) {
          alert("Browser not supported(do something else here!): " + message);
        },
      });
    }

    if (
      location.pathname.indexOf("advertisement_mang.php") >= 0 ||
      location.pathname.indexOf("activity_") >= 0
    ) {
      $("input[name=start_date]").daterangepicker({
        minDate: getISODateTime(now_time(), "yyyy-MM-dd"),
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: true,
        locale: {
          format: "YYYY-MM-DD HH:mm:ss",
          applyLabel: "確定",
          cancelLabel: "取消",
          fromLabel: "開始日期",
          toLabel: "結束日期",
          customRangeLabel: "自訂日期區間",
          daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
          monthNames: [
            "1月",
            "2月",
            "3月",
            "4月",
            "5月",
            "6月",
            "7月",
            "8月",
            "9月",
            "10月",
            "11月",
            "12月",
          ],
          firstDay: 1,
        },
      });

      $("input[name=end_date]").daterangepicker({
        minDate: getISODateTime(now_time(), "yyyy-MM-dd"),
        autoUpdateInput: false,
        singleDatePicker: true,
        showDropdowns: true,
        timePicker: true,
        timePicker24Hour: true,
        locale: {
          format: "YYYY-MM-DD HH:mm:ss",
          applyLabel: "確定",
          cancelLabel: "取消",
          fromLabel: "開始日期",
          toLabel: "結束日期",
          customRangeLabel: "自訂日期區間",
          daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
          monthNames: [
            "1月",
            "2月",
            "3月",
            "4月",
            "5月",
            "6月",
            "7月",
            "8月",
            "9月",
            "10月",
            "11月",
            "12月",
          ],
          firstDay: 1,
        },
      });

      $("input[name=bird_date]").daterangepicker({
        minDate: getISODateTime(now_time(), "yyyy-MM-dd"),
        autoUpdateInput: false,
        singleDatePicker: true,
        locale: {
          format: "YYYY-MM-DD",
          applyLabel: "確定",
          cancelLabel: "取消",
          fromLabel: "開始日期",
          toLabel: "結束日期",
          customRangeLabel: "自訂日期區間",
          daysOfWeek: ["日", "一", "二", "三", "四", "五", "六"],
          monthNames: [
            "1月",
            "2月",
            "3月",
            "4月",
            "5月",
            "6月",
            "7月",
            "8月",
            "9月",
            "10月",
            "11月",
            "12月",
          ],
          firstDay: 1,
        },
      });

      $("input[name=start_date]").on(
        "apply.daterangepicker",
        function (ev, picker) {
          $(this).val(picker.startDate.format("YYYY-MM-DD HH:mm:ss"));
        }
      );

      $("input[name=end_date]").on(
        "apply.daterangepicker",
        function (ev, picker) {
          $(this).val(picker.startDate.format("YYYY-MM-DD HH:mm:ss"));
        }
      );

      $("input[name=bird_date]").on(
        "apply.daterangepicker",
        function (ev, picker) {
          $(this).val(picker.startDate.format("YYYY-MM-DD"));
        }
      );
    }

    //###############以下為新增及編輯頁面 fun#######################//

    if (location.pathname.indexOf("_mang.php") >= 0) {
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

      //如果送出後，因為有必填沒填寫而被返回上一頁時，預覽圖會消失，所以要讓預覽圖再出現
      setTimeout(function () {
        $("textarea").each(function () {
          var b64 = $(this).val();
          if (b64.indexOf("base64") >= 0 && b64.indexOf("data:image") >= 0) {
            $(this)
              .prev()
              .css("background-image", 'url("' + b64 + '")');
          }
        });
      }, 500);

      // 刪除圖片, 刪除附件用
      $(document).on("click", "[file_sql]", function () {
        if (confirm("確定刪除?")) {
          // 找到元素中的update sql語法
          // 圖片刪除僅會將資料庫欄位更新為空
          var cmd = $(this).attr("file_sql");
          var temp = gettoken_value();
          var value = temp.value;
          var token = temp.token;
          document.location.href =
            "sp_command.php?cmd=" +
            cmd +
            "&value=" +
            value +
            "&token=" +
            token +
            "";
        }
      });

      $(document).on("change", "[type=checkbox]", function (e) {
        var box_arr = {}; //宣告物件
        $(this)
          .parents("td:first")
          .find("[type=checkbox]:checked")
          .each(function (i, n) {
            var key = $(this).val();
            var title = $(this).attr("title");
            box_arr[title] = key;
          });
        var str = JSON.stringify(box_arr);
        $(this).parents("td:first").find("[box_str]").val(str);
      });
      $("[type=checkbox]").change(); //自主觸發

      // 檢查必填
      $("button[name=post]").click(function (e) {
        var sum_arr = [];
        var model = $("[name=model]").val();
        var ckeditorObj = CKEDITOR.instances;
        var ckeditorArr = Object.keys(ckeditorObj);
        $(this).prev("button:reset").prop("disabled", true);
        $(this)
          .prop("disabled", true)
          .append(
            ' <span class="spinner-grow spinner-grow-sm mx-1" role="status" aria-hidden="true"></span>'
          );
        var this_form = Boolean($(this).parents("form"))
          ? $(this).parents("form")
          : $("#" + $(this).attr("form"));
        this_form.find(".is-invalid").each(function () {
          $(this).removeClass("is-invalid");
        });

        // 取得所有包含rep(必填)的元素
        $("[req]:not([readonly],[disabled]").each(function () {
          var title = $.trim($(this).attr("data-title"));
          var name = $(this).attr("name");

          if ($(this).attr("type") === "file") {
            var files = $(this).prop("files");
            if (model === "add") {
              // 新增模式 如果files長度為0
              if (files.length <= 0) {
                sum_arr.push("請檢查「" + title + "」是否已上傳?");
              }
            } else if (model === "update") {
              // 編輯模式 如果data('file')為空且files長度為0
              if (
                aes_decrypt($(this).data("file")) === "" &&
                files.length <= 0
              ) {
                sum_arr.push("請檢查「" + title + "」是否已上傳?");
              }
            }
          } else if (
            ckeditorArr.length > 0 &&
            ckeditorArr.indexOf(name) !== -1
          ) {
            var value = "";
            if (ckeditorArr.indexOf(name) !== -1) {
              value = ckeditorObj[name].getData();
            } else if (ckeditorArr.indexOf("ckeditor") !== -1) {
              value = ckeditorObj.ckeditor.getData();
            }
            if (value === "") {
              sum_arr.push("請檢查「" + title + "」是否填寫?");
              $(this).addClass("is-invalid");
            }
          } else if ($(this).attr("type") === "radio") {
            var value = $(
              "input:radio[name=" + $(this).attr("name") + "]:checked"
            ).val();
            if (value === "" || value === undefined) {
              sum_arr.push("請勾選一項「" + title + "」");
            }
          } else {
            if ($(this).val() === "") {
              sum_arr.push("請檢查「" + title + "」是否填寫?");
              $(this).addClass("is-invalid");
            } else {
              if (
                $(this).attr("type") === "email" &&
                !validateEmail($(this).val())
              ) {
                sum_arr.push("電子信箱格式錯誤");
                $(this).addClass("is-invalid");
              } else if (
                $(this).attr("name") === "mobile" &&
                !ValidateMobile($(this).val())
              ) {
                sum_arr.push("手機格式錯誤");
                $(this).addClass("is-invalid");
              } else if (
                $(this).attr("name") === "dob" &&
                !ValidateYYYYMMDD($(this).val())
              ) {
                sum_arr.push("生日格式錯誤");
                $(this).addClass("is-invalid");
              } else if (
                $(this).attr("name") === "types_option1" &&
                $(this).val() === "其他" &&
                $("[name=other0]").val() === ""
              ) {
                var title = $.trim($("[name=other0]").attr("data-title"));
                sum_arr.push("請檢查「" + title + "」是否填寫?");
                $(this).addClass("is-invalid");
              }

              if (location.pathname.indexOf("member_mang.php") >= 0) {
                if (
                  $(this).attr("name") === "account" &&
                  !ValidateID($(this).val())
                ) {
                  sum_arr.push("身分證字號格式錯誤");
                  $(this).addClass("is-invalid");
                }
              }

              if (
                location.pathname.indexOf("qrcode_mang.php") >= 0 &&
                model === "add"
              ) {
                if ($(this).attr("type") === "number") {
                  var min = parseInt($(this).attr("min"));
                  var max = parseInt($(this).attr("max"));
                  if (
                    parseInt($(this).val()) >= min &&
                    parseInt($(this).val()) <= max
                  ) {
                  } else {
                    sum_arr.push(
                      "「" + title + "」請介於 " + min + " 至 " + max
                    );
                    $(this).addClass("is-invalid");
                  }
                }
              }
            }
          }

          // 判斷
          if ($(this).hasClass("hide")) {
            var arr = Object.keys($.parseJSON($(this).val()));
            if (arr.length === 0) {
              sum_arr.push("請至少勾選一項" + title);
              $(this).addClass("is-invalid");
            }
          }
        });

        if (sum_arr.length > 0) {
          Loading(false);
          $(this).prop("disabled", false);
          $(this).prev("button:reset").prop("disabled", false);
          $(this).children("span").remove();
          this_form.find('[data-title="' + sum_arr[0] + '"]').focus();
          sum_arr = sum_arr.join("\n");
          alert(sum_arr);
          e.preventDefault ? e.preventDefault() : (e.returnValue = false);
          return false;
        } else {
          if (
            location.pathname.indexOf("qrcode_mang.php") >= 0 &&
            model === "add"
          ) {
            var yes = confirm("產生QRCode會花一些時間，請耐心等候。");
            if (yes) {
              Loading(true);
              this_form.submit();
            } else {
              $(this).prop("disabled", false);
              $(this).prev("button:reset").prop("disabled", false);
              $(this).children("span").remove();
              e.preventDefault ? e.preventDefault() : (e.returnValue = false);
              return false;
            }
          } else {
            this_form.submit();
          }
        }
      });

      /**
       * This will fix the CKEDITOR not handling the input[type=reset] clicks.
       */
      if (typeof CKEDITOR != "undefined") {
        $("form").on("reset", function (e) {
          if ($(CKEDITOR.instances).length) {
            for (var key in CKEDITOR.instances) {
              var instance = CKEDITOR.instances[key];
              if (
                $(instance.element.$).closest("form").attr("name") ==
                $(e.target).attr("name")
              ) {
                instance.setData(instance.element.$.defaultValue);
              }
            }
          }
        });
      }

      // 顯示檔案名稱
      $(".filesupload[data-file]").each(function () {
        var fileInput = $(this);
        var file_data = aes_decrypt(fileInput.data("file"));
        var text = fileInput.parents("td:first").find("#filename");
        if (file_data !== "") {
          text
            .addClass("text-danger")
            .html(
              '<i class="fa-solid fa-circle-exclamation"></i> 若重新上傳將會覆蓋原始檔案...'
            );
        } else {
          text.addClass("text-muted").text("尚未選擇檔案...");
        }
        fileInput.on("change", function () {
          var filename = $(this).val();
          text.removeClass("text-danger").removeClass("text-muted");
          if (/^\s*$/.test(filename)) {
            if (file_data !== "") {
              text
                .addClass("text-danger")
                .html(
                  '<i class="fa-solid fa-circle-exclamation"></i> 若重新上傳將會覆蓋原始檔案...'
                );
            } else {
              text.addClass("text-muted").text("尚未選擇檔案...");
            }
          } else {
            text
              .addClass("text-muted")
              .text(filename.replace("C:\\fakepath\\", ""));
          }
        });
      });
    }

    //###############以下為列表頁面 fun#######################//
    if (location.pathname.indexOf("_list.php") >= 0 || location.pathname.indexOf("verify") >= 0 ) {
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

      // 列表批次刪除
      $("button[name=box_del]").click(function (event) {
        // 找到所有被勾選的id，並合併成,字串
        var id = $("input[name=box_list]:checked")
          .map(function () {
            return $(this).val();
          })
          .get()
          .join(",");

        if (id != "") {
          // alert("相關資料也會統一刪除")
          // 為了避免使用者無腦刪除，會希望他們輸入字樣，知道自己在做刪除動作
          var ans = prompt("請輸入 確定刪除 四個字");

          if (pintech_trim(ans) === "確定刪除") {
            $("input[name=box_list]").each(function () {
              //loop through each checkbox
              $(this).prop("checked", false); //uncheck
            });
            // 找到delete sql解密後，帶入欲刪除的id
            var cmd = aes_decrypt($("[name=del_sql]").val()).replaceAll(
              "?1",
              id
            );
            // 再加密一次，送進sp_command.php
            cmd = aes_encrypt(cmd);
            var temp = gettoken_value();
            var value = temp.value;
            var token = temp.token;
            document.location.href =
              "sp_command.php?cmd=" +
              cmd +
              "&value=" +
              value +
              "&token=" +
              token +
              "";
          }
        } else {
          alert("請先勾選欲刪除的項目");
        }
      });

      // 列表儲存排序
      $("button[name=save]").click(function (event) {
        var orders = $(this).parents("tr").find("[name=orders]").val();
        var id = $(this).parents("tr").find("[name=box_list]").val();
        // 找到 update sql語法，並將orders(?1)、id(?2)帶入參數，再加密
        var cmd = aes_decrypt($("[name=orders_sql]").val()).replace(
          "?1",
          orders
        );
        cmd = cmd.replace("?2", id);
        cmd = aes_encrypt(cmd);

        var temp = gettoken_value();
        var value = temp.value;
        var token = temp.token;
        document.location.href =
          "sp_command.php?cmd=" +
          cmd +
          "&value=" +
          value +
          "&token=" +
          token +
          "";
      });

      // 列表上方的篩選DOM
      // 按下搜尋按鈕時，取得有search_ref的所有元素，將所有值組合成url param，並導向
      $("[name=search_button]").click(function (event) {
        var str = $("[search_ref]")
          .map(function () {
            return $(this).attr("name") + "=" + $(this).val();
            // 拼成網址參數字串
          })
          .get()
          .join("&");
        document.location.href = "?" + str;
      });

      // 列表excel匯出
      $("[name=excel_button]").click(function (event) {
        var cmd = $("[name=excel_sql]").val();
        var sp = $("[name=excel_sql]").attr("sp");
        var temp = gettoken_value();
        var value = temp.value;
        var token = temp.token;
        document.location.href =
          sp + "?cmd=" + cmd + "&value=" + value + "&token=" + token + "";
      });

      // 列表清除篩選條件
      $("[name=clear_filter]").click(function (event) {
        var current = window.location.href;
        current = current.split("?")[0];
        document.location.href = current;
      });

      // 複製場館id
      $('[data-trigger="copy"]').on("click", function (e) {
        var temp = $("<input>"); // 建立input物件
        $("body").append(temp); // 將input物件增加到body
        var url = $(this).data("url"); // 取得要複製的連結
        temp.val(url).select(); // 將連結加到input物件value
        document.execCommand("copy"); // 複製
        temp.remove(); // 移除input物件
        alert("已複製場館ID");
      });

      // 自動搜尋
      $("[search_ref]").each(function () {
        $(this).change(function () {
          $("[name=search_button]").trigger("click");
        });
      });

      // 列表zip下載
      $("[name=zip_button]").click(function (event) {
        var cmd = $("[name=zip_sql]").val();
        var sp = $("[name=zip_sql]").attr("sp");
        var temp = gettoken_value();
        var value = temp.value;
        var token = temp.token;
        window.open(
          sp + "?cmd=" + cmd + "&value=" + value + "&token=" + token + "",
          "_blank"
        );
        // document.location.href = sp + "?cmd=" + cmd + "&value=" + value + "&token=" + token + "";
      });
    }
  }
});

function ValidatePasswd2(str) {
  var re = /^(?=.*[a-z])(?=.*\d).{8,12}$/;
  return re.test(str);
}

function Loading(toggle) {
  if (toggle) {
    $(".loading").removeClass("hidden");
  } else {
    $(".loading").addClass("hidden");
  }
}
