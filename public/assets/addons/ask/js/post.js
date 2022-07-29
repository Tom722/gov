$(function () {
    $("#uploadimg").change(function () {
        var file = $('#uploadimg').get(0).files[0];
        $("#image").uploadFile(file, file.name, {
            success: function (ret) {
                $("#previewimg").attr("src", ret.data.url);
                return false;
            }
        });
    });

    //切换悬赏类型
    $(document).on("change", "select[name=switpricetype]", function () {
        var type = $(this).val();
        $(".pricelist .row-price[data-type]").addClass("hidden");
        $(".pricelist .row-price[data-type='" + type + "']").removeClass("hidden");
        $(".pricelist .row-price label[data-type][data-value=0]").trigger("click");
    });
    //切换金额
    $(document).on("click", ".row-price label[data-type]", function () {
        var container = $(this).closest(".row-price");
        var type = container.data("type");
        $("label[data-type]", container).removeClass("active");
        $(this).addClass("active");
        $("input[name=" + (type ? type : 'price') + "]").val($(this).data("value"));
        if ($(this).data("type") === 'custom') {
            if ($("input", this).size() == 0) {
                $(this).html('<input type="number" name="customprice" class="form-control customprice" />');
            }
            $("input[name=customprice]", container).trigger("focus").trigger("keydown");
        } else {
            $("label[data-type='custom']", container).html('其它' + (type == 'score' ? '积分' : '金额'));
        }

        if ($("#post-article").size() > 0) {
            $("#anonymoustips").toggleClass("hidden", $(this).data("value") == "0");
            $("#c-isanonymous").prop("checked", false).prop("disabled", $(this).data("value") != "0");
        }
    });

    //选中价格
    $(document).on("click", ".row-paytype label", function () {
        $(".row-paytype label").removeClass("active");
        $(this).addClass("active");
        $("input[name=paytype]").val($(this).data("value"));
    });

    //自定义价格
    $(document).on("keyup keydown change", ".customprice", function () {
        var container = $(this).closest(".row-price");
        var type = container.data("type");
        $("input[name=" + (type ? type : 'price') + "]").val($(this).val());
    });

    //付费邀请
    $(document).on("click", ".btn-postinvite", function () {
        layer.open({
            title: "邀请解答",
            type: 1,
            content: template("postinvitetpl", {}),
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
                    $("input[name=to_user_id]", form).val($(this).data("id"));
                    $("input[name=inviteprice]", form).val(0);
                    $("#inviteuser").html('<a href="' + $(this).data('url') + '" data-toggle="popover" data-title="' + $(this).data('nickname') + '" data-placement="right" data-type="user" data-id="' + $(this).data('id') + '" target="_blank">' + $(this).data('nickname') + '</a>');
                    $("#invitebody").removeClass("hidden");
                    layer.closeAll();
                });
                var tpl = '<tr>' +
                    '<td><a href="<%=url%>" target="_blank"><img src="<%=avatar%>" class="img-rounded img-tiny" alt=""></a></td>' +
                    '<td><%=nickname%></td>' +
                    '<td width="135">' +
                    '<a href="javascript:" data-id="<%=id%>" data-url="<%=url%>" data-nickname="<%=nickname%>" class="btn btn-primary btn-xs btn-invite-user" type="submit">邀请TA</a>' +
                    '<a href="javascript:" data-id="<%=id%>" data-url="<%=url%>" data-nickname="<%=nickname%>" class="btn btn-danger btn-xs btn-invite-user-pay ml-1" type="submit">付费邀请</a>' +
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
    });

    //付费邀请设定
    $(document).on("click", ".btn-invite-user-pay", function () {
        var that = this;
        var tips = "<div class='alert alert-danger small'>温馨提示：<br>1、被邀请者回答提问后将直接得到<b>邀请赏金</b>，与是否采纳无关<br>2、如果被邀请者在采纳最佳答案前仍未回答，赏金将退还给邀请者<br></div>";
        var price = Config.inviteprice.split(/\-/);
        layer.prompt({
            title: '付费邀请',
            content: tips + '<div class="mb-2">请输入邀请赏金 <span class="text-muted small">金额必须在￥' + Config.inviteprice + '区间</span></div><input type="text" class="layui-layer-input form-control" placeholder="金额必须在￥' + Config.inviteprice + '区间" style="width:100%;" value="' + price[0] + '">',
            area: isMobile ? 'auto' : ["460px", 'auto'],
            btn: ["确定", "取消"]
        }, function (value, index, elem) {
            if ($(that).prev().is("a.btn-invite-user")) {
                $(that).prev().trigger("click");
            }
            $("input[name=inviteprice]").val(value);
            $("#invitebody").toggleClass("hidden", value == 0);
            if (value == 0) {
                $("#invitetips").html("");
            } else {
                $("#invitetips").html("付费邀请赏金为￥" + value + "元");
            }
            layer.close(index);
        });
    });

    //取消付费邀请
    $(document).on("click", ".btn-invite-cancel", function () {
        $("#invitebody").addClass("hidden");
        $("input[name='to_user_id']").val(0);
        $("input[name='inviteprice']").val(0);
        return false;
    });

    //标签选择
    ASK.render.tags("#c-tags");
    //编辑器
    ASK.render.editor("#c-content");
    //问题搜索
    ASK.render.question("#c-title");
    //表单提交
    ASK.api.form(".post-form", function (data, ret) {
        ASK.api.msg(ret.msg, ret.url);
        return false;
    }, function (data, ret) {
        console.log(ret.msg.indexOf("余额不足"));
        if (ret.msg.indexOf("余额不足") > -1) {
            layer.confirm(ret.msg + "，是否前往充值余额？", {
                icon: 3, btn: ["充值余额", "取消"], title: "温馨提示",
                success: function (layero, index) {
                    $(".layui-layer-btn0", layero).attr("href", ret.url).attr("target", "_blank");
                }
            }, function () {
                layer.alert("充值成功后再次提交即可", {icon: 0, title: "温馨提示"});
            });
            return false;
        } else if (ret.msg.indexOf("暂未绑定") > -1) {
            layer.confirm(ret.msg + "，是否前往绑定？", {
                icon: 3, btn: ["前往绑定", "取消"], title: "温馨提示",
                success: function (layero, index) {
                    $(".layui-layer-btn0", layero).attr("href", ret.url).attr("target", "_blank");
                }
            }, function () {

            });
            return false;
        }
    });
});
