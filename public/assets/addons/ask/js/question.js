$(function () {

    //采纳提示
    if ($(".best-answer").size() == 0 && Config.user && Config.user.id == $(".about-author").data("id")) {
        if ($(".answer-list > .comment").length == 0) {
            if (!ASK.api.storage("tips.reward")) {
                setTimeout(function () {
                    $(".article-header .comment-toolbar .btn-reward").popover({
                        placement: 'bottom',
                        html: true,
                        title: '',
                        content: '<p>如果长时间没有小伙伴解答，建议你增加悬赏或补充完善你的问题</p><div class="text-right"><a href="javascript:;" class="btn btn-primary btn-xs btn-invitetips">明白了</a></div>',
                        trigger: 'manual'
                    }).popover('show');
                    $(document).on("click", ".btn-invitetips", function () {
                        ASK.api.storage("tips.reward", true);
                        var reward = $(this).closest(".comment-toolbar").find((".btn-reward"));
                        var thanks = $(this).closest(".comment-toolbar").find((".btn-invite"));
                        reward.popover('hide');
                        thanks.popover({
                            placement: 'bottom',
                            html: true,
                            title: '',
                            content: '<p>你还可以邀请认证的专家或小伙伴来解答你的问题</p><div class="text-right"><a href="javascript:;" class="btn btn-primary btn-xs" data-dismiss="popover">关闭</a></div>',
                            trigger: 'manual'
                        }).popover('show');
                    });
                }, 100);
            }
        } else {
            if (!ASK.api.storage("tips.adopt")) {
                setTimeout(function () {
                    $(".answer-list .comment-toolbar:first .btn-adopt").popover({
                        placement: 'bottom',
                        html: true,
                        title: '',
                        content: '<p>如果小伙伴的回答帮你解答了问题，别忘了采纳</p><div class="text-right"><a href="javascript:;" class="btn btn-primary btn-xs btn-adopttips">明白了</a></div>',
                        trigger: 'manual'
                    }).popover('show');
                    $(document).on("click", ".btn-adopttips", function () {
                        ASK.api.storage("tips.adopt", true);
                        var adopt = $(this).closest(".popover").siblings((".btn-adopt"));
                        var thanks = $(this).closest(".popover").siblings((".btn-thanks"));
                        adopt.popover('hide');
                        thanks.popover({
                            placement: 'bottom',
                            html: true,
                            title: '',
                            content: '<p>别忘了对小伙伴的热心解答表示感谢和支持</p><div class="text-right"><a href="javascript:;" class="btn btn-primary btn-xs" data-dismiss="popover">关闭</a></div>',
                            trigger: 'manual'
                        }).popover('show');
                    });
                }, 100);
            }
        }
    }

    //我来回答
    $(document).on("click", ".btn-answer", function () {
        $("#answercontent").focus();
    });

    //追加悬赏
    $(document).on("click", ".btn-reward", function () {
        layer.open({
            title: "追加悬赏",
            type: 1,
            content: template("rewardtpl", {}),
            success: function (layero, index) {
                $("form", layero).on("submit", function () {
                    ASK.api.ajax({
                        url: "question/reward",
                        data: $(this).serialize()
                    }, function (data, ret) {
                        ASK.api.msg(ret.msg, ret.url);
                        return false;
                    });
                    return false;
                });
            }
        });
        return false;
    });

    //采纳答案
    $(document).on("click", ".btn-adopt", function () {
        var that = this;
        layer.confirm("确认采纳此答案？<br>采纳成功以后将无法再进行修改！！！", {title: '温馨提示', icon: 3}, function () {
            ASK.api.ajax({
                url: 'question/adopt',
                data: {question_id: $(that).data("question-id"), best_answer_id: $(that).data("id")}
            }, function (data, ret) {
                layer.alert("采纳成功！", function () {
                    location.reload();
                });
                return false;
            });
        });
        return false;
    });

    //关闭问题
    $(document).on("click", ".btn-close", function () {
        var that = this;
        layer.confirm("确认关闭此提问？<br>如果当前为悬赏提问，关闭后悬赏金额将退回提问者余额账号！！！", {title: '温馨提示', icon: 3}, function () {
            ASK.api.ajax({
                url: $(that).attr("href")
            }, function (data, ret) {
                layer.alert("关闭成功！", function () {
                    location.reload();
                });
                return false;
            });
        });
        return false;
    });

    //开启问题
    $(document).on("click", ".btn-open", function () {
        var that = this;
        ASK.api.ajax({
            url: $(that).attr("href")
        }, function (data, ret) {
            layer.alert("开启成功！", function () {
                location.reload();
            });
            return false;
        });
        return false;
    });

    //完善答案
    $(document).on("click", ".btn-editanswer", function () {
        var that = this;
        ASK.api.ajax({
            url: "answer/update",
            type: 'get',
            data: {id: $(that).data("id")}
        }, function (data, ret) {
            layer.open({
                title: '完善回答',
                type: 1,
                zIndex: 1051,
                area: isMobile ? 'auto' : ["600px", "515px"],
                content: data,
                success: function (layero, index) {
                    ASK.render.editor($("#c-content", layero));
                    ASK.api.form($("form", layero), function (data, ret) {
                        $(that).closest(".comment").find(".comment-body").find(".wysiwyg").html(data);
                        ASK.render.audio();
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

    //切换排序
    $("select[name='order']").change(function () {
        location.href = "?order=" + $(this).val();
    });

    //添加答案
    ASK.api.form("#postanswer", function (data, ret) {
        var answerlist = $(".answer-list");
        $(".answer-nodata", answerlist).remove();
        answerlist.append(data);
        $(".answer-nums", answerlist).text(parseInt($(".answer-nums", answerlist).text()) + 1);
        $("textarea", this).val('');
        if (Config.editormode !== 'markdown') {
            $("textarea[name='content']", this).summernote("code", "");
        }
        $("input[name=price]", this).val('');
        $("#peepsetting").addClass("hidden");
    }, function (data, ret) {

    });

    //发表评论
    ASK.api.form(".postcomment", function (data, ret) {
        var footer = $(this).closest(".comment-footer");
        $(".comment-nodata", footer).remove();
        $(".comment-list", footer).append(data);
        $("a.btn-comment span", footer).text(parseInt($("a.btn-comment span", footer).text()) + 1);
        $("textarea", this).val('');
        if (Config.editormode !== 'markdown') {
            $("textarea[name='content']", this).summernote("code", "");
        }
    }, function (data, ret) {

    });

    //付费查看设定
    $(document).on("click", ".btn-peepsetting", function () {
        if ($(this).hasClass("disabled")) {
            layer.alert("当前无法进行付费查看设定，原因：" + $(this).data("reason"), {title: "温馨提示"});
            return;
        }
        $("#peepsetting").toggleClass("hidden");
    });

    //切换付费查看类型
    $(document).on("change", "#peepsetting select[name=switchpeeptype]", function () {
        var type = $(this).val();
        $("#peepsetting > div[data-type]").addClass("hidden");
        $("#peepsetting > div[data-type='" + type + "']").removeClass("hidden");
        $("#peepsetting > div[data-type] input[type=number]").val("");
        $("#peepsetting > div[data-type] .btn-price").removeClass("active");
    });

    //付费查看选择金额
    $(document).on("click", "#peepsetting .btn-price", function () {
        var form = $(this).closest("form");
        var type = $(this).closest("div[data-type]").data("type");
        $("input[name='score'],input[name='price']", form).val('');
        $("input[name='" + type + "']", form).val($(this).data("value"));
        $(".btn-price", form).removeClass("active");
        $(this).addClass("active");
        return false;
    });

    //付费查看清除
    $(document).on("click", "#peepsetting .btn-clear", function () {
        var form = $(this).closest("form");
        $("input[name='score'],input[name='price']", form).val('');
        $(".btn-price", form).removeClass("active");
        return false;
    });

    //发送邀请
    $(document).on("click", ".btn-invite", function () {
        layer.open({
            title: "发送邀请",
            type: 1,
            content: template('invitetpl', {}),
            area: isMobile ? 'auto' : ["500px", '570px'],
            zIndex: 9999,
            btn: false,
            success: function (layero, index) {
                var form = $("form", layero);
                $("a[data-toggle='tab']", form).on("shown.bs.tab", function () {
                    form.find("input[name=type]").val($(this).data("type"));
                });
                ASK.api.form(form);
                form.on("click", ".btn-invite-user", function (e) {
                    $("input[name=user]", form).val($(this).data("id"));
                    $("input[name=price]", form).val(0);
                    form.trigger("submit");
                });
                form.on("click", ".btn-invite-user-pay", function () {
                    var that = this;
                    var tips = "<div class='alert alert-danger small'>温馨提示：<br>1、被邀请者回答提问后将直接得到<b>邀请赏金</b>，与是否采纳无关<br>2、如果被邀请者在采纳最佳答案前仍未回答，赏金将退还给邀请者<br></div>";
                    var price = Config.inviteprice.split(/\-/);
                    layer.prompt({
                        title: '付费邀请',
                        content: tips + '<div class="mb-2">请输入邀请赏金 <span class="text-muted small">金额必须在￥' + Config.inviteprice + '区间</span></div><input type="text" class="layui-layer-input form-control" placeholder="金额必须在￥' + Config.inviteprice + '区间" style="width:100%;" value="' + price[0] + '">',
                        area: isMobile ? 'auto' : ["460px", 'auto'],
                        btn: ["发送邀请", "取消"]
                    }, function (value, index, elem) {
                        $("input[name=user]", form).val($(that).data("id"));
                        $("input[name=price]", form).val(value);
                        form.trigger("submit");
                        layer.close(index);
                    });
                });
                var tpl = '<tr>' +
                    '<td><a href="<%=url%>" target="_blank"><img src="<%=avatar%>" class="img-rounded img-tiny" alt=""></a></td>' +
                    '<td><%=nickname%></td>' +
                    '<td width="135">' +
                    '<a href="javascript:" data-id="<%=id%>" class="btn btn-primary btn-xs btn-invite-user" type="submit">邀请TA</a>' +
                    '<a href="javascript:" data-id="<%=id%>" class="btn btn-danger btn-xs btn-invite-user-pay ml-1" type="submit">付费邀请</a>' +
                    '</td>' +
                    '</tr>';
                var sst, ajax = null;
                form.on("keydown keyup", "#search-account", function (e) {
                    if (e.type == 'keydown') {
                        if (e.keyCode == 13 || e.keyCode == 10) {
                            return false;
                        }
                        return;
                    }
                    var that = this;
                    clearTimeout(sst);
                    sst = setTimeout(function () {
                        try {
                            ajax && ajax.abort();
                        } catch (e) {

                        }
                        ajax = ASK.api.ajax({
                            url: "invite/search",
                            data: {q: $(that).val()}
                        }, function (data, ret) {
                            var tbody = $(".table tbody", form);
                            tbody.html('');
                            $.each(data, function (i, j) {
                                tbody.append(template.render(tpl, j));
                            });
                            if ($("tr", tbody).length == 0) {
                                tbody.html('<tr><td colspan="3" class="text-center">' + ($(that).val() ? '未找到指定用户' : '暂无推荐用户') + '</td></tr>');
                            }
                            return false;
                        }, function () {
                            return false;
                        });
                    }, 300);
                });
                $("#search-account", form).trigger("keyup");
            }
        });
        return false;
    });

    //加载更多答案
    $(document).on("click", ".action-answer", function () {
        var that = this;
        var page = parseInt($(that).data("page"));
        $.ajax({
            url: "ajax/get_answer_list",
            dataType: 'json',
            data: {id: $(that).data("id"), page: page, order: $("select[name='order'] option:selected").val()},
            success: function (ret) {
                if (ret) {
                    $(".answer-list").append(ret);
                    $(that).data("page", page + 1);
                    var remains = parseInt($("span", that).text()) - $(ret).filter(".comment").size();
                    if (remains < 0) {
                        $(that).html("已经全部加载");
                    } else {
                        $("span", that).text(remains);
                    }
                } else {
                    $(that).html("已经全部加载");
                }
            }
        });
    });

    //问题查看更多
    $(document).on("click", ".question-richtext .read-more a", function () {
        $(".question-richtext").css("max-height", 9999);
        $(".question-richtext .read-more").fadeOut(100);
        return false;
    });

});
