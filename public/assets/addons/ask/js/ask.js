var ASK = {

    events: {
        //请求成功的回调
        onAjaxSuccess: function (ret, onAjaxSuccess) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作成功';

            if (typeof onAjaxSuccess === 'function') {
                var result = onAjaxSuccess.call(this, data, ret);
                if (result === false)
                    return;
            }
            layer.msg(msg, {icon: 1});
        },
        //请求错误的回调
        onAjaxError: function (ret, onAjaxError) {
            var data = typeof ret.data !== 'undefined' ? ret.data : null;
            var msg = typeof ret.msg !== 'undefined' && ret.msg ? ret.msg : '操作失败';
            if (typeof onAjaxError === 'function') {
                var result = onAjaxError.call(this, data, ret);
                if (result === false) {
                    return;
                }
            }
            layer.msg(msg, {icon: 2});
        },
        //服务器响应数据后
        onAjaxResponse: function (response) {
            try {
                var ret = typeof response === 'object' ? response : JSON.parse(response);
                if (!ret.hasOwnProperty('code')) {
                    $.extend(ret, {code: -2, msg: response, data: null});
                }
            } catch (e) {
                var ret = {code: -1, msg: e.message, data: null};
            }
            return ret;
        }
    },
    api: {
        //获取修复后可访问的cdn链接
        cdnurl: function (url, domain) {
            var rule = new RegExp("^((?:[a-z]+:)?\\/\\/|data:image\\/)", "i");
            var cdnurl = Config.upload.cdnurl;
            url = rule.test(url) || (cdnurl && url.indexOf(cdnurl) === 0) ? url : cdnurl + url;
            if (domain && !rule.test(url)) {
                domain = typeof domain === 'string' ? domain : location.origin;
                url = domain + url;
            }
            return url;
        },
        //发送Ajax请求
        ajax: function (options, success, error) {
            options = typeof options === 'string' ? {url: options} : options;
            var st, index = 0;
            st = setTimeout(function () {
                if (typeof options.loading === 'undefined' || options.loading) {
                    index = layer.load(options.loading || 0);
                }
            }, 150);
            options = $.extend({
                type: "POST",
                dataType: "json",
                xhrFields: {
                    withCredentials: true
                },
                success: function (ret) {
                    clearTimeout(st);
                    index && layer.close(index);
                    ret = ASK.events.onAjaxResponse(ret);
                    if (ret.code === 1) {
                        ASK.events.onAjaxSuccess(ret, success);
                    } else {
                        ASK.events.onAjaxError(ret, error);
                    }
                },
                error: function (xhr) {
                    clearTimeout(st);
                    index && layer.close(index);
                    var ret = {code: xhr.status, msg: xhr.statusText, data: null};
                    ASK.events.onAjaxError(ret, error);
                }
            }, options);
            return $.ajax(options);
        },
        //提示并跳转
        msg: function (message, url) {
            var callback = typeof url === 'function' ? url : function () {
                if (typeof url !== 'undefined' && url) {
                    location.href = url;
                }
            };
            layer.msg(message, {
                icon: 1,
                time: 2000
            }, callback);
        },
        //表单提交事件
        form: function (elem, success, error, submit) {
            var delegation = typeof elem === 'object' && typeof elem.prevObject !== 'undefined' ? elem.prevObject : document;
            $(delegation).on("submit", elem, function (e) {
                var form = $(e.target);
                if (typeof submit === 'function') {
                    if (false === submit.call(form, success, error)) {
                        return false;
                    }
                }
                var error = '';
                $("[data-rule]", form).each(function () {
                    var nameArr = {category_id: "分类", title: "标题", tags: "标签", zone_id: "专区", content: "内容", captcha: "验证码"};
                    var name = $(this).attr("name");
                    if ($(this).data("rule").indexOf("required") > -1 && $(this).val() === '') {
                        var label = typeof nameArr[name] !== 'undefined' ? nameArr[name] : '';
                        if (!label) {
                            label = $(this).closest(".form-group").find("label").text() || '';
                        }
                        error = label + "不能为空";
                        if (name === 'tags') {
                            $(this).next().find("input").focus();
                        } else if (name === 'content' && Config.editormode !== 'markdown') {
                            $(this).next().find(".note-editable").focus();
                        } else {
                            $(this).focus();
                        }
                        return false;
                    }
                });
                if (error) {
                    layer.msg(error, {icon: 2});
                    return false;
                }
                $("[type=submit]", form).prop("disabled", true);
                var captcha = $("input[name=captcha]", form);
                ASK.api.ajax({
                    url: form.attr("action"),
                    data: form.serialize(),
                    complete: function (xhr) {
                        var token = xhr.getResponseHeader('__token__');
                        if (token) {
                            $("input[name='__token__']").val(token);
                        }
                        $("[type=submit]", form).prop("disabled", false);
                    }
                }, function (data, ret) {
                    //刷新客户端token
                    if (data && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    //刷新验证码
                    if (captcha) {
                        captcha.next().find("img").trigger("click");
                        captcha.val('');
                    }
                    //自动保存草稿设置
                    var autosaveKey = $("textarea[data-autosave-key]", form).data("autosave-key");
                    if (autosaveKey && localStorage) {
                        localStorage.removeItem("autosave-" + autosaveKey);
                        $(".md-autosave", form).addClass("hidden");
                    }
                    if (typeof success === 'function') {
                        if (false === success.call(form, data, ret)) {
                            return false;
                        }
                    }
                }, function (data, ret) {
                    //刷新客户端token
                    if (data && typeof data.token !== 'undefined') {
                        $("input[name='__token__']").val(data.token);
                    }
                    //刷新验证码
                    if (captcha) {
                        captcha.next().find("img").trigger("click");
                        captcha.val('');
                    }
                    if (typeof error === 'function') {
                        if (false === error.call(form, data, ret)) {
                            return false;
                        }
                    }
                });
                return false;
            });
        },
        //localStorage存储
        storage: function (key, value) {
            key = key.split('.');

            var _key = key[0];
            var o = JSON.parse(localStorage.getItem(_key));

            if (typeof value === 'undefined') {
                if (o == null)
                    return null;
                if (key.length === 1) {
                    return o;
                }
                _key = key[1];
                return typeof o[_key] !== 'undefined' ? o[_key] : null;
            } else {
                if (key.length === 1) {
                    o = value;
                } else {
                    if (o && typeof o === 'object') {
                        o[key[1]] = value;
                    } else {
                        o = {};
                        o[key[1]] = value;
                    }
                }
                localStorage.setItem(_key, JSON.stringify(o));
            }
        }
    },
    render: {
        //问题搜索
        question: function (elem, options, callback) {
            var xhr;
            var question = $(elem);
            question.autoComplete($.extend({
                minChars: 1,
                cache: 0,
                menuClass: 'autocomplete-search',
                header: question.data("header") ? template(question.data("header"), {}) : '',
                footer: question.data("footer") ? template(question.data("footer"), {}) : '',
                source: function (term, response) {
                    try {
                        xhr.abort();
                    } catch (e) {
                    }
                    xhr = $.getJSON('ajax/get_search_autocomplete', {q: term, type: question.data("type")}, function (data) {
                        response(data);
                    });
                },
                renderItem: function (item, search) {
                    search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    var regexp = new RegExp("(" + search.replace(/[\,|\u3000|\uff0c]/, ' ').split(' ').join('|') + ")", "gi");
                    template.helper("replace", function (value) {
                        return value.replace(regexp, "<b>$1</b>");
                    });
                    return template(question.data("body") ? template(question.data("body"), {}) : 'bodytpl', {item: item, search: search});
                },
                onSelect: function (e, term, item) {
                    e.preventDefault();
                    if (typeof callback === 'function') {
                        callback.call(elem, term, item);
                    } else {
                        if ($(item).data("url")) {
                            location.href = $(item).data("url");
                        }
                        return false;
                    }
                }
            }, options || {}));
            //Ctrl+回车执行Bing搜索
            question.on("keydown", function (e) {
                if ($(this).attr("name") === "keyword") {
                    var form = $(this).closest("form");
                    var keyword = $(this).val();
                    var action = form.attr("action");
                    if ((e.metaKey || e.ctrlKey) && (e.keyCode == 13 || e.keyCode == 10)) {
                        form.attr("action", "https://cn.bing.com/search").attr("target", "_blank");
                        //var q = $("<input type='hidden' name='q' />").val("site:" + location.host + " " + keyword);
                        var q = $("<input type='hidden' name='q' />").val("site:" + location.host + " " + keyword);
                        form.append(q).trigger("submit");
                        setTimeout(function () {
                            form.attr("action", action).attr("target", "_self");
                            q.remove();
                        }, 100);
                    } else if (e.keyCode == 13 || e.keyCode == 10) {
                        if (question.val() == '') {
                            return false;
                        }
                        form.trigger("submit");
                    }
                }
            });
        },
        //标签搜索
        tags: function (elem, options, callback) {
            var tags = $(elem);
            //标签输入
            tags.tagsInput($.extend({
                width: 'auto',
                defaultText: '输入后回车确认',
                minInputWidth: 110,
                height: '42px',
                placeholderColor: '#999',
                onChange: function (row) {
                    if (typeof callback === 'function') {
                        callback.call(elem, row);
                    } else {
                        $(elem + "_addTag").toggle(tags.val().split(/\,/).length < 3).focus();
                        $(elem + "_tag").trigger("blur.autocomplete").focus();
                    }
                },
                onKeyDown: function (e) {
                    if ((e.metaKey || e.ctrlKey) && (e.keyCode == 13 || e.keyCode == 10)) {
                        $(this).closest("form").trigger("submit");
                    }
                },
                autocomplete: {
                    url: 'ajax/get_tags_autocomplete',
                    minChars: 1,
                    menuClass: 'autocomplete-tags'
                }
            }, options || {}));
        },
        //编辑器绑定
        editor: function (elem) {
            var editor = $(elem);
            if (editor.length == 0) {
                return;
            }
            var form = editor.closest("form");
            var isMarkdown = Config.editormode === 'markdown';

            if (!isMarkdown) {
                var onFileUpload = function (files) {
                    for (var i = 0; i < files.length; i++) {
                        editor.uploadFile(files[i], files[i].name, {
                            success: function (ret, filename, file) {
                                if (ret.code == 1) {
                                    var node;
                                    if (file.type.match(/^image\//i)) {
                                        node = $("<img />").attr("src", ASK.api.cdnurl(ret.data.url, true))[0];
                                    } else {
                                        node = $("<a />").text(filename).attr("href", ASK.api.cdnurl(ret.data.url, true))[0];
                                    }
                                    editor.summernote("insertNode", node);
                                } else {
                                    layer.msg(ret.msg || "上传发生错误(code:1)");
                                }
                            }, error: function (ret) {
                                layer.msg("上传发生错误(code:2)");
                            }
                        });
                    }
                };
                var ti, insert = ['link'];
                if (Config.uploadparts.indexOf("image") > -1) {
                    insert.push('picture');
                }
                if (Config.uploadparts.indexOf("file") > -1) {
                    insert.push('file');
                }
                editor.val(filterXSS(editor.val()));
                editor.summernote({
                    lang: 'zh-CN',
                    placeholder: editor.attr("placeholder"),
                    height: editor.height() > 0 ? editor.height() : 200,
                    tabsize: 2,
                    // shortcuts:false,
                    prettifyHtml: false,
                    codeviewFilter: true,
                    toolbar: [
                        ['style', ['style', 'undo', 'redo']],
                        ['font', ['bold', 'italic', 'hr', 'clear']],
                        ['para', ['ul', 'ol']],
                        ['table', ['table']],
                        ['insert', insert],
                        ['view', ['fullscreen', 'help']]
                    ],
                    popover: {
                        image: [['remove', ['removeMedia']]]
                    },
                    callbacks: {
                        onInit: function () {
                        },
                        onKeydown: function (e, a, b) {
                            if (e.keyCode == 13) {
                                if (editor.data("summernote").layoutInfo.editable.data('textComplete').dropdown.shown) {
                                    e.preventDefault();
                                }
                            }
                        },
                        onChange: function (contents) {
                            clearTimeout(ti);
                            ti = setTimeout(function () {
                                var turndownService = new TurndownService();
                                turndownService.use(turndownPluginGfm.gfm);
                                editor.val(turndownService.turndown(contents));
                            }, 250);
                        },
                        onImageUpload: onFileUpload,
                        onFileUpload: onFileUpload
                    }
                });
                var replace = {};
                //设定summernote值
                editor.summernote("code", (new HyperDown()).makeHtml(editor.val().replace(/#(\d+)[\\]?\[(.*)[\\]?\]\(([a-zA-Z]{1})\)/g, function () {
                    var placeholder = arguments[3] + "" + arguments[1];
                    replace[placeholder] = arguments[0];
                    return "{##" + placeholder + "##}";
                })).replace(/\{##(.*)##\}/g, function () {
                    return typeof replace[arguments[1]] !== 'undefined' ? replace[arguments[1]] : arguments[0];
                }));
                //@和#下拉选择
                $.fn.textcomplete.Completer.prototype.select = function (value, strategy) {
                    var str = strategy.replace(value, strategy) || '';
                    str = str.replace(/ $/, '&nbsp;');
                    if (!str) {
                        return;
                    }
                    var select = editor.summernote('getLastRange').getWordsMatchRange(strategy.match).select();
                    select.deleteContents();
                    editor.summernote("setLastRange");
                    editor.summernote('pasteHTML', str);
                };
                form.on('submit', function (e) {
                    var turndownService = new TurndownService();
                    turndownService.use(turndownPluginGfm.gfm);
                    editor.val(turndownService.turndown(editor.summernote('code')));
                    console.log(editor.summernote('code'), turndownService.turndown(editor.summernote('code')));
                });
            } else {
                //粘贴上传图片
                editor.pasteUploadImage();

                //Tab事件
                tabOverride.tabSize(4).autoIndent(true).set(editor[0]);

                //Markdown编辑器
                editor.markdown({
                    resize: 'vertical', language: 'zh', iconlibrary: 'fa', autofocus: false, savable: false,
                    onShow: function (e) {
                        //添加上传图片按钮和上传附件按钮
                        var imgBtn = $("button[data-handler='bootstrap-markdown-cmdImage']", e.$editor);
                        var fileBtn = $("button[data-handler='bootstrap-markdown-cmdFile']", e.$editor);
                        var btnParent = imgBtn.parent();
                        btnParent.addClass("md-relative");

                        if (Config.uploadparts.indexOf("image") > -1) {
                            var upImgBtn = $('<input type="file" class="uploadimage" data-button="image" title="点击上传图片" accept="image/*" multiple="">');
                            upImgBtn.css(imgBtn.position()).appendTo(btnParent);
                        } else {
                            imgBtn.remove();
                        }
                        if (Config.uploadparts.indexOf("file") > -1) {
                            var upFileBtn = $('<input type="file" class="uploadfile" data-button="file" title="点击上传附件" multiple="">');
                            upFileBtn.css(fileBtn.position()).appendTo(btnParent);
                        } else {
                            fileBtn.remove();
                        }

                        $(".uploadimage,.uploadfile", e.$editor).on("mouseenter", function () {
                            ($(this).data("button") === 'image' ? imgBtn : fileBtn).addClass("active");
                        }).on("mouseleave", function () {
                            ($(this).data("button") === 'image' ? imgBtn : fileBtn).removeClass("active");
                        });
                    }
                });

                //手动选择上传图片
                $(".uploadimage,.uploadfile", form).change(function () {
                    var that = this;
                    $.each($(this)[0].files, function (i, file) {
                        editor.uploadFile(file, file.name, {fileType: $(that).data("button")});
                    });
                });

            }

            //自动保存草稿设置
            var autosaveKey = editor.data("autosave-key");
            if (autosaveKey && localStorage) {
                autosaveKey = "autosave-" + autosaveKey;
                var autosave = (isMarkdown ? editor.data("markdown").$editor : editor.data("summernote").layoutInfo.editor).addClass("md-relative").prepend('<span class="md-autosave hidden"></span>').find(".md-autosave");
                if (localStorage.getItem(autosaveKey)) {
                    autosave.html("<a href='javascript:' data-event='restore' class='text-danger'><i class='fa fa-info-circle'></i> 发现未保存的草稿数据，点击还原</a> | <a href='javascript:' data-event='release' class='text-danger'><i class='fa fa-times'></i> 清除草稿</a>").removeClass("hidden");
                }
                var timer = null;
                (isMarkdown ? editor : editor.data('summernote').layoutInfo.editable).on("keyup keydown paste cut input", function () {
                    clearTimeout(timer);
                    timer = setTimeout(function () {
                        localStorage.setItem(autosaveKey, isMarkdown ? editor.val() : editor.summernote('code'));
                        var timeNow = new Date(),
                            hours = timeNow.getHours(),
                            minutes = timeNow.getMinutes(),
                            seconds = timeNow.getSeconds();
                        var time = hours + ((minutes < 10) ? ":0" : ":") + minutes + ((seconds < 10) ? ":0" : ":") + seconds;

                        autosave.html("<i class='fa fa-info-circle'></i> 草稿已于 " + time + " 自动保存 | <a href='javascript:' data-event='release' class='text-warning'><i class='fa fa-times'></i> 清除草稿</a>").removeClass("hidden");
                    }, 300);
                });
                form.on("submit", function () {
                    clearTimeout(timer);
                });
                autosave.on("click", "a", function () {
                    if ($(this).data("event") === 'restore') {
                        if (isMarkdown) {
                            editor.val(localStorage.getItem(autosaveKey)).trigger("change");
                        } else {
                            editor.summernote('code', localStorage.getItem(autosaveKey));
                        }
                    } else if ($(this).data("event") === 'release') {
                        localStorage.removeItem(autosaveKey);
                    }
                    autosave.addClass("hidden");
                    return false;
                });
            }

            //捕获回车
            (isMarkdown ? editor : editor.data('summernote').layoutInfo.editable).keydown(function (e) {
                if ((e.metaKey || e.ctrlKey) && (e.keyCode == 13 || e.keyCode == 10)) {
                    setTimeout(function () {
                        form.trigger("submit");
                    }, 0);
                }
            });

            //@用户支持 #话题支持
            var loadingText = '加载中...';
            var userTipsText = '请输入关键词进行搜索用户';
            var questionTipsText = '请输入关键词进行搜索问题或文章';
            var ajax;
            (isMarkdown ? editor : editor.data('summernote').layoutInfo.editable).textcomplete([
                {
                    id: 'user',
                    match: /\B@(((?!\s).)*)$/,
                    search: function (term, callback) {
                        callback([loadingText]);
                        ajax && ajax.abort();
                        if (!term || term === '@') {
                            ajax = $.ajax({
                                url: "ajax/get_user_autocomplete",
                                data: {q: '', id: form.find("input[name=id]").val(), type: form.find("input[name=type]").val()},
                                dataType: 'json',
                                success: function (result) {
                                    var data = [];
                                    if (result.length > 0) {
                                        $.each(result, function (index, item) {
                                            data.push('<div data-username="' + item.username + '"><img src="' + item.avatar + '" class="img-circle mr-1" width="20" height="20">' + item.nickname + ' <span class="small text-muted">@' + item.username + '</span></div>');
                                        });
                                        callback(data, true);
                                    } else {
                                        callback([userTipsText]);
                                    }
                                }
                            });
                            return;
                        }
                        ajax = $.ajax({
                            url: "ajax/get_user_autocomplete",
                            data: {q: term, id: form.find("input[name=id]").val(), type: form.find("input[name=type]").val()},
                            dataType: 'json',
                            success: function (result) {
                                var data = [];
                                if (result.length > 0) {
                                    $.each(result, function (index, item) {
                                        data.push('<div data-username="' + item.username + '"><img src="' + item.avatar + '" class="img-circle mr-1" width="20" height="20">' + item.nickname + ' <span class="small text-muted">@' + item.username + '</span></div>');
                                    });
                                }
                                callback(data, true);
                            }
                        });
                    },
                    index: 1,
                    replace: function (word) {
                        if (word !== loadingText && word !== userTipsText) {
                            console.log("replace:" + $(word).data("username"));
                            return '@' + $(word).data("username") + ' ';
                        }
                    }
                },
                {
                    id: 'question',
                    match: /\B#(((?!\s).)*)$/,
                    search: function (term, callback) {
                        if (!term || term === '#') {
                            return callback([questionTipsText]);
                        }
                        callback([loadingText]);
                        $.ajax({
                            url: "ajax/get_question_autocomplete",
                            data: {q: term},
                            dataType: 'json'
                        }).then(function (result) {
                            var data = [];
                            var regexp = new RegExp("(" + term.replace(/[\,|\u3000|\uff0c]/, ' ').split(' ').join('|') + ")", "gi");
                            if (result.length > 0) {
                                $.each(result, function (index, item) {
                                    data.push('<div class="row" data-id="' + item.id + '" data-type="' + item.type + '" data-title="' + item.title + '"><div class="col-xs-10">#<span>' + item.title.replace(regexp, "<b>$1</b>") + '</span></div>' + '<div class="col-xs-2"><span class="tag tag-xs ' + (item.type === 'question' ? "" : "tag-danger") + '">' + (item.type === 'question' ? "问题" : "文章") + '</span>' + '</div></div>');
                                });
                            }
                            callback(data, true);
                        });
                    },
                    replace: function (word) {
                        if (word !== loadingText && word !== questionTipsText) {
                            return '#' + $(word).data("id") + '[' + $(word).data("title") + ']' + '(' + ($(word).data("type") === 'question' ? 'Q' : 'A') + ')' + ' ';
                        }
                    },
                    index: 1
                }
            ], {appendTo: 'body'});
        },
        //倒计时
        countdown: function (elem) {
            var makeTimer = function (elem, timeLeft) {
                var days = Math.floor(timeLeft / 86400);
                var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
                var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600)) / 60);
                var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

                if (hours < "10") {
                    hours = "0" + hours;
                }
                if (minutes < "10") {
                    minutes = "0" + minutes;
                }
                if (seconds < "10") {
                    seconds = "0" + seconds;
                }
                $(elem).html(days + "天" + hours + "时" + minutes + "分" + seconds + "秒");

            };

            $(elem).each(function () {
                var seconds = $(this).data("seconds");

                setInterval(function () {
                    makeTimer(elem, seconds);
                    seconds--;
                }, 1000);

            });
        },
        //语音播放器
        audio: function (dom) {
            $("audio:not([rendered])", dom || document.body).each(function () {
                $(this).attr("rendered", true);
                var container = $("<div />").addClass("audio-player-container");
                var audioTime = $("<div />").addClass("audio-player-time").html('<i class="fa fa-wifi fa-rotate-90"></i> <span>' + ($(this).attr("duration") ? parseFloat($(this).attr("duration")).toFixed(0) : '') + '</span> "');
                var audioBar = $("<div />").addClass("audio-player-bar").html('<span>听答案</span><div class="volumebar hidden"><i></i><i></i><i></i><i></i></div>');
                var volumeText = audioBar.find("span");
                var volumeBar = audioBar.find(".volumebar");

                container.append(audioTime).append(audioBar).insertBefore(this);
                $(this).appendTo(container);

                var player = $(this).get(0);
                player.volume = 0.8;

                container.on("click", function () {
                    if (player.paused) {
                        $.each($('audio'), function () {
                            this.pause();
                        });
                        player.play();
                    } else {
                        player.pause();
                    }
                });

                $(player).on('ended', function (evt) {
                    var duration = parseFloat($(this).attr("duration")) || this.duration;
                    player.pause();
                    audioTime.find("span").text(duration.toFixed(0));
                });

                $(player).on('play', function (e) {
                    volumeText.toggleClass("hidden", true);
                    volumeBar.toggleClass("hidden", false);
                });

                $(player).on('pause', function (e) {
                    volumeText.toggleClass("hidden", false);
                    volumeBar.toggleClass("hidden", true);
                });

                $(player).on('timeupdate', function (e) {
                    var duration = parseFloat($(this).attr("duration")) || this.duration;
                    var time = this.currentTime;
                    var fraction = time / duration;
                    percent = fraction * 100;
                    audioTime.find("span").text((duration - time).toFixed(0));
                });

                if ($(this).attr('autoplay') == 'autoplay') {
                    container.click();
                }
            });
        }
    }
};
