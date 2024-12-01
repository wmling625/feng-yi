// 警示重置
$("input,textarea").focus(function () {
    $(this).closest(".warning-box").find(".alert-text").removeClass("show")
    $(this).removeClass("alert-border");
})
$("select,input").on('change', function () {
    $(this).closest(".warning-box").find(".alert-text").removeClass("show")
    $(this).removeClass("alert-border");
})
$(document).on("click", '#region_list label', function () {
    $(this).closest(".warning-box").find(".alert-text").removeClass("show")
    $(this).removeClass("alert-border");
})
$(document).on('click', '.warning-box label', function () {
    $(this).closest(".warning-box").find(".alert-text").removeClass("show")
    $(this).removeClass("alert-border");
})

if ($(".checkthisform").length > 0) {
    $(document).on('click', ".submit-btn", function (e) {
        e.preventDefault();
        var sum_arr = [];
        var this_form = Boolean($(this).parents('form')) ? $(this).parents('form') : $('#' + $(this).attr('form'));

        this_form.find("[req=Y]").each(function () {
            var tooltips = $(this).attr("data-tooltip");
            var title = Boolean(tooltips) ? $.trim($(this).attr("data-tooltip")) : $.trim($(this).attr("title"));
            if ($(this).val() == "" || $(this).val() == null) {
                $(this).addClass("alert-border");
                $(this).closest(".warning-box").find(".alert-text").addClass("show");

                sum_arr.push(title);
            } else if ((this.className).indexOf("check-color") >= 0) {
                if ($(".check-color:checked").val() == null || $(".check-color:checked").val() == "") {
                    $(this).addClass("alert-border");
                    $(this).closest(".warning-box").find(".alert-text").addClass("show");
                    sum_arr.push(title);
                }
            } else if ((this.id).indexOf("privacy") >= 0) {
                if ($("#privacy:checked").val() == null || $("#privacy:checked").val() == "") {
                    $(this).addClass("alert-border");
                    $(this).closest(".warning-box").find(".alert-text").addClass("show");
                    sum_arr.push(title);
                }
            } else if ((this.name).indexOf("tel") >= 0 && ValidateMobile($(this).val()) == false) {
                $(this).addClass("alert-border");
                $(this).closest(".warning-box").find(".alert-text").addClass("show");
                sum_arr.push("手機格式錯誤");
            } else if ((this.id).indexOf("email") >= 0) {
                if (validateEmail($(this).val()) == false) {
                    $(this).addClass("alert-border");
                    $(this).closest(".warning-box").find(".alert-text").addClass("show");
                    sum_arr.push(title);
                }
            }
        })

        if (sum_arr.length > 0) {
            $('html, body').animate({
                scrollTop: $('.alert-text.show:first').offset().top - 200
            }, 500);


        } else {
            this_form.submit();
            // if ((location.pathname).indexOf("fill_in.") >= 0) {
            //     location.href = 'code_list.php'; // edit by 田 2022-06-30
            // } else if ((location.pathname).indexOf("login.") >= 0) {
            //     location.href = 'code_list.php' // edit by 田 2022-06-30
            // } else if ((location.pathname).indexOf("edit.") >= 0) {
            //     location.href = 'code_list.php' // edit by 田 2022-06-30
            // }


        }

    })
}

if ((location.pathname).indexOf("fill_in") >= 0 ||
    (location.pathname).indexOf("login") >= 0) {
    // 20220725新增 by 云
    $('input.text-uppercase').keyup(function () {
        $(this).val($(this).val().toUpperCase());
    });

    // 2022-07-06 註解 by 田
    $("#mobile").on('keyup', function () {
        if ($(this).val() !== "") {
            $(".progress-btn").removeClass("disabled")
        } else {
            $(".progress-btn").addClass("disabled")
        }
    })

    // 2022-07-01 註解 by 田
    $(".progress-btn").click(function () {
        var sum_arr = [];
        $(this).closest(".input-group").find("input").each(function (i, n) {
            var title = $.trim($(this).attr("title"));
            if ($("#mobile").val() == undefined || $("#mobile").val() == "") {
                $(this).closest(".warning-box").find(".alert-text").addClass("show");
                sum_arr.push(title)
            } else if ((this.name).indexOf("tel") >= 0 && ValidateMobile($(this).val()) == false) {
                $(this).closest(".warning-box").find(".alert-text").addClass("show");
                sum_arr.push("手機格式錯誤");
            }
        })
        if (sum_arr.length > 0) {
            sum_arr = $.unique(sum_arr); //消除重複的陣列元素
            sum_arr = sum_arr.join("、");
        } else {
            $(this).addClass("wait");
            $(this).attr("disabled", true);
            var $this = $(this);
    
            var t = 60;
            var intervalID = setInterval(function () {
                $(".progress-btn span").html('(' + t + ')');
                t = t - 1;
                if (t == "-1") {
                    clearInterval(intervalID);
                    $(".progress-btn span").html("");
                    $this.removeClass("wait");
                    $this.attr("disabled", false);
                    return false;
                }
            }, 1000);
        }
    
    })

    // 2022-07-04 註解 by 田
    $(document).on("click", "#sms-btn", function () {
        var sumarr = [];
        $(".st-1").find("input").each(function () {
            if ($(this).val() == "") {
                $(this).closest(".warning-box").find(".alert-text").addClass("show");
                $(this).addClass("alert-border");
                sumarr.push("有空白欄位")
            }
        })
        if (sumarr.length > 0) {
            sumarr = sumarr.join("、");
    
        } else {
            $('#sms-success').modal('show');
            setTimeout(function () {
                $('#sms-success').modal('hide');
            }, 1200);
        }
    
        $('#sms-success').on("hidden.bs.modal", function () {
            $("#mobile").attr("readonly", true);
            $("#sms-input").attr("readonly", true);
            $(".st-2").removeClass("hidden");
            $(".progress-btn").addClass("hidden");
    
    
            $("#mobile").addClass("okay");
            $(".okay-text").addClass("show");
            $(".sms-form-item").addClass("hidden")
        })
        if ($("#sms-input").val() !== "" && $("#mobile").val() !== "") {
            $("#mobile").attr("readonly",true);
            $("#sms-input").attr("readonly",true);
            $(".st-2").removeClass("hidden");
            $(".progress-btn").addClass("hidden");
            $('#sms-success').modal('show');
    
    
            $("#mobile").addClass("okay");
            $(".okay-text").addClass("show");
            $(".sms-form-item").addClass("hidden")
    
            setTimeout(function() {
                $('#sms-success').modal('hide');
            }, 1200);
        }else {
            $(this).closest(".warning-box").find(".alert-text").addClass("show");
            $(this).addClass("alert-border");
        }
    })

}

