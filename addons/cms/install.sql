
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_addondownload` (
  `id` int(10) NOT NULL,
  `content` longtext NOT NULL,
  `os` set('windows','linux','mac','ubuntu') DEFAULT '' COMMENT '操作系统',
  `version` varchar(255) DEFAULT '' COMMENT '最新版本',
  `filesize` varchar(255) DEFAULT '' COMMENT '文件大小',
  `language` set('zh-cn','en') DEFAULT '' COMMENT '语言',
  `downloadurl` varchar(1500) DEFAULT '' COMMENT '下载地址',
  `screenshots` varchar(1500) DEFAULT '' COMMENT '预览截图',
  `downloads` varchar(10) DEFAULT '0' COMMENT '下载次数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='下载';

--
-- 表的结构 `__PREFIX__cms_addonnews`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_addonnews` (
  `id` int(10) NOT NULL,
  `content` longtext NOT NULL,
  `author` varchar(50) DEFAULT '' COMMENT '作者',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='新闻';

--
-- 表的结构 `__PREFIX__cms_addonproduct`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_addonproduct` (
  `id` int(10) NOT NULL,
  `content` longtext NOT NULL,
  `productdata` varchar(1500) DEFAULT '' COMMENT '产品列表',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='产品表';

--
-- 表的结构 `__PREFIX__cms_archives`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_archives` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `channel_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '栏目ID',
  `channel_ids` varchar(100) DEFAULT '' COMMENT '副栏目ID集合',
  `model_id` int(10) NOT NULL DEFAULT '0' COMMENT '模型ID',
  `special_ids` varchar(100) DEFAULT '' COMMENT '专题ID集合',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `title` varchar(255) DEFAULT '' COMMENT '文章标题',
  `flag` varchar(100) DEFAULT '' COMMENT '标志',
  `style` varchar(100) NULL DEFAULT '' COMMENT '样式',
  `image` varchar(255) DEFAULT '' COMMENT '缩略图',
  `images` varchar(1500) DEFAULT '' COMMENT '组图',
  `seotitle` varchar(255) DEFAULT '' COMMENT 'SEO标题',
  `keywords` varchar(255) DEFAULT '' COMMENT '关键字',
  `description` varchar(255) DEFAULT '' COMMENT '描述',
  `tags` varchar(255) DEFAULT '' COMMENT 'TAG',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '价格',
  `outlink` varchar(255) DEFAULT '' COMMENT '外部链接',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '浏览次数',
  `comments` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论次数',
  `likes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞数',
  `dislikes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点踩数',
  `diyname` varchar(100) DEFAULT '' COMMENT '自定义URL',
  `isguest` tinyint(1) unsigned DEFAULT '1' COMMENT '是否访客访问',
  `iscomment` tinyint(1) unsigned DEFAULT '1' COMMENT '是否允许评论',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `publishtime` bigint(16) DEFAULT NULL COMMENT '发布时间',
  `deletetime` bigint(16) DEFAULT NULL COMMENT '删除时间',
  `memo` varchar(100) DEFAULT '' COMMENT '备注',
  `status` enum('normal','hidden','rejected','pulloff') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `diyname` (`diyname`),
  KEY `channel_id` (`channel_id`),
  KEY `channel_ids` (`channel_ids`),
  KEY `weigh` (`weigh`,`publishtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='内容表';

--
-- 表的结构 `__PREFIX__cms_autolink`
--
CREATE TABLE IF NOT EXISTS `__PREFIX__cms_autolink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `url` varchar(255) DEFAULT '' COMMENT '链接',
  `target` enum('self','blank') DEFAULT 'blank' COMMENT '打开方式',
  `weigh` int(10) DEFAULT '0' COMMENT '排序',
  `views` int(10) unsigned DEFAULT '0' COMMENT '点击次数',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='自动链接表';

--
-- 表的结构 `__PREFIX__cms_block`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_block` (
  `id` smallint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` varchar(30) DEFAULT '' COMMENT '类型',
  `name` varchar(50) DEFAULT '' COMMENT '名称',
  `title` varchar(100) DEFAULT '' COMMENT '标题',
  `image` varchar(255) DEFAULT '' COMMENT '图片',
  `url` varchar(255) DEFAULT '' COMMENT '链接',
  `content` mediumtext COMMENT '内容',
  `parsetpl` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '解析模板标签',
  `weigh` int(10) NULL DEFAULT 0 COMMENT '权重',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `begintime` bigint(16) DEFAULT NULL COMMENT '开始时间',
  `endtime` bigint(16) DEFAULT NULL COMMENT '结束时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='区块表';

--
-- 表的结构 `__PREFIX__cms_channel`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_channel` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('channel','page','link','list') NOT NULL COMMENT '类型',
  `model_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '模型ID',
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父ID',
  `name` varchar(30) DEFAULT '' COMMENT '名称',
  `image` varchar(255) DEFAULT '' COMMENT '图片',
  `flag` varchar(100) DEFAULT '' COMMENT '标志',
  `seotitle` varchar(255) DEFAULT '' COMMENT 'SEO标题',
  `keywords` varchar(255) DEFAULT '' COMMENT '关键字',
  `description` varchar(255) DEFAULT '' COMMENT '描述',
  `diyname` varchar(100) DEFAULT '' COMMENT '自定义名称',
  `outlink` varchar(255) DEFAULT '' COMMENT '外部链接',
  `linktype` varchar(100) DEFAULT '' COMMENT '链接类型',
  `linkid` int(10) DEFAULT '0' COMMENT '链接ID',
  `items` mediumint(8) UNSIGNED NOT NULL DEFAULT '0' COMMENT '文章数量',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `channeltpl` varchar(100) DEFAULT '' COMMENT '栏目页模板',
  `listtpl` varchar(100) DEFAULT '' COMMENT '列表页模板',
  `showtpl` varchar(100) DEFAULT '' COMMENT '详情页模板',
  `pagesize` smallint(5) NOT NULL DEFAULT '0' COMMENT '分页大小',
  `vip` tinyint(1) UNSIGNED NULL DEFAULT '0' COMMENT 'VIP',
  `listtype` tinyint(1) unsigned DEFAULT '0' COMMENT '列表数据类型',
  `iscontribute` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可投稿',
  `isnav` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否导航显示',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `diyname` (`diyname`),
  KEY `type` (`type`),
  KEY `weigh` (`weigh`,`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='栏目表';

--
-- 表的结构 `__PREFIX__cms_channel_admin`
--

CREATE TABLE `__PREFIX__cms_channel_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '栏目ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_id` (`admin_id`,`channel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='栏目权限表';

--
-- 表的结构 `__PREFIX__cms_collection`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_collection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('archives','special','page','diyform') DEFAULT NULL COMMENT '类型',
  `aid` int(10) unsigned DEFAULT '0' COMMENT '关联ID',
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `title` varchar(255) DEFAULT NULL COMMENT '收藏标题',
  `image` varchar(255) DEFAULT NULL COMMENT '图片',
  `url` varchar(255) DEFAULT NULL COMMENT 'URL',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `aid` (`type`,`aid`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

--
-- 表的结构 `__PREFIX__cms_comment`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_comment` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '会员ID',
  `type` enum('archives','page', 'special') NOT NULL DEFAULT 'archives' COMMENT '类型',
  `aid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '关联ID',
  `pid` int(10) NOT NULL DEFAULT '0' COMMENT '父ID',
  `content` longtext COMMENT '内容',
  `comments` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论数',
  `ip` varchar(50) DEFAULT '' COMMENT 'IP',
  `useragent` varchar(255) DEFAULT '' COMMENT 'User Agent',
  `subscribe` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '订阅',
  `createtime` bigint(16) UNSIGNED NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` bigint(16) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
  `deletetime` bigint(16) DEFAULT NULL COMMENT '删除时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `post_id` (`aid`,`pid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='评论表';

--
-- 表的结构 `__PREFIX__cms_diyform`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_diyform`(
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `name` char(30) DEFAULT '' COMMENT '表单名称',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `seotitle` varchar(255) DEFAULT '' COMMENT 'SEO标题',
  `posttitle` varchar(255) DEFAULT '' COMMENT '发布标题',
  `keywords` varchar(100) DEFAULT NULL COMMENT '关键字',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `table` varchar(50) DEFAULT '' COMMENT '表名',
  `fields` text COMMENT '字段列表',
  `isguest` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否访客访问',
  `needlogin` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否需要登录发布',
  `isedit` tinyint(1) unsigned DEFAULT '0' COMMENT '是否允许编辑',
  `iscaptcha` tinyint(1) unsigned DEFAULT '0' COMMENT '是否启用验证码',
  `successtips` varchar(255) DEFAULT NULL COMMENT '成功提示文字',
  `redirecturl` varchar(255) DEFAULT NULL COMMENT '成功后跳转链接',
  `posttpl` varchar(50) DEFAULT '' COMMENT '表单页模板',
  `listtpl` varchar(50) DEFAULT '' COMMENT '列表页模板',
  `showtpl` varchar(50) DEFAULT '' COMMENT '详情页模板',
  `diyname` varchar(100) DEFAULT NULL COMMENT '自定义名称',
  `usermode` enum('all','user') DEFAULT 'all' COMMENT '用户筛选模式',
  `statusmode` enum('all','normal','hidden') DEFAULT 'all' COMMENT '状态筛选模式',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `setting` varchar(1500) DEFAULT NULL COMMENT '表单配置',
  `status` enum('normal','hidden') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `diyname` (`diyname`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='自定义表单表';

--
-- 表的结构 `__PREFIX__cms_fields`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_fields` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source` varchar(30) DEFAULT '' COMMENT '来源',
  `source_id` int(10) NOT NULL DEFAULT '0' COMMENT '来源ID',
  `name` char(30) DEFAULT '' COMMENT '名称',
  `type` varchar(30) DEFAULT '' COMMENT '类型',
  `title` varchar(30) DEFAULT '' COMMENT '标题',
  `content` text COMMENT '内容',
  `defaultvalue` varchar(100) DEFAULT '' COMMENT '默认值',
  `rule` varchar(100) DEFAULT '' COMMENT '验证规则',
  `msg` varchar(100) DEFAULT '' COMMENT '错误消息',
  `ok` varchar(100) DEFAULT '' COMMENT '成功消息',
  `tip` varchar(100) DEFAULT '' COMMENT '提示消息',
  `decimals` tinyint(1) DEFAULT NULL COMMENT '小数点',
  `length` mediumint(8) DEFAULT NULL COMMENT '长度',
  `minimum` smallint(6) DEFAULT NULL COMMENT '最小数量',
  `maximum` smallint(6) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最大数量',
  `extend` varchar(255) DEFAULT '' COMMENT '扩展信息',
  `setting` varchar(1500) DEFAULT '' COMMENT '配置信息',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `isorder` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可排序',
  `iscontribute` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否可投稿',
  `isfilter` tinyint(1) NOT NULL DEFAULT '0' COMMENT '筛选',
  `status` enum('normal','hidden') NOT NULL COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `source` (`source`) USING BTREE,
  KEY `source_id` (`source_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='模型字段表';

--
-- 表的结构 `__PREFIX__cms_message`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `name` varchar(50) DEFAULT '' COMMENT '姓名',
  `telephone` varchar(100) DEFAULT '' COMMENT '电话',
  `qq` varchar(30) DEFAULT '' COMMENT 'QQ',
  `content` longtext COMMENT '内容',
  `os` enum('windows','mac') DEFAULT 'windows' COMMENT '操作系统',
  `language` set('zh-cn','en') DEFAULT '' COMMENT '语言',
  `address` varchar(255) DEFAULT '' COMMENT '地区',
  `category` varchar(255) DEFAULT '' COMMENT '分类',
  `memo` varchar(255) DEFAULT '' COMMENT '备注',
  `image` varchar(500) DEFAULT '' COMMENT '图片',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden','rejected') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='站内留言';

--
-- 表的结构 `__PREFIX__cms_friendlink`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_friendlink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `title` varchar(255) DEFAULT '' COMMENT '站点名称',
  `image` varchar(1500) DEFAULT '' COMMENT '站点Logo',
  `website` varchar(100) DEFAULT '' COMMENT '站点链接',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `memo` varchar(1500) DEFAULT '' COMMENT '备注',
  `status` enum('normal','hidden','rejected') DEFAULT 'hidden' COMMENT '状态',
  `intro` varchar(255) DEFAULT '' COMMENT '站点介绍',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='友情链接';

--
-- 表的结构 `__PREFIX__cms_navigation`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_navigation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `title` varchar(255) DEFAULT '' COMMENT '标题',
  `image` varchar(255) DEFAULT '' COMMENT '图片',
  `website` varchar(255) DEFAULT '' COMMENT '导航链接',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `memo` varchar(1500) DEFAULT '' COMMENT '备注',
  `status` enum('normal','hidden','rejected') DEFAULT 'hidden' COMMENT '状态',
  `intro` varchar(255) DEFAULT '' COMMENT '介绍',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='热门导航';

--
-- 表的结构 `__PREFIX__cms_model`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_model` (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(30) DEFAULT '' COMMENT '模型名称',
  `table` char(20) DEFAULT '' COMMENT '表名',
  `fields` text COMMENT '字段列表',
  `channeltpl` varchar(100) DEFAULT '' COMMENT '栏目页模板',
  `listtpl` varchar(100) DEFAULT '' COMMENT '列表页模板',
  `showtpl` varchar(100) DEFAULT '' COMMENT '详情页模板',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `setting` text COMMENT '模型配置',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='内容模型表';

--
-- 表的结构 `__PREFIX__cms_order`
--

CREATE TABLE `__PREFIX__cms_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `orderid` varchar(50) DEFAULT '' COMMENT '订单ID',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `archives_id` int(10) unsigned DEFAULT '0' COMMENT '文档ID',
  `title` varchar(100) DEFAULT NULL COMMENT '订单标题',
  `amount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '订单金额',
  `payamount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '支付金额',
  `paytype` varchar(50) DEFAULT NULL COMMENT '支付类型',
  `paytime` bigint(16) DEFAULT NULL COMMENT '支付时间',
  `method` varchar(100) NULL DEFAULT '' COMMENT '支付方法',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `useragent` varchar(255) DEFAULT NULL COMMENT 'UserAgent',
  `memo` varchar(255) DEFAULT NULL COMMENT '备注',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('created','paid','expired') DEFAULT 'created' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `archives_id` (`archives_id`),
  KEY `orderid` (`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='订单表';

--
-- 表的结构 `__PREFIX__cms_page`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_page` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `category_id` int(10) NOT NULL DEFAULT '0' COMMENT '分类ID',
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `type` varchar(50) DEFAULT '' COMMENT '类型',
  `title` varchar(50) DEFAULT '' COMMENT '标题',
  `seotitle` varchar(255) DEFAULT '' COMMENT 'SEO标题',
  `keywords` varchar(255) DEFAULT '' COMMENT '关键字',
  `description` varchar(255) DEFAULT '' COMMENT '描述',
  `flag` varchar(100) DEFAULT '' COMMENT '标志',
  `image` varchar(255) DEFAULT '' COMMENT '头像',
  `content` longtext COMMENT '内容',
  `icon` varchar(50) DEFAULT '' COMMENT '图标',
  `views` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点击',
  `likes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点赞',
  `dislikes` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '点踩',
  `comments` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '评论',
  `diyname` varchar(100) DEFAULT '' COMMENT '自定义',
  `showtpl` varchar(50) DEFAULT '' COMMENT '视图模板',
  `iscomment` tinyint(1) unsigned DEFAULT '1' COMMENT '是否允许评论',
  `parsetpl` tinyint(1) UNSIGNED NULL DEFAULT '0' COMMENT '解析模板标签',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint(16) DEFAULT NULL COMMENT '删除时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `status` varchar(30) DEFAULT '' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `diyname` (`diyname`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='单页表';

--
-- 表的结构 `__PREFIX__cms_search_log`
--
CREATE TABLE `__PREFIX__cms_search_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keywords` varchar(100) CHARACTER SET utf8mb4 DEFAULT '' COMMENT '关键字',
  `nums` int(10) unsigned DEFAULT '0' COMMENT '搜索次数',
  `createtime` bigint(16) DEFAULT NULL COMMENT '搜索时间',
  `status` varchar(50) DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `keywords` (`keywords`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='搜索记录表';

--
-- 表的结构 `__PREFIX__cms_special`
--

CREATE TABLE `__PREFIX__cms_special` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(10) unsigned DEFAULT '0' COMMENT '管理员ID',
  `title` varchar(100) DEFAULT '' COMMENT '标题',
  `tag_ids` varchar(1500) NULL DEFAULT '' COMMENT '标签ID集合',
  `flag` varchar(100) DEFAULT '' COMMENT '标志',
  `label` varchar(50) DEFAULT '' COMMENT '标签',
  `image` varchar(255) DEFAULT '' COMMENT '图片',
  `banner` varchar(255) DEFAULT '' COMMENT 'Banner图片',
  `diyname` varchar(100) DEFAULT '' COMMENT '自定义名称',
  `seotitle` varchar(255) DEFAULT '' COMMENT 'SEO标题',
  `keywords` varchar(100) DEFAULT NULL COMMENT '关键字',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `intro` varchar(255) DEFAULT NULL COMMENT '专题介绍',
  `views` int(10) unsigned DEFAULT '0' COMMENT '浏览次数',
  `comments` int(10) unsigned DEFAULT '0' COMMENT '评论次数',
  `iscomment` tinyint(1) unsigned DEFAULT '1' COMMENT '是否允许评论',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `deletetime` bigint(16) DEFAULT NULL COMMENT '删除时间',
  `template` varchar(100) DEFAULT '' COMMENT '专题模板',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `diyname` (`diyname`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='专题表';

--
-- 表的结构 `__PREFIX__cms_spider_log`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_spider_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('index','archives','page','special','channel','diyform','tag','user') DEFAULT NULL COMMENT '类型',
  `aid` int(10) DEFAULT '0' COMMENT '关联ID',
  `name` varchar(50) DEFAULT '' COMMENT '名称',
  `url` varchar(255) DEFAULT '' COMMENT '来访页面',
  `nums` int(10) unsigned DEFAULT '0' COMMENT '来访次数',
  `firsttime` bigint(16) DEFAULT NULL COMMENT '首次来访时间',
  `lastdata` varchar(100) DEFAULT '' COMMENT '最后5次来访时间',
  `lasttime` bigint(16) DEFAULT NULL COMMENT '最后来访时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COMMENT='搜索引擎来访记录';

--
-- 表的结构 `__PREFIX__cms_tag`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_tag` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '' COMMENT '标签名称',
  `nums` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文档数量',
  `seotitle` varchar(100) DEFAULT '' COMMENT 'SEO标题',
  `keywords` varchar(255) DEFAULT NULL COMMENT '关键字',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `views` int(10) DEFAULT NULL COMMENT '浏览次数',
  `autolink` tinyint(1) unsigned DEFAULT 0 COMMENT '自动内链',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE,
  KEY `nums` (`nums`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='标签表';

--
-- 表的结构 `__PREFIX__cms_taggable`
--

CREATE TABLE IF NOT EXISTS `__PREFIX__cms_taggable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(10) DEFAULT NULL COMMENT '标签ID',
  `archives_id` int(10) DEFAULT NULL COMMENT '文档ID',
  `createtime` bigint(16) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `tag_id` (`tag_id`),
  KEY `archives_id` (`archives_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='标签列表';

-- 1.3.5 --
ALTER TABLE `__PREFIX__cms_tag` ADD COLUMN `autolink` tinyint(1) unsigned DEFAULT 0 COMMENT '自动内链' AFTER `views`;

-- 1.3.6 --
ALTER TABLE `__PREFIX__cms_order` ADD COLUMN `method` varchar(100) NULL DEFAULT '' COMMENT '支付方法' AFTER `paytime`;
ALTER TABLE `__PREFIX__cms_page` ADD COLUMN `parsetpl` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '解析模板标签' AFTER `iscomment`;
ALTER TABLE `__PREFIX__cms_block` ADD COLUMN `parsetpl` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '解析模板标签' AFTER `content`;

-- 1.3.7 --
ALTER TABLE `__PREFIX__cms_channel` ADD COLUMN `vip` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT 'VIP' AFTER `pagesize`;
ALTER TABLE `__PREFIX__cms_channel` ADD COLUMN `listtype` tinyint(1) unsigned DEFAULT '0' COMMENT '列表数据类型' AFTER `vip`;

-- 1.4.0 --
ALTER TABLE `__PREFIX__cms_archives` ADD COLUMN `price` decimal(10, 2) UNSIGNED NULL DEFAULT 0 COMMENT '价格' AFTER `description`,ADD COLUMN `outlink` varchar(255) NULL DEFAULT '' COMMENT '外部链接' AFTER `price`;
ALTER TABLE `__PREFIX__cms_diyform` ADD COLUMN `iscaptcha` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否启用验证码' AFTER `isedit`;
ALTER TABLE `__PREFIX__cms_archives` ADD INDEX(`channel_id`), ADD INDEX(`channel_ids`), ADD INDEX(`diyname`);
ALTER TABLE `__PREFIX__cms_channel` ADD INDEX(`type`);
ALTER TABLE `__PREFIX__cms_page` ADD INDEX(`diyname`);
ALTER TABLE `__PREFIX__cms_special` ADD INDEX(`diyname`);
ALTER TABLE `__PREFIX__cms_order` ADD INDEX(`orderid`);
ALTER TABLE `__PREFIX__cms_fields` ADD COLUMN `filterlist` text NULL COMMENT '筛选列表' AFTER `content`;

-- 1.4.3 --
ALTER TABLE `__PREFIX__cms_collection` DROP INDEX `aid`,ADD UNIQUE INDEX `aid`(`type`, `aid`, `user_id`) USING BTREE;

-- 1.4.4 --
ALTER TABLE `__PREFIX__cms_diyform` ADD COLUMN `isguest` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否访客访问' AFTER `fields`,MODIFY COLUMN `needlogin` tinyint(1) NOT NULL DEFAULT 0 COMMENT '是否需要登录发布' AFTER `isguest`;
ALTER TABLE `__PREFIX__cms_block` ADD INDEX(`name`);
ALTER TABLE `__PREFIX__cms_archives` MODIFY COLUMN `channel_ids` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '副栏目ID集合' AFTER `channel_id`,DROP INDEX `channel`,DROP INDEX `channel_id`,DROP INDEX `channel_ids`,DROP INDEX `status`,ADD INDEX `model_id`(`model_id`, `channel_id`, `channel_ids`) USING BTREE;

-- 1.5.0 --
ALTER TABLE `__PREFIX__cms_archives` MODIFY COLUMN `special_ids` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '专题IDID集合' AFTER `channel_ids`,DROP INDEX `model_id`,DROP INDEX `weigh`,ADD INDEX `channel_id`(`channel_id`),ADD INDEX `channel_ids`(`channel_ids`),ADD INDEX `weigh`(`weight`, `publishtime`) USING BTREE;
ALTER TABLE `__PREFIX__cms_channel` ADD COLUMN `linktype` varchar(100) NULL DEFAULT '' COMMENT '链接类型' AFTER `outlink`,ADD COLUMN `linkid` int(10) NULL DEFAULT 0 COMMENT '链接ID' AFTER `linktype`;
ALTER TABLE `__PREFIX__cms_search_log` ADD COLUMN `createtime` bigint(16) NULL COMMENT '搜索时间' AFTER `nums`,ADD COLUMN `status` varchar(50) NULL DEFAULT 'hidden' COMMENT '状态' AFTER `createtime`;

-- 1.5.4 --
ALTER TABLE `__PREFIX__cms_diyform` ADD COLUMN `posttitle` varchar(255) NULL DEFAULT '' COMMENT '发布标题' AFTER `seotitle`;
ALTER TABLE `__PREFIX__cms_fields` MODIFY COLUMN `msg` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '错误消息' AFTER `rule`,MODIFY COLUMN `ok` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '成功消息' AFTER `msg`;
