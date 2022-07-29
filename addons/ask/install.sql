
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for fa_ask_answer
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_answer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父ID',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `question_id` int(10) unsigned DEFAULT '0' COMMENT '问题ID',
  `reply_user_id` int(10) unsigned DEFAULT '0' COMMENT '回复会员ID',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '付费查看金额',
  `score` int(10) unsigned DEFAULT '0' COMMENT '付费查看积分',
  `content` text COMMENT '回答内容',
  `content_fmt` text COMMENT '富文本内容',
  `voteup` int(10) unsigned DEFAULT '0' COMMENT '点赞次数',
  `votedown` int(10) unsigned DEFAULT '0' COMMENT '点踩次数',
  `sales` int(10) unsigned DEFAULT '0' COMMENT '付费次数',
  `comments` int(10) unsigned DEFAULT '0' COMMENT '评论数量',
  `shares` int(10) unsigned DEFAULT '0' COMMENT '分享次数',
  `collections` int(10) unsigned DEFAULT '0' COMMENT '收藏次数',
  `thanks` int(10) unsigned DEFAULT '0' COMMENT '感谢次数',
  `reports` int(10) unsigned DEFAULT '0' COMMENT '举报次数',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `adopttime` int(10) DEFAULT NULL COMMENT '采纳时间',
  `status` enum('normal','hidden','closed') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `question_id` (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答回答表';

-- ----------------------------
-- Table structure for fa_ask_article
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `category_id` int(10) DEFAULT NULL COMMENT '分类ID',
  `zone_id` int(10) unsigned DEFAULT 0 COMMENT '专区ID',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `style` varchar(100) DEFAULT '' COMMENT '样式',
  `keywords` varchar(100) DEFAULT NULL COMMENT '关键字',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `summary` varchar(255) DEFAULT NULL COMMENT '摘要',
  `flag` set('index','hot','recommend','top') DEFAULT NULL COMMENT '标志',
  `image` varchar(100) DEFAULT NULL COMMENT '图片',
  `images` varchar(1500) DEFAULT '' COMMENT '组图',
  `content` text COMMENT '内容',
  `content_fmt` text COMMENT '富文本内容',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '付费金额',
  `score` int(10) unsigned DEFAULT '0' COMMENT '付费积分',
  `comments` int(10) unsigned DEFAULT '0' COMMENT '评论次数',
  `voteup` int(10) unsigned DEFAULT '0' COMMENT '点赞次数',
  `sales` int(10) unsigned DEFAULT '0' COMMENT '付费次数',
  `shares` int(10) unsigned DEFAULT '0' COMMENT '分享次数',
  `views` int(10) unsigned DEFAULT '0' COMMENT '浏览次数',
  `thanks` int(10) unsigned DEFAULT '0' COMMENT '感谢次数',
  `reports` int(10) unsigned DEFAULT '0' COMMENT '举报次数',
  `collections` int(10) unsigned DEFAULT '0' COMMENT '收藏次数',
  `weigh` int(10) DEFAULT '0' COMMENT '权重',
  `isanonymous` tinyint(1) unsigned DEFAULT '0' COMMENT '是否匿名发文',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `status` enum('normal','hidden') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `createtime` (`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答文章表';

-- ----------------------------
-- Table structure for fa_ask_attention
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_attention` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` varchar(50) DEFAULT NULL COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答关注表';

-- ----------------------------
-- Table structure for fa_ask_block
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_block` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(30) NOT NULL DEFAULT '' COMMENT '类型',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '名称',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `image` varchar(100) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(100) NOT NULL DEFAULT '' COMMENT '链接',
  `content` mediumtext COMMENT '内容',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) DEFAULT 0 COMMENT '权重',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='问答区块表';

-- ----------------------------
-- Table structure for fa_ask_category
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父ID',
  `type` enum('question','article','tag', 'expert') DEFAULT 'question' COMMENT '类型',
  `icon` varchar(50) DEFAULT NULL COMMENT '图标',
  `color` varchar(50) DEFAULT 'default' COMMENT '颜色',
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `intro` varchar(500) DEFAULT NULL COMMENT '介绍',
  `weigh` int(10) DEFAULT '0' COMMENT '权重',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='问答分类表';

-- ----------------------------
-- Table structure for fa_ask_certification
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_certification` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `category_id` int(10) unsigned DEFAULT '0' COMMENT '分类ID',
  `title` varchar(30) DEFAULT '' COMMENT '认证称号',
  `ability` varchar(100) DEFAULT NULL COMMENT '技能',
  `qq` varchar(100) DEFAULT NULL COMMENT 'QQ号',
  `works` varchar(255) DEFAULT NULL COMMENT '作品集',
  `intro` varchar(255) DEFAULT NULL COMMENT '个人介绍',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP',
  `memo` varchar(100) DEFAULT NULL COMMENT '原因',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `handletime` int(10) DEFAULT NULL COMMENT '处理时间',
  `status` enum('hidden','rejected','agreed') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='问答认证表';

-- ----------------------------
-- Table structure for fa_ask_collection
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_collection` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `type` enum('question','article','answer') DEFAULT 'question' COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`type`,`source_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答收藏表';

-- ----------------------------
-- Table structure for fa_ask_comment
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_comment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('question','article','answer') DEFAULT NULL COMMENT '类型',
  `source_id` int(10) DEFAULT NULL COMMENT '来源ID',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `reply_user_id` int(10) unsigned DEFAULT '0' COMMENT '回复会员ID',
  `content` text COMMENT '回答内容',
  `content_fmt` text COMMENT '富文本内容',
  `voteup` int(10) unsigned DEFAULT '0' COMMENT '点赞次数',
  `votedown` int(10) unsigned DEFAULT '0' COMMENT '点踩次数',
  `comments` int(10) unsigned DEFAULT '0' COMMENT '评论数量',
  `shares` int(10) unsigned DEFAULT '0' COMMENT '分享次数',
  `collections` int(10) unsigned DEFAULT '0' COMMENT '收藏次数',
  `thanks` int(10) unsigned DEFAULT '0' COMMENT '感谢次数',
  `reports` int(10) unsigned DEFAULT '0' COMMENT '举报次数',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `status` enum('normal','hidden') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答评论表';

-- ----------------------------
-- Table structure for fa_ask_feed
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_feed` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `action` varchar(50) DEFAULT NULL COMMENT '动作',
  `type` enum('question','article','answer','comment') NOT NULL DEFAULT 'question' COMMENT '类型',
  `source_id` varchar(50) NOT NULL DEFAULT '' COMMENT '来源ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `content` mediumtext COMMENT '内容',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答动态表';

-- ----------------------------
-- Table structure for fa_ask_invite
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_invite` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '邀请人ID',
  `invite_user_id` int(10) unsigned DEFAULT '0' COMMENT '被邀请人ID',
  `question_id` int(10) unsigned DEFAULT '0' COMMENT '问题ID',
  `email` varchar(50) DEFAULT NULL COMMENT '邮箱',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '金额',
  `isanswered` tinyint(1) unsigned DEFAULT '0' COMMENT '是否已回答',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `invite_user_id` (`invite_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答邀请回答表';

-- ----------------------------
-- Table structure for fa_ask_message
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送会员ID',
  `to_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收会员ID',
  `content` text COLLATE utf8_unicode_ci NOT NULL COMMENT '消息内容',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读',
  `isfromdeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否发送方删除',
  `istodeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否接收方删除',
  `createtime` int(10) NOT NULL COMMENT '添加时间',
  `updatetime` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `from_user_id` (`from_user_id`,`isfromdeleted`),
  KEY `to_user_id` (`to_user_id`,`istodeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答消息表';

-- ----------------------------
-- Table structure for fa_ask_notification
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_notification` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送会员ID',
  `to_user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '接收会员ID',
  `action` varchar(50) DEFAULT NULL COMMENT '事件',
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '类型',
  `source_id` int(10) unsigned NOT NULL COMMENT '来源ID',
  `title` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题',
  `content` text COLLATE utf8_unicode_ci COMMENT '内容',
  `isread` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否已读',
  `createtime` int(10) NOT NULL COMMENT '添加时间',
  `updatetime` int(10) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `notifications_to_user_id_index` (`to_user_id`),
  KEY `notifications_source_id_index` (`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答通知表';

-- ----------------------------
-- Table structure for fa_ask_order
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` enum('question','article','answer','thanks') DEFAULT NULL COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `title` varchar(100) DEFAULT NULL COMMENT '订单标题',
  `amount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '金额',
  `payamount` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '支付金额',
  `currency` enum('money','score') NULL DEFAULT 'money' COMMENT '货币类型',
  `memo` varchar(100) DEFAULT NULL COMMENT '备注',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP',
  `useragent` varchar(255) DEFAULT NULL COMMENT 'UserAgent',
  `paytype` varchar(50) DEFAULT NULL COMMENT '支付类型',
  `method` varchar(100) DEFAULT NULL COMMENT '支付方法',
  `paytime` int(10) DEFAULT NULL COMMENT '支付时间',
  `createtime` int(10) DEFAULT NULL COMMENT '创建时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` enum('created','paid') DEFAULT 'created' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderid` (`orderid`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答付费订单';

-- ----------------------------
-- Table structure for fa_ask_question
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_question` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `category_id` int(10) DEFAULT NULL COMMENT '分类ID',
  `zone_id` int(10) unsigned DEFAULT 0 COMMENT '专区ID',
  `images` varchar(1500) DEFAULT '' COMMENT '组图',
  `title` varchar(100) DEFAULT NULL COMMENT '标题',
  `style` varchar(100) DEFAULT '' COMMENT '样式',
  `price` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '悬赏金额',
  `score` decimal(10,2) unsigned DEFAULT '0' COMMENT '悬赏积分',
  `flag` set('index','hot','recommend','top') DEFAULT NULL COMMENT '标志',
  `voteup` int(10) unsigned DEFAULT '0' COMMENT '点赞次数',
  `votedown` int(10) unsigned DEFAULT '0' COMMENT '点踩次数',
  `followers` int(10) unsigned DEFAULT '0' COMMENT '关注次数',
  `views` int(10) unsigned DEFAULT '0' COMMENT '浏览次数',
  `collections` int(10) unsigned DEFAULT '0' COMMENT '收藏次数',
  `thanks` int(10) unsigned DEFAULT '0' COMMENT '感谢次数',
  `reports` int(10) unsigned DEFAULT '0' COMMENT '举报次数',
  `answers` int(10) unsigned DEFAULT '0' COMMENT '回答次数',
  `comments` int(10) unsigned DEFAULT '0' COMMENT '评论次数',
  `peeps` int(10) unsigned DEFAULT '0' COMMENT '偷看次数',
  `content` text COMMENT '问题内容',
  `content_fmt` text COMMENT '富文本内容',
  `best_answer_id` int(10) unsigned DEFAULT '0' COMMENT '最佳回复',
  `isanonymous` tinyint(1) unsigned DEFAULT '0' COMMENT '是否匿名发文',
  `isprivate` tinyint(1) unsigned DEFAULT '0' COMMENT '是否私有答案',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `rewardtime` int(10) DEFAULT NULL COMMENT '悬赏时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `status` enum('normal','hidden','solved','closed') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答问题表';

-- ----------------------------
-- Table structure for fa_ask_report
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` enum('question','article','answer','tag','comment') DEFAULT NULL COMMENT '类型',
  `source_id` int(10) DEFAULT NULL COMMENT '来源ID',
  `reason` tinyint(1) unsigned DEFAULT NULL COMMENT '原因',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  `useragent` varchar(255) DEFAULT NULL COMMENT 'UserAgent',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `status` enum('hidden','normal') DEFAULT NULL COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`type`,`source_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答举报表';

-- ----------------------------
-- Table structure for fa_ask_score
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_score` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT '0' COMMENT '会员ID',
  `type` varchar(100) DEFAULT '' COMMENT '类型',
  `score` int(10) DEFAULT NULL COMMENT '积分',
  `date` varchar(8) DEFAULT '' COMMENT '年月日',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`type`,`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='问答积分统计';

-- ----------------------------
-- Table structure for fa_ask_tag
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned DEFAULT '0' COMMENT '分类ID',
  `zone_id` int(10) unsigned DEFAULT '0' COMMENT '专区ID',
  `flag` set('index','recommend','hot') DEFAULT NULL COMMENT '标志',
  `name` varchar(50) DEFAULT '' COMMENT '名称',
  `diyname` varchar(50) DEFAULT NULL COMMENT '自定义名称',
  `image` varchar(100) DEFAULT NULL COMMENT '图片',
  `icon` varchar(100) DEFAULT NULL COMMENT '图标',
  `intro` varchar(255) DEFAULT NULL COMMENT '介绍',
  `questions` int(10) DEFAULT '0' COMMENT '问题数',
  `articles` int(10) DEFAULT '0' COMMENT '文章数',
  `followers` int(10) DEFAULT '0' COMMENT '关注人数',
  `reports` int(10) unsigned DEFAULT '0' COMMENT '举报次数',
  `weigh` int(10) DEFAULT '0' COMMENT '权重',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `deletetime` int(10) DEFAULT NULL COMMENT '删除时间',
  `status` enum('normal','hidden') DEFAULT 'normal' COMMENT '状态',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答话题表';

-- ----------------------------
-- Table structure for fa_ask_taggable
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_taggable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` int(10) unsigned DEFAULT '0' COMMENT '话题ID',
  `type` varchar(50) DEFAULT NULL COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  KEY `name` (`tag_id`) USING BTREE,
  KEY `topicable_id` (`source_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答话题关联表';

-- ----------------------------
-- Table structure for fa_ask_thanks
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_thanks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `type` enum('question','article','answer') DEFAULT 'question' COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `money` decimal(10,2) DEFAULT NULL COMMENT '金额',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `paytype` varchar(50) DEFAULT NULL COMMENT '支付类型',
  `paytime` int(10) DEFAULT NULL COMMENT '支付时间',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` enum('created','paid') DEFAULT 'created' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`type`,`source_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答感谢表';

-- ----------------------------
-- Table structure for fa_ask_user
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_user` (
  `user_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned DEFAULT '0' COMMENT '分类ID',
  `flag` set('index','recommend','new') DEFAULT NULL COMMENT '标志',
  `followers` int(10) DEFAULT '0' COMMENT '关注数',
  `questions` int(10) DEFAULT '0' COMMENT '问题数',
  `answers` int(10) DEFAULT '0' COMMENT '回答数',
  `comments` int(10) DEFAULT '0' COMMENT '评论数',
  `articles` int(10) DEFAULT '0' COMMENT '文章数',
  `collections` int(10) DEFAULT '0' COMMENT '收藏数',
  `adoptions` int(10) DEFAULT '0' COMMENT '被采纳数',
  `views` int(10) DEFAULT '0' COMMENT '被浏览数',
  `isadmin` tinyint(1) unsigned DEFAULT '0' COMMENT '是否管理员',
  `isexpert` tinyint(1) unsigned DEFAULT '0' COMMENT '是否认证专家',
  `experttitle` varchar(50) DEFAULT NULL COMMENT '认证标题',
  `invites` int(10) DEFAULT '0' COMMENT '邀请数',
  `messages` int(10) DEFAULT '0' COMMENT '私信数',
  `notifications` int(10) DEFAULT '0' COMMENT '通知数',
  `unadopted` int(10) unsigned DEFAULT '0' COMMENT '未采纳',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答会员表';

-- ----------------------------
-- Table structure for fa_ask_vote
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_vote` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) DEFAULT NULL COMMENT '会员ID',
  `type` varchar(50) DEFAULT NULL COMMENT '类型',
  `source_id` int(10) unsigned DEFAULT '0' COMMENT '来源ID',
  `value` enum('up','down') DEFAULT 'up' COMMENT '结果',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`type`,`source_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答投票表';

-- ----------------------------
-- Table structure for fa_ask_zone
-- ----------------------------
CREATE TABLE IF NOT EXISTS `__PREFIX__ask_zone` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '名称',
  `diyname` varchar(50) DEFAULT NULL COMMENT '自定义名称',
  `image` varchar(100) DEFAULT NULL COMMENT '图片',
  `intro` varchar(255) DEFAULT NULL COMMENT '介绍',
  `productid` varchar(100) DEFAULT '' COMMENT '产品ID',
  `productname` varchar(100) DEFAULT NULL COMMENT '产品名称',
  `producturl` varchar(255) DEFAULT '' COMMENT '产品链接',
  `condition` varchar(1500) DEFAULT '' COMMENT '专区条件',
  `views` int(10) unsigned DEFAULT '0' COMMENT '浏览次数',
  `weigh` int(10) DEFAULT '0' COMMENT '权重',
  `isnav` tinyint(1) unsigned DEFAULT '0' COMMENT '是否导航',
  `createtime` int(10) DEFAULT NULL COMMENT '添加时间',
  `updatetime` int(10) DEFAULT NULL COMMENT '更新时间',
  `status` enum('normal','hidden') DEFAULT 'hidden' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `diyname` (`diyname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='问答专区表';

-- ----------------------------
-- Records of fa_ask_block
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__ask_block` VALUES (1, 'other', 'sidebarad1', '边栏广告1', '/assets/addons/ask/img/sidebar/howto.png', 'http://www.fastadmin.net', '', 1553821881, 1553821881, 0, 'normal');
INSERT INTO `__PREFIX__ask_block` VALUES (2, 'other', 'sidebarad2', '边栏广告2', '/assets/addons/ask/img/sidebar/aliyun.png', 'http://www.fastadmin.net', '', 1553821881, 1553821881, 0, 'normal');
COMMIT;

-- ----------------------------
-- Records of fa_ask_category
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__ask_category` VALUES (1, 0, 'question', 'fa fa-dashboard', 'default', '问答分类', NULL, 0, 1553818461, 1553818461, 'normal');
INSERT INTO `__PREFIX__ask_category` VALUES (2, 0, 'article', 'fa fa-dashboard', 'default', '文章分类', NULL, 0, 1553818461, 1553818461, 'normal');
COMMIT;

-- ----------------------------
-- Records of fa_ask_zone
-- ----------------------------
BEGIN;
INSERT INTO `__PREFIX__ask_zone` VALUES (1, '测试专区1', 'test1', '', '这里是专区的介绍', '', '', '', '{\"level\":\"10\"}', 8, 0, 1, 1563506848, 1563506848, 'normal');
INSERT INTO `__PREFIX__ask_zone` VALUES (2, '测试专区2', 'test2', '', '这里是专区的介绍', '', '', '', '{\"level\":\"1\",\"score\":\"10\"}', 12, 0, 1, 1563506848, 1563506848, 'normal');
COMMIT;

BEGIN;
ALTER TABLE `__PREFIX__user` ADD COLUMN `title` varchar(100) NULL DEFAULT '' COMMENT '称号' AFTER `verification`;
COMMIT;

BEGIN;
INSERT INTO `__PREFIX__ask_user` (`user_id`) SELECT id AS user_id FROM `__PREFIX__user` WHERE id NOT IN (SELECT `user_id` FROM `__PREFIX__ask_user`);
COMMIT;

BEGIN;
UPDATE `__PREFIX__ask_user` SET isexpert='1',experttitle='认证专家' WHERE `user_id` = 1 AND experttitle='';
COMMIT;

--
-- 1.0.8
--

ALTER TABLE `__PREFIX__ask_article` MODIFY COLUMN `zone_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '专区ID' AFTER `category_id`;
ALTER TABLE `__PREFIX__ask_article` ADD COLUMN `style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '样式' AFTER `title`;
ALTER TABLE `__PREFIX__ask_article` ADD COLUMN `images` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '组图' AFTER `image`;

ALTER TABLE `__PREFIX__ask_article` MODIFY COLUMN `flag` set('index','hot','recommend','top') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标志' AFTER `price`;
ALTER TABLE `__PREFIX__ask_block` ADD COLUMN `weigh` int(10) NULL DEFAULT 0 COMMENT '权重' AFTER `content`;
ALTER TABLE `__PREFIX__ask_question` MODIFY COLUMN `zone_id` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '专区ID' AFTER `category_id`;

ALTER TABLE `__PREFIX__ask_question` ADD COLUMN `images` varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '组图' AFTER `zone_id`;
ALTER TABLE `__PREFIX__ask_question` ADD COLUMN `style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '样式' AFTER `title`;
ALTER TABLE `__PREFIX__ask_question` MODIFY COLUMN `flag` set('index','hot','recommend','top') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标志' AFTER `price`;
ALTER TABLE `__PREFIX__ask_question` ADD COLUMN `isprivate` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否私有答案' AFTER `isanonymous`;

--
-- 1.0.12
--
ALTER TABLE `__PREFIX__ask_category` MODIFY COLUMN `type` enum('question','article','tag','expert') DEFAULT 'question' COMMENT '类型' AFTER `pid`;
ALTER TABLE `__PREFIX__ask_certification` ADD COLUMN `category_id` int(10) DEFAULT '0' COMMENT '分类ID' AFTER `user_id`;
ALTER TABLE `__PREFIX__ask_user` ADD COLUMN `category_id` int(10) DEFAULT '0' COMMENT '分类ID' AFTER `user_id`;

--
-- 1.0.13
--
ALTER TABLE `__PREFIX__ask_order` ADD COLUMN `method` varchar(100) NULL COMMENT '支付方法' AFTER `paytype`;
SET FOREIGN_KEY_CHECKS = 1;
