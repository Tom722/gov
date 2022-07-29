define([], function () {
    require.config({
    paths: {
        'jquery-colorpicker': '../addons/ask/js/jquery.colorpicker.min',
    },
    shim: {
        'jquery-colorpicker': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
    }
});

require.config({
    paths: {
        'clicaptcha': '../addons/clicaptcha/js/clicaptcha'
    },
    shim: {
        'clicaptcha': {
            deps: [
                'jquery',
                'css!../addons/clicaptcha/css/clicaptcha.css'
            ],
            exports: '$.fn.clicaptcha'
        }
    }
});

require(['clicaptcha'], function () {
    window.clicaptcha = function (captcha) {
        captcha = captcha ? captcha : $("input[name=captcha]");
        if (captcha.length > 0) {
            var form = captcha.closest("form");
            var parentDom = captcha.parent();
            // 非文本验证码
            if ($("a[data-event][data-url]", parentDom).size() > 0) {
                return;
            }
            if (captcha.parentsUntil(form, "div.form-group").length > 0) {
                captcha.parentsUntil(form, "div.form-group").addClass("hidden");
            } else if (parentDom.is("div.input-group")) {
                parentDom.addClass("hidden");
            }
            captcha.attr("data-rule", "required");
            // 验证失败时进行操作
            captcha.on('invalid.field', function (e, result, me) {
                //必须删除errors对象中的数据，否则会出现Layer的Tip
                delete me.errors['captcha'];
                if (result.key === 'captcha') {
                    captcha.clicaptcha({
                        src: '/addons/clicaptcha/index/start',
                        success_tip: '验证成功！',
                        error_tip: '未点中正确区域，请重试！',
                        callback: function (captchainfo) {
                            form.trigger("submit");
                            return false;
                        }
                    });
                }
            });
            // 监听表单错误事件
            form.on("error.form", function (e, data) {
                captcha.val('');
            });
        }
    };
    clicaptcha($("input[name=captcha]"));
});

require.config({
    paths: {
        'jquery-colorpicker': '../addons/cms/js/jquery.colorpicker.min',
        'jquery-autocomplete': '../addons/cms/js/jquery.autocomplete',
        'jquery-tagsinput': '../addons/cms/js/jquery.tagsinput',
        'clipboard': '../addons/cms/js/clipboard.min',
    },
    shim: {
        'jquery-colorpicker': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'jquery-autocomplete': {
            deps: ['jquery'],
            exports: '$.fn.extend'
        },
        'jquery-tagsinput': {
            deps: ['jquery', 'jquery-autocomplete', 'css!../addons/cms/css/jquery.tagsinput.min.css'],
            exports: '$.fn.extend'
        }
    }
});

});