if ($(".preview-outter").length > 0) {
    $(document).on("click", ".del-btn", function () {
        $(this).prev("textarea").val("");
        $(this).siblings(".preview-outter").find(".preview-label").css("background-image", "")
    })
}

if ((location.pathname).indexOf("info") >= 0) {
    // 圖片輪播
    var swiper = new Swiper(".photo-swiper", {
        pagination: {
            el: ".photo-pagination",
            clickable: true
        },
    });

    //   留言
    $(document).on('click', '#comment-btn', function () {
        $(this).addClass("disabled");
        $(".comment-box").removeClass("hidden")
    })

    // 還沒註冊linebot的人alert他去註冊
    $(document).on('click', '#un-btn', function () {
        $('#un-line').modal('show');
    })
}

if ($(".ad-swiper").length > 0) {
    // 廣告
    var ad_swiper = new Swiper(".ad-swiper", {
        autoHeight: true,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        effect: "fade",

    });
}

if ((location.pathname).indexOf("code_list.") >= 0) {
    // 2022-07-04 註解 by 田
    // $(document).on("click", ".del-btn", function () {
    //     if (confirm("確認註銷此筆QR CODE？") == false) {
    //         return false
    //     } else {
    //         $(this).closest(".item").remove()
    //     }
    // })
}

if ((location.pathname).indexOf("comment") >= 0) {
    lightbox.option({
        'resizeDuration': 100,
        'showImageNumberLabel': false,
        'fadeDuration': 200
    })

    $(".reply-box textarea").on("keyup", function () {
        if ($(this).val() !== "") {
            $(this).next().children(".reply-btn").removeClass("disabled")
        } else {
            $(".progress-btn").addClass("disabled")
        }
    })
    $(document).on("click", ".reply-btn", function () {
        var textval = $(this).closest(".reply-box").children("textarea").val();
        if (textval !== "") {
            $(this).closest(".reply-box").addClass("hidden");
            $(this).closest(".reply-box").next(".my-reply").removeClass("hidden")
        }
    })
    // 2022-08-01 add by 田
    $(document).on("click", ".editopen-btn", function () {
        $(this).addClass("hidden");
        $(this).closest(".my-reply").addClass("hidden")
        $(".reply-box").removeClass("hidden")
    })
}

if ((location.pathname).indexOf("comment_visitor_edit.") >= 0) {
    $(document).on("click", ".editopen-btn", function () {
        $(this).addClass("hidden");
        $(this).closest(".visitor-box").addClass("hidden")
        $(".visitor-reply-box").removeClass("hidden")
    })
    $(".reply-box textarea").on("keyup", function () {
        if ($(this).val() !== "") {
            $(this).next().children(".reply-btn").removeClass("disabled")
        }
    })
    $(document).on("click", ".reply-btn", function () {
        var textval = $(this).closest(".reply-box").children("textarea").val();
        var thisday = getISODateTime(now_time(), "yyyy.MM.dd");
        if (textval !== "") {
            $(this).closest(".reply-box").addClass("hidden");
            $(".visitor-box .textbox").html(textval)
            $(".date").html(thisday)
            $(this).closest(".reply-box").prev(".visitor-box").removeClass("hidden");

        }
    })
}

if ($(".video-section").length > 0) {
    var player = videojs('videobox', {
        autoplay: true,
        muted: true,
        loop: true,
        fluid: true
    });

    document.body.addEventListener('click', function () {
        player.muted(false);

    }, true);
    // $('video')[0].play();


    // $(document).on('click','#unmute-video',function() {
    //     player.play();
    // })


    // $("#pic-modal").modal("show")
    // $(document).on('click',function () {
    //     $('video')[0].play();
    //     $('video')[0].prop('muted',false);
    // })
}

// 2022-07-26 移開~ by 田
if ($("#pic-modal").length > 0) {
    $("#pic-modal").modal("show")
}
