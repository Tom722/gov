<?php

return [
    [
        'name' => 'system_user_id',
        'title' => '平台会员ID',
        'type' => 'string',
        'content' => [],
        'value' => '0',
        'rule' => 'required',
        'msg' => '',
        'tip' => '用于统计站点收入的前台会员ID',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'sitename',
        'title' => '站点名称',
        'type' => 'string',
        'content' => [],
        'value' => '我的CMS网站',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'sitelogo',
        'title' => '站点Logo',
        'type' => 'image',
        'content' => [],
        'value' => '/uploads/20220727/d1d086425440e13a3420f2791900c4c0.png',
        'rule' => 'required',
        'msg' => '',
        'tip' => '高度50px，宽度建议160px以内',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'title',
        'title' => '首页标题',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'keywords',
        'title' => '首页关键字',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => 'data-role="tagsinput"',
    ],
    [
        'name' => 'description',
        'title' => '首页描述',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'indexpagesize',
        'title' => '首页分页大小',
        'type' => 'string',
        'content' => [],
        'value' => '10',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'mobileurl',
        'title' => 'H5站点URL',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '如果未部署Uni-APP的H5则无需设置,需http://开头',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'theme',
        'title' => '皮肤',
        'type' => 'string',
        'content' => [],
        'value' => 'default',
        'rule' => 'required; config',
        'msg' => '',
        'tip' => '请确保addons/cms/view有相应的目录',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'qrcode',
        'title' => '公众号二维码',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/qrcode.png',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'wxapp',
        'title' => '小程序二维码',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/qrcode.png',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'donateimage',
        'title' => '打赏图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/qrcode.png',
        'rule' => '',
        'msg' => '',
        'tip' => '打赏图片，请使用300*300的图片',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_archives_img',
        'title' => '文档默认图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/noimage.jpg',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_channel_img',
        'title' => '栏目默认图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/noimage.jpg',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_block_img',
        'title' => '区块默认图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/noimage.jpg',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_page_img',
        'title' => '单页默认图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/noimage.jpg',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_special_img',
        'title' => '专题默认图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/noimage.jpg',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'default_author_head_img',
        'title' => '作者顶部Banner图片',
        'type' => 'image',
        'content' => [],
        'value' => '/assets/addons/cms/img/author-head.jpeg',
        'rule' => '',
        'msg' => '',
        'tip' => '建议宽300px 高90px',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'downloadtype',
        'title' => '下载类型字典',
        'type' => 'array',
        'content' => [],
        'value' => [
            'baidu' => '百度网盘',
            'local' => '本地',
            'other' => '其它',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'spiders',
        'title' => '搜索引擎字典',
        'type' => 'array',
        'content' => [],
        'value' => [
            'Googlebot' => 'Google',
            'Bingbot' => 'Bing',
            'Baiduspider' => '百度',
            'Bytespider' => '头条',
            'AspiegelBot' => '华为',
            'Yahoo!' => '雅虎',
            'YodaoBot' => '有道',
            'SogouSpider' => '搜狗',
            '360Spider' => '360',
            'YandexBot' => 'Yandex',
            'Sosospider' => '搜搜',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '用于判断搜索引擎蜘蛛来访使用<br>键:搜索引擎标识<br>值:显示名称',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'archivesratio',
        'title' => '付费文章分成',
        'type' => 'string',
        'content' => [],
        'value' => '1:0',
        'rule' => 'required; config',
        'msg' => '',
        'tip' => '平台:文章作者 <br>请保证两者相加为1',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'score',
        'title' => '获取积分设置',
        'type' => 'array',
        'content' => [],
        'value' => [
            'postarchives' => 2,
            'postcomment' => 0,
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果问题或评论被删除则会扣除相应的积分<br>postarchives:发布文章<br>postcomment:发布评论',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'limitscore',
        'title' => '限定积分设置',
        'type' => 'array',
        'content' => [],
        'value' => [
            'postarchives' => 0,
            'postcomment' => 0,
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '必须达到相应的积分限制条件才可以操作<br>postarchives:发布文章<br>postcomment:发布评论',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'openedsite',
        'title' => '站点前台开关',
        'type' => 'checkbox',
        'content' => [
            'pc' => 'PC',
            'wxapp' => 'Wxapp',
            'uniapp' => 'Uniapp',
        ],
        'value' => 'pc,wxapp,uniapp',
        'rule' => '',
        'msg' => '',
        'tip' => 'Wxapp为微信原生小程序，Uniapp仅为Uniapp版本(包括小程序、H5、APP)',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'userpage',
        'title' => '会员个人主页',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '是否开启会员个人主页功能',
        'extend' => '',
    ],
    [
        'name' => 'domain',
        'title' => '绑定二级域名前缀',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'rewrite',
        'title' => '伪静态',
        'type' => 'array',
        'content' => [],
        'value' => [
            'index/index' => '/cms/$',
            'tag/index' => '/cms/t/[:diyname]$',
            'page/index' => '/cms/p/[:diyname]$',
            'search/index' => '/cms/s$',
            'diyform/index' => '/cms/d/[:diyname]$',
            'diyform/post' => '/cms/d/[:diyname]/post',
            'diyform/show' => '/cms/d/[:diyname]/[:id]',
            'special/index' => '/cms/special/[:diyname]',
            'user/index' => '/u/[:id]',
            'channel/index' => '/cms/[:diyname]$',
            'archives/index' => '/cms/[:catename]/[:id]$',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果需要将CMS绑定到网站首页，请移除<code>值</code>中的<code>/cms</code><br>键:控制器/方法<br>值:伪静态URL',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'wxappid',
        'title' => '微信小程序AppID',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'wxappsecret',
        'title' => '微信小程序AppSecret',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'ispaylogin',
        'title' => '支付是否需要登录',
        'type' => 'radio',
        'content' => [
            1 => '是',
            0 => '否',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '支付时是否需要登录,仅支持PC版本',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'paytypelist',
        'title' => '支付模块',
        'type' => 'checkbox',
        'content' => [
            'wechat' => '微信支付',
            'alipay' => '支付宝',
            'balance' => '余额支付',
        ],
        'value' => 'wechat,alipay,balance',
        'rule' => 'required',
        'msg' => '',
        'tip' => '前台支付开启的模块',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'defaultpaytype',
        'title' => '默认支付模块',
        'type' => 'radio',
        'content' => [
            'wechat' => '微信支付',
            'alipay' => '支付宝',
            'balance' => '余额支付',
        ],
        'value' => 'balance',
        'rule' => 'required',
        'msg' => '',
        'tip' => '前台内容页默认支付方式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'isarchivesaudit',
        'title' => '发布文章审核',
        'type' => 'radio',
        'content' => [
            1 => '全部审核',
            0 => '无需审核',
            -1 => '仅含有过滤词时审核',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'iscommentaudit',
        'title' => '发表评论审核',
        'type' => 'radio',
        'content' => [
            1 => '全部审核',
            0 => '无需审核',
            -1 => '仅含有过滤词时审核',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'iscomment',
        'title' => '全局评论开关',
        'type' => 'radio',
        'content' => [
            1 => '开',
            0 => '关',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'audittype',
        'title' => '审核方式',
        'type' => 'radio',
        'content' => [
            'local' => '本地',
            'baiduyun' => '百度云',
        ],
        'value' => 'local',
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果启用百度云，请输入百度云AI平台应用的AK和SK',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'nlptype',
        'title' => '分词方式',
        'type' => 'radio',
        'content' => [
            'local' => '本地',
            'baiduyun' => '百度云',
        ],
        'value' => 'local',
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果启用百度云，请输入百度云AI平台应用的AK和SK',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'aip_appid',
        'title' => '百度AI平台应用Appid',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '百度云AI开放平台应用AppId',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'aip_apikey',
        'title' => '百度AI平台应用Apikey',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '百度云AI开放平台应用ApiKey',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'aip_secretkey',
        'title' => '百度AI平台应用Secretkey',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '百度云AI开放平台应用Secretkey',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'apikey',
        'title' => 'API密钥',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '用于调用API接口时写入数据权限控制<br>默认为空时表示关闭，如需启用请自行设定值',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'archiveseditmode',
        'title' => '文档编辑模式',
        'type' => 'radio',
        'content' => [
            'addtabs' => '新选项卡',
            'dialog' => '弹窗',
        ],
        'value' => 'dialog',
        'rule' => '',
        'msg' => '',
        'tip' => '在添加或编辑文档时的操作方式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'redirecturl',
        'title' => '是否启用链接中转',
        'type' => 'radio',
        'content' => [
            1 => '是',
            0 => '否',
        ],
        'value' => '1',
        'rule' => '',
        'msg' => '',
        'tip' => '正文中的外部链接是否启用中转',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'redirectseconds',
        'title' => '链接中转等待时间',
        'type' => 'number',
        'content' => [],
        'value' => '-1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '链接中转等待时间，默认为-1表示需手动点击跳转，0表示不等待跳转，单位秒',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'auditnotice',
        'title' => '审核通知',
        'type' => 'radio',
        'content' => [
            'none' => '无需通知',
            'dinghorn' => '钉钉小喇叭',
            'vbot' => '企业微信通知',
            'notice' => '站内消息通知',
        ],
        'value' => 'none',
        'rule' => '',
        'msg' => '',
        'tip' => '如需启用审核通知，务必在插件市场安装对应的插件',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'noticetemplateid',
        'title' => '审核通知模板ID',
        'type' => 'string',
        'content' => [],
        'value' => '1',
        'rule' => '',
        'msg' => '',
        'tip' => '当启用审核通知时，消息通知的模板ID',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'channelallocate',
        'title' => '栏目授权',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '0',
        'rule' => '',
        'msg' => '',
        'tip' => '开启后可以单独给管理员分配可管理的内容栏目',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'archivesdatalimit',
        'title' => '文章数据范围',
        'type' => 'select',
        'content' => [
            'all' => '可查看全部数据',
            'auth' => '仅可查看自己和子级发布的数据',
            'personal' => '仅可查看自己发布的数据',
        ],
        'value' => 'all',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'specialdatalimit',
        'title' => '专题数据范围',
        'type' => 'select',
        'content' => [
            'all' => '可查看全部数据',
            'auth' => '仅可查看自己和子级发布的数据',
            'personal' => '仅可查看自己发布的数据',
        ],
        'value' => 'all',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'pagedatalimit',
        'title' => '单页数据范围',
        'type' => 'select',
        'content' => [
            'all' => '可查看全部数据',
            'auth' => '仅可查看自己和子级发布的数据',
            'personal' => '仅可查看自己发布的数据',
        ],
        'value' => 'all',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'diyformdatalimit',
        'title' => '自定义表单数据范围',
        'type' => 'select',
        'content' => [
            'all' => '可查看全部数据',
            'auth' => '仅可查看自己和子级发布的数据',
            'personal' => '仅可查看自己发布的数据',
        ],
        'value' => 'all',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'realtimereplacelink',
        'title' => '实时内联替换',
        'type' => 'radio',
        'content' => [
            1 => '是',
            0 => '否',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '是否开启实时内联替换',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'autolinks',
        'title' => '关键字链接',
        'type' => 'array',
        'content' => [],
        'value' => [],
        'rule' => '',
        'msg' => '',
        'tip' => '文章中对应的关键字将会自动加上链接<br>键:关键字<br>值:跳转链接',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'searchtype',
        'title' => '搜索方式',
        'type' => 'radio',
        'content' => [
            'local' => '本地搜索，采用Like(无需配置,效率低)',
            'xunsearch' => '采用Xunsearch全文搜索(需安装插件+配置服务器)',
        ],
        'value' => 'local',
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果启用Xunsearch全文搜索，需安装Xunsearch插件并配置Xunsearch服务端',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'autopinyin',
        'title' => '标题自动转拼音',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '1',
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果开启自动转拼音，则在录入文档标题或栏目名称时，自定义名称将自动转换成拼音',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'baidupush',
        'title' => '百度主动推送链接',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '0',
        'rule' => 'required',
        'msg' => '',
        'tip' => '如果开启百度主动推送链接，将在文章发布时自动进行推送，请务必在插件市场安装百度主动推送插件并配置',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'usersidenav',
        'title' => '会员中心边栏模块',
        'type' => 'checkbox',
        'content' => [
            'myhomepage' => '我的个人主页',
            'myarchives' => '我发布的文章',
            'postarchives' => '发布文章',
            'myorder' => '我的消费订单',
            'mycomment' => '我发表的评论',
            'mycollection' => '我的收藏',
        ],
        'value' => 'myhomepage,myarchives,postarchives,myorder,mycomment,mycollection',
        'rule' => '',
        'msg' => '',
        'tip' => '会员中心边栏模块',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'loadmode',
        'title' => '列表页加载模式',
        'type' => 'radio',
        'content' => [
            'infinite' => '无限加载模式',
            'paging' => '分页加载模式',
        ],
        'value' => 'paging',
        'rule' => 'required',
        'msg' => '',
        'tip' => '列表页加载模式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'pagemode',
        'title' => '页码显示模式',
        'type' => 'radio',
        'content' => [
            'simple' => '仅使用上下页',
            'full' => '包含数字分页',
        ],
        'value' => 'full',
        'rule' => 'required',
        'msg' => '',
        'tip' => '分页加载模式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'indexloadmode',
        'title' => '首页最近更新加载模式',
        'type' => 'radio',
        'content' => [
            'infinite' => '无限加载模式',
            'paging' => '分页加载模式',
        ],
        'value' => 'infinite',
        'rule' => 'required',
        'msg' => '',
        'tip' => '首页最近更新加载模式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'indexpagemode',
        'title' => '首页最近更新分页模式',
        'type' => 'radio',
        'content' => [
            'simple' => '仅使用上下页',
            'full' => '包含数字分页',
        ],
        'value' => 'simple',
        'rule' => 'required',
        'msg' => '',
        'tip' => '首页最近更新分页模式',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'cachelifetime',
        'title' => '缓存默认时长',
        'type' => 'string',
        'content' => [],
        'value' => '0',
        'rule' => 'required; config',
        'msg' => '',
        'tip' => '单位为秒，为0表示永久缓存，-1表示不缓存',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'cachelistcount',
        'title' => '缓存列表页总数',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '0',
        'rule' => '',
        'msg' => '',
        'tip' => '大数据建议开启列表页缓存总数',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'flagtype',
        'title' => '标志字典',
        'type' => 'array',
        'content' => [],
        'value' => [
            'hot' => '热门',
            'new' => '新',
            'recommend' => '推荐',
            'top' => '置顶',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '用于文档内容标志配置<br>键:数据库存储值<br>值:显示值',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'urlsuffix',
        'title' => 'URL后缀',
        'type' => 'string',
        'content' => [],
        'value' => 'html',
        'rule' => '',
        'msg' => '如果不需要后缀可以设置为空',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'moduleurlsuffix',
        'title' => '模块URL后缀',
        'type' => 'array',
        'content' => [],
        'value' => [
            'channel' => 'html',
            'archives' => 'html',
            'special' => 'html',
            'page' => 'html',
            'diyform' => 'html',
            'tag' => 'html',
        ],
        'rule' => '',
        'msg' => '如果不需要后缀可以设置为空',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'sitemapcachelifetime',
        'title' => 'Sitemap缓存时长',
        'type' => 'number',
        'content' => [],
        'value' => '-1',
        'rule' => '',
        'msg' => '',
        'tip' => '单位为秒，为0表示永久缓存，-1表示不缓存',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'sitemappagesize',
        'title' => 'Sitemap分页大小',
        'type' => 'number',
        'content' => [],
        'value' => '5000',
        'rule' => '',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'archiveshashids',
        'title' => '是否启用文档ID加密',
        'type' => 'radio',
        'content' => [
            1 => '是',
            0 => '否',
        ],
        'value' => '0',
        'rule' => '',
        'msg' => '',
        'tip' => '若启用文档ID加密，要求伪静态键<code>archives/index</code>对应的值中必须存在<code>[:eid]</code>',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'hashids_key',
        'title' => '文档加密ID密钥',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '默认为空则使用系统Token配置的密钥',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'hashids_key_length',
        'title' => '文档加密ID长度',
        'type' => 'number',
        'content' => [],
        'value' => '10',
        'rule' => '',
        'msg' => '',
        'tip' => '建议加密ID长度大于10位',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'spiderrecord',
        'title' => '搜索引擎蜘蛛来访记录',
        'type' => 'radio',
        'content' => [
            1 => '开启',
            0 => '关闭',
        ],
        'value' => '0',
        'rule' => '',
        'msg' => '',
        'tip' => '开启后可以在搜索引擎记录中查看搜索引擎蜘蛛来访记录',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'spiderfollow',
        'title' => '搜索引擎蜘蛛关注',
        'type' => 'selects',
        'content' => [],
        'value' => 'Baiduspider,Bytespider',
        'rule' => '',
        'msg' => '',
        'tip' => '设定关注的蜘蛛后将在内容列表可查看蜘蛛来访记录',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'app_id',
        'title' => '移动端APP的AppID',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '微信开放平台中移动端应用的Appid，仅Uniapp版本使用',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'app_secret',
        'title' => '移动端APP的AppSecret',
        'type' => 'string',
        'content' => [],
        'value' => '',
        'rule' => '',
        'msg' => '',
        'tip' => '微信开放平台中移动端应用的AppSecret，仅Uniapp版本使用',
        'ok' => '',
        'extend' => '',
    ],
];
