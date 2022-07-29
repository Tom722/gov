$(function () {
    $.fn.tooltip.Constructor.DEFAULTS.sanitize = false;
    $.fn.popover.Constructor.DEFAULTS.sanitize = false;

    var parser = new HyperDown();
    window.marked = function (text) {
        return parser.makeHtml(text);
    };
    window.isMobile = !!("ontouchstart" in window);

    //内容中的图片点击事件
    $(document).on("click", ".wysiwyg img", function () {
        var that = this;
        var data = [];
        var index = 0;
        $(".wysiwyg img").each(function (i, j) {
            if (that == this) {
                index = i;
            }
            data.push({
                "src": $(this).attr("src") //原图地址
            });
        });
        layer.photos({
            photos: {
                "start": index, "data": data
            },
            anim: 0,
            scrollbar: true,
            full: false,
            closeBtn: 1
        });
    });

    //关闭Popover
    $(document).on("click", "[data-dismiss='popover']", function () {
        $(this).closest(".popover").prev().popover("hide");
    });

    //对相对地址进行处理
    $.ajaxSetup({
        beforeSend: function (xhr, setting) {
            var public = Config.__PUBLIC__.replace(/(\/*$)/g, "");
            var url = setting.url;
            if (url.substr(0, 8) === "/addons/") {
                url = public + url;
            } else if (url.substr(0, 1) != '/' && url.substr(0, 1) != '?' && url.substr(0, 7) != 'http://' && url.substr(0, 8) != 'https://') {
                url = public + (Config.url_domain_deploy ? '/' : '/addons/ask/') + url;
            }
            setting.url = url;
        }
    });

    //如果是PC则移除navbar的dropdown点击事件
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobi/i.test(navigator.userAgent)) {
        $("#navbar-collapse [data-toggle='dropdown']").removeAttr("data-toggle");
    } else {
        $(".navbar-nav ul li:not(.dropdown-submenu):not(.dropdown) a").removeAttr("data-toggle");
    }

    //展开评论
    $(document).on("click", ".btn-comment", function () {
        var that = this;
        var subcomments = $(".sub-comments", $(this).closest(".comment-footer"));
        if (subcomments.hasClass("collapsed")) {
            $.ajax({
                url: "ajax/get_comment_list",
                dataType: 'json',
                data: {id: $(that).data("id"), type: $(that).data("type")},
                success: function (ret) {
                    subcomments.html(ret);
                    ASK.render.editor($("textarea[name=content]", subcomments));
                },
                complete: function (xhr) {
                    var token = xhr.getResponseHeader('__token__');
                    if (token) {
                        $("input[name='__token__']").val(token);
                    }
                }
            });
            subcomments.addClass("expanded");
            subcomments.removeClass("collapsed");
        } else {
            subcomments.addClass("collapsed");
            subcomments.removeClass("expanded");
        }
        subcomments.removeClass("clicked");
        subcomments.addClass("clicked");
    });

    //追加评论
    $(document).on("click", ".btn-appendcomment", function () {
        var form = $(this).closest(".comment-post").find("form");
        form.show();
        ASK.render.editor($("textarea[name=content]", form));
        $(this).hide();
        $("textarea[name=content]", form).focus();
    });

    //关注
    $(document).on("click", ".btn-attention", function () {
        var that = this;
        ASK.api.ajax({
            url: "attention/" + ($(that).hasClass("followed") ? "delete" : "create"),
            dataType: 'json',
            data: {id: $(that).data("id"), type: $(that).data("type")},
        }, function (data, ret) {
            $("span", that).html($(that).hasClass("followed") ? ($(that).data("type") === "question" ? "关注问题" : "关注TA") : "已关注");
            //移除
            $(that).siblings(".btn-attention").removeClass("followed");
            if ($(that).hasClass("followed")) {
                $(that).removeClass("followed");
            } else {
                $(that).addClass("followed");
            }
            return false;
        });
        return false;
    });

    //收藏
    $(document).on("click", ".btn-collection", function () {
        var that = this;
        ASK.api.ajax({
            url: "collection/" + ($(that).hasClass("collected") ? "delete" : "create"),
            dataType: 'json',
            data: {id: $(that).data("id"), type: $(that).data("type")},
        }, function (data, ret) {
            $("span", that).html($(that).hasClass("collected") ? "收藏" : "已收藏");
            //移除
            $(that).siblings(".btn-collection").removeClass("collected");
            if ($(that).hasClass("collected")) {
                $(that).removeClass("collected");
            } else {
                $(that).addClass("collected");
            }
            return false;
        });
        return false;
    });

    //投票
    $(document).on("click", ".btn-vote", function () {
        var that = this;
        ASK.api.ajax({
            url: "vote/" + ($(that).hasClass("voted") ? "delete" : "create"),
            dataType: 'json',
            data: {id: $(that).data("id"), type: $(that).data("type"), value: $(that).data("value")},
        }, function (data, ret) {
            $(that).siblings(".btn-vote").andSelf().find("span").text(ret.data.voteup);
            //移除
            $(that).siblings(".btn-vote").removeClass("voted");
            if ($(that).hasClass("voted")) {
                $(that).removeClass("voted");
            } else {
                $(that).addClass("voted");
            }
            return false;
        });
        return false;
    });

    //感谢
    $(document).on("click", ".btn-thanks", function () {
        if ($(this).data("st")) {
            clearTimeout($(this).data("st"));
        }
        $("[data-toggle='popover']").popover('hide');
        var that = this;
        layer.open({
            type: 1,
            area: isMobile ? 'auto' : ["450px", "530px"],
            zIndex: 1031,
            title: '感谢',
            content: template("thankstpl", $(that).data()),
            success: function (layero, index) {

            }
        });
        return false;
    });

    //举报
    $(document).on("click", ".btn-report", function () {
        var that = this;
        layer.open({
            type: 1,
            area: isMobile ? 'auto' : ["450px", "380px"],
            zIndex: 1031,
            title: '举报',
            content: template("reporttpl", $(that).data()),
            success: function (layero, index) {
                ASK.api.form($("form", layero), function (data, ret) {
                    layer.close(index);
                });
            }
        });
        return false;
    });

    //点击分享
    $(document).on("click", ".btn-share", function () {
        var url = $(this).data("url"),
            title = $(this).data("title");
        url = url ? url : location.href;
        title = title ? title : document.title;
        layer.open({
            type: 1,
            area: isMobile ? 'auto' : ["450px", "380px"],
            zIndex: 1031,
            title: '分享', //不显示标题
            btn: ["关闭"],
            btnAlign: "c",
            content: template("sharetpl", {url: url, title: title})
        });
    });

    //修改评论
    $(document).on("click", ".btn-editcomment", function () {
        var that = this;
        ASK.api.ajax({
            url: "comment/update",
            type: 'get',
            data: {id: $(that).data("id")}
        }, function (data, ret) {
            layer.open({
                title: '编辑评论',
                type: 1,
                zIndex: 1031,
                area: isMobile ? 'auto' : ["600px", "440px"],
                content: data,
                success: function (layero, index) {
                    ASK.render.editor($("#c-content", layero));
                    ASK.api.form($("form", layero), function (data, ret) {
                        $(that).closest(".media-body").find(".wysiwyg").html(data);
                        layer.closeAll();
                        layer.msg("编辑成功", {icon: 1});
                        return false;
                    });
                }
            });
            return false;
        });
        return false;
    });

    //点击回复
    $(document).on("click", ".btn-reply", function () {
        var form = Config.controllername === 'article' ? $(this).closest(".comment-footer").find(".comment-post").find("form") : $(this).closest(".comment-list").siblings(".comment-post").find("form");
        if (form.prev().is(".appendcomment")) {
            $(".btn-appendcomment", form.prev()).trigger("click");
        }
        $('input[name="reply_user_id"]', form).val($(this).data("user-id"));
        var content = $('textarea[name="content"]', form).val();
        content = content.replace(/@(\w+)\s/, '');
        content = "@" + $(this).data("user-username") + " " + content;
        $('textarea[name="content"]', form).val(content).focus();
        if (Config.editormode !== 'markdown') {
            $('textarea[name="content"]', form).summernote("pasteHTML", "@" + $(this).data("user-username") + "&nbsp;</p>");
        }
        return false;
    });

    //删除
    $(document).on("click", ".btn-delete", function () {
        var that = this;
        layer.confirm("确认删除?", {title: '温馨提示', icon: 3}, function () {
            ASK.api.ajax({
                url: $(that).attr("href")
            }, function (data, ret) {
                ASK.api.msg(ret.msg, ret.url);
                return false;
            });
        });
        return false;
    });

    //转移
    $(document).on("click", ".btn-transfer", function () {
        var that = this;
        layer.confirm("确认转移？", {title: '温馨提示', icon: 3}, function () {
            ASK.api.ajax($(that).attr("href"), function (data, ret) {
                ASK.api.msg(ret.msg, ret.url);
                return false;
            })
        });
        return false;
    });

    //选择金额
    $(document).on("click", ".btn-money", function () {
        var form = $(this).closest("form");
        $("input[name='money'],input[name='price'],input[name='score']", form).val($(this).data("money"));
        $(".btn-money", form).removeClass("active");
        $(this).addClass("active");
        return false;
    });

    //Tooltip
    if (!('ontouchstart' in document.documentElement)) {
        $('body').tooltip({selector: '[data-toggle="tooltip"]'});
    }

    //浮动窗口
    $(document).on("mouseenter", '[data-toggle="popover"]', function () {
        var that = this;
        if ($(that).data("st")) {
            clearTimeout($(that).data("st"));
        }
        var url = $(that).data("url");
        var id = $(that).data("id");
        var type = $(that).data("type");
        if (!url) {
            if (type === 'user') {
                url = "ajax/get_user_info";
            } else if (type === 'addon') {
                url = "ajax/get_addon_info";
            }
        }
        var st = setTimeout(function () {
            $.ajax({
                url: url,
                data: {id: id, type: type},
                success: function (response) {
                    if ($(that).hasClass("btn-thanks") && $(".thanks-list a", $(response)).size() === 0) {
                        return;
                    }
                    $(that).popover({
                        trigger: "manual", html: true, content: response, container: document.body
                    });
                    $(that).popover('show');
                    $(".popover").on("mouseleave", function () {
                        $(that).popover('hide');
                    });
                }
            });
        }, 300);
        $(that).data("st", st);
    }).on("mouseleave", '[data-toggle="popover"]', function () {
        var that = this;

        if ($(that).data("st")) {
            clearTimeout($(that).data("st"));
        }
        setTimeout(function () {
            if (!$(".popover:hover").length) {
                $(that).popover("hide");
            }
        }, 300);
    });

    var $question = $(".question-list");
    //无限加载模式
    if ($question.size() > 0 && Config.loadmode === 'infinite') {

        // 加载更多
        $(document).on("click", ".btn-loadmore", function () {
            var that = this;
            var page = parseInt($(this).data("page"));
            page++;
            $(that).prop("disabled", true);
            $.ajax({
                dataType: 'json',
                data: {page: $(that).data("page")},
                success: function (data, ret) {
                    $(data).insertBefore($(that).parent());
                    $(that).remove();
                },
                error: function () {
                    layer.msg("加载数据失败");
                }
            });
            return false;
        });

        //滚动加载更多
        $(window).scroll(function () {
            var loadmore = $(".btn-loadmore");
            if (loadmore.size() > 0 && !loadmore.prop("disabled") && (loadmore.data("autoload") === undefined || loadmore.data("autoload"))) {
                if ($(window).scrollTop() - loadmore.height() > loadmore.offset().top - $(window).height()) {
                    loadmore.trigger("click");
                }
            }
        });
        setTimeout(function () {
            if ($(window).scrollTop() > 0) {
                $(window).trigger("scroll");
            }
        }, 500);
    }

    //更多评论
    $(document).on("click", ".btn-morecomment", function () {
        var that = this;
        var subcomments = $(this).closest(".sub-comments").size() > 0 ? $(this).closest(".sub-comments") : document;
        $.ajax({
            url: "ajax/get_comment_list",
            dataType: 'json',
            data: {id: $(that).data("id"), type: $(that).data("type"), page: $(that).data("page")},
            success: function (ret) {
                if (!ret) {
                    $(that).remove();
                    return;
                }
                $(".comment-list", subcomments).append(ret);
                $(that).data("page", parseInt($(that).data("page")) + 1);
            }
        });
    });

    //更多设置
    $(document).on("click", ".btn-config", function () {
        var that = this;
        ASK.api.ajax({
            url: $(that).attr("href"),
            type: 'get',
        }, function (data, ret) {
            layer.open({
                title: '更多设置',
                type: 1,
                zIndex: 1031,
                area: isMobile ? 'auto' : ["350px", "320px"],
                content: data,
                success: function (layero, index) {
                    $('.colorpicker', layero).colorpicker({
                        color: function () {
                            var color = $(this.eve).prev().val();
                            return color;
                        }
                    }, function (event, obj) {
                        $(this.eve).prev().val('#' + obj.hex);
                    }, function (event) {
                        $(this.eve).prev().val('');
                    });

                    $("form", layero).on("submit", function () {
                        ASK.api.ajax({
                            url: $(this).attr("action"),
                            data: $(this).serialize()
                        }, function (data, ret) {
                            layer.closeAll();
                            ASK.api.msg(ret.msg, function () {
                                location.reload();
                            });
                            return false;
                        });
                        return false;
                    });
                }
            });
            return false;
        });
        return false;
    });

    //返回顶部
    $(document).on("click touchend", ".backtotop", function () {
        $('body,html').animate({scrollTop: 0}, 300);
    });

    //滚动后显示返回顶部
    $(window).scroll(function () {
        if ($(this).scrollTop() > $("#header").height()) {
            $(".floatbtn-item-top").fadeIn();
        } else {
            $(".floatbtn-item-top").fadeOut();
        }
    });

    //粘贴上传图片
    $.fn.pasteUploadImage.defaults = $.extend(true, $.fn.pasteUploadImage.defaults, {
        fileName: "file",
        appendMimetype: false,
        ajaxOptions: {
            url: Config.upload.uploadurl,
            beforeSend: function (jqXHR, settings) {
                if (typeof settings.data === 'object') {
                    for (var key in Config.upload.multipart) {
                        settings.data.append(key, Config.upload.multipart[key]);
                    }
                } else {
                    $.extend(settings.data, Config.upload.multipart);
                }
                return true;
            }
        },
        success: function (data, filename, file) {
            var url;
            if (typeof data.data !== 'undefined' && typeof data.data.url !== 'undefined') {
                url = data.data.url;
            } else if (typeof data.url !== 'undefined') {
                url = data.url;
            } else if (typeof data.key !== 'undefined') {
                url = '/' + data.key;
            }
            $(this).insertToTextArea(filename, Config.upload.cdnurl + url);
            return false;
        },
        error: function (data, filename, file) {
            layer.msg("上传错误");
        }
    });

    //立即支付
    $(document).on("click", ".btn-paynow", function () {
        var that = this;
        var currency = $(that).data("currency");
        var price = $(that).data("price");
        var score = $(that).data("score");
        var data = {id: $(that).data("id"), type: $(that).data("type"), price: price, score: score, currency: currency};
        if (currency == 'score') {
            layer.confirm("确认使用" + score + "积分?", {icon: 3}, function () {
                ASK.api.ajax({
                    url: 'order/submit',
                    data: data
                }, function (data, ret) {
                    ASK.api.msg(ret.msg, function () {
                        location.reload();
                    });
                });
            });
        } else {
            layer.open({
                title: "立即支付",
                type: 1,
                area: isMobile ? 'auto' : ["450px", "280px"],
                zIndex: 99,
                content: template("paynowtpl", data),
                success: function (layero) {

                }
            });
        }
    });

    //支付提交和感谢支付提交
    $(document).on("submit", "form#paynow-form,form#thanks-form", function () {
        var form = $(this);
        if ($("input[name=paytype]:checked", form).val() === 'balance') {
            ASK.api.ajax({
                url: form.attr("action"),
                data: form.serialize()
            }, function (data, ret) {
                ASK.api.msg(ret.msg, function () {
                    location.reload();
                });
                return false;
            });
            return false;
        } else {
            layer.closeAll();
            layer.confirm("请你支付完成后根据你的支付状态进行操作", {
                icon: 0,
                title: "温馨提示",
                btn: ["支付成功", "支付失败"]
            }, function () {
                location.reload();
            });
            return true;
        }
    });

    //发送私信
    $(document).on("click", ".btn-message", function () {
        var that = this;

        layer.prompt({
            formType: 2,
            title: "发送私信",
            success: function (layero, index) {
                $("textarea", layero).prop("placeholder", "请输入私信内容");
            }
        }, function (value, index) {
            ASK.api.ajax({
                url: "message/post",
                data: {id: $(that).data("id"), content: value}
            }, function (data, ret) {
                layer.close(index);
            });
        });
    });

    //通知消息删除
    $(document).on("click", ".belling ul li > em", function () {
        var that = this;
        ASK.api.ajax({
            url: $(that).prev().attr("href"),
            data: {act: "markall"}
        }, function (data, ret) {
            var ul = $(that).closest("ul");
            $(that).closest("li").remove();
            if ($("li", ul).size() === 0) {
                ul.prev().removeClass("unread");
                ul.remove();
            }
            return false;
        });
    });

    //移动端
    if (isMobile) {
        //折叠菜单
        $(document).on("click", ".comment-toolbar .btn-expand", function () {
            if (!$(this).hasClass("open")) {
                $(".comment-toolbar .expand").hide();
            }
            $(this).toggleClass("open", !$(this).siblings(".expand").is(":visible"));
            $(this).siblings(".expand").toggle();
        }).on("click touch", function (e) {
            if (!$(e.target).parents().addBack().is('.btn-expand')) {
                $(".comment-toolbar .expand").hide();
                $(".comment-toolbar .btn-expand").removeClass("open");
            }
        });
    }

    ASK.render.audio();
    ASK.render.editor("#answercontent");
    ASK.render.question("#searchinput");
    ASK.render.countdown(".countdown");

    //优化z-index
    $("#searchinput").data("sc").css("z-index", 1031);
    //展开搜索框
    $("#searchinput").focus(function () {
        $(this).closest("form").addClass("searching");
    }).blur(function () {
        var that = this;
        setTimeout(function () {
            $(that).closest("form").removeClass("searching");
        });
    });
});
