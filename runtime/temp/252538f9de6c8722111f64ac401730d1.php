<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:48:"F:\demo\demogov\addons\ask\view\index\index.html";i:1639985476;s:51:"F:\demo\demogov\addons\ask\view\layout\default.html";i:1658892402;s:56:"F:\demo\demogov\addons\ask\view\common\questionitem.html";i:1597822252;s:52:"F:\demo\demogov\addons\ask\view\common\pageinfo.html";i:1639985476;s:51:"F:\demo\demogov\addons\ask\view\common\sidebar.html";i:1581583269;}*/ ?>
<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="renderer" content="webkit">
    <title><?php echo isset($title)?htmlentities($title):'首页'; if(\think\Request::instance()->get('page')): ?> - 第<?php echo intval(\think\Request::instance()->get('page')); ?>页<?php endif; ?> - <?php echo \think\Config::get("ask.sitename"); ?></title>
    <?php if(isset($keywords) && $keywords): ?>
    <meta name="keywords" content="<?php echo isset($keywords)?htmlentities($keywords):''; ?>"/>
    <?php endif; if(isset($description) && $description): ?>
    <meta name="description" content="<?php echo isset($description)?htmlentities($description):''; ?>"/>
    <?php endif; ?>

    <link rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon" />
    <?php if($config['debug']): ?>
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/libs/font-awesome/css/font-awesome.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/libs/fastadmin-layer/dist/theme/default/layer.css?v=<?php echo $site['version']; ?>"/>

    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/swiper.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/jquery.tagsinput.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/jquery.autocomplete.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/wysiwyg.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/bootstrap-markdown.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/summernote.css?v=<?php echo $site['version']; ?>"/>

    <link rel="stylesheet" media="screen" href="/assets/addons/ask/css/common.css?v=<?php echo $site['version']; ?>"/>
    <?php else: ?>
    <link rel="stylesheet" type="text/css" id="layuicss-layer" href="/assets/libs/fastadmin-layer/dist/theme/default/layer.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" type="text/css" href="/assets/addons/ask/css/all.min.css?v=<?php echo $site['version']; ?>"/>
    <?php endif; ?>

    {__STYLE__}

    <!--[if lt IE 9]>
    <script src="/libs/html5shiv.js?v="></script>
    <script src="/libs/respond.min.js?v="></script>
    <![endif]-->

    <!--@formatter:off-->
    <script type="text/javascript">
        var Config = <?php echo json_encode($askConfig); ?>;
    </script>
    <!--@formatter:on-->

</head>
<body class="group-page">

<header class="header">
    <!-- S 导航 -->
    <nav class="navbar navbar-default navbar-white navbar-fixed-top" role="navigation">
        <div class="container">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle <?php echo $user && $user['ask']['notices']>0?'unread':''; ?>" data-toggle="collapse" data-target="#navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo \think\Config::get("ask.indexurl"); ?>"><img src="<?php echo cdnurl((\think\Config::get('ask.sitelogo') ?: '/assets/addons/ask/img/logo.png')); ?>" style="height:100%;"></a>
            </div>

            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="<?php echo $askConfig['controllername']=='index'?'active':''; ?>"><a href="<?php echo addon_url('ask/index/index'); ?>">首页</a></li>
                    <li class="<?php echo $askConfig['controllername']=='question'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>">问答</a></li>
                    <li class="<?php echo $askConfig['controllername']=='article'?'active':''; ?>"><a href="<?php echo addon_url('ask/article/index'); ?>">文章</a></li>
                    <li class="<?php echo $askConfig['controllername']=='tag'?'active':''; ?>"><a href="<?php echo addon_url('ask/tag/index'); ?>">话题</a></li>
                    <li class="<?php echo $askConfig['controllername']=='expert'?'active':''; ?>"><a href="<?php echo addon_url('ask/expert/index'); ?>">专家</a></li>
<!--                    <li class="dropdown">-->
<!--                        <a href="<?php echo addon_url('ask/zone/index','',true); ?>" class="dropdown-toggle" data-toggle="dropdown">专区 <b class="caret"></b></a>-->
<!--                        <ul class="dropdown-menu multi-level">-->
<!--                            <?php if(is_array($topZoneList) || $topZoneList instanceof \think\Collection || $topZoneList instanceof \think\Paginator): if( count($topZoneList)==0 ) : echo "" ;else: foreach($topZoneList as $key=>$item): ?>-->
<!--                            <li><a href="<?php echo $item['url']; ?>"><?php echo $item['name']; ?></a></li>-->
<!--                            <?php endforeach; endif; else: echo "" ;endif; ?>-->
<!--                            <li><a href="<?php echo addon_url('ask/zone/index','',true); ?>">更多专区...</a></li>-->
<!--                        </ul>-->
<!--                    </li>-->
<!--                    <li><a href="<?php echo url('/','',false); ?>">返回主站</a></li>-->
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <form class="form-inline navbar-form search-form pt-1" action="<?php echo addon_url('ask/search/index'); ?>" method="get">
                            <input class="form-control" name="q" value="<?php echo htmlentities((isset($keyword) && ($keyword !== '')?$keyword:'')); ?>" type="text" id="searchinput" placeholder="搜索问题、话题或文章">
                        </form>
                    </li>
                    <li class="hidden-xs">
                        <div class="py-3 px-2">
                            <a href="<?php echo addon_url('ask/question/post'); ?>" class="btn btn-primary">提问</a>
                        </div>
                    </li>
                    <?php if($user): ?>
                    <li class="dropdown belling">
                        <a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/notification?act=marktopall" class="dropdown-toggle <?php echo $user['ask']['notices']>0?'unread':''; ?>" data-toggle="dropdown"><i class="fa fa-bell-o fa-lg"></i></a>
                        <?php if($user['ask']['notices']>0): ?>
                        <ul class="dropdown-menu">
                            <?php if($user['ask']['unadopted']>0): ?>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/question?type=unsolved">你有 <b><?php echo $user['ask']['unadopted']; ?></b> 个提问待采纳</a></li>
                            <?php endif; if($user['ask']['invites']>0): ?>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/invite?act=marktop">你有 <b><?php echo $user['ask']['invites']; ?></b> 条邀请消息</a> <em>&times;</em></li>
                            <?php endif; if($user['ask']['messages']>0): ?>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/message?act=marktop">你有 <b><?php echo $user['ask']['messages']; ?></b> 条私信消息</a> <em>&times;</em></li>
                            <?php endif; if($user['ask']['notifications']>0): ?>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/notification?act=marktop">你有 <b><?php echo $user['ask']['notifications']; ?></b> 条通知消息</a> <em>&times;</em></li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <?php endif; ?>
                    <li class="dropdown navbar-userinfo">
                        <?php if($user): ?>
                        <a href="<?php echo url('index/user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="avatar-img pull-left"><img src="<?php echo cdnurl($user['avatar']); ?>" class="img-circle" style="width:30px;height:30px;border-radius:50%;" alt=""></span>
                            <span class="visible-xs pull-left ml-2 pt-1"><?php echo $user['nickname']; ?> <b class="caret"></b></span>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo url('index/user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown">会员<span class="hidden-sm">中心</span> <b class="caret"></b></a>
                        <?php endif; ?>
                        <ul class="dropdown-menu">
                            <?php if($user): ?>
                            <li><a href="<?php echo url('index/user/index'); ?>"><i class="fa fa-user fa-fw"></i> 会员中心</a></li>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>"><i class="fa fa-home fa-fw"></i> 我的主页</a></li>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/question"><i class="fa fa-question-circle fa-fw"></i> 我的提问</a></li>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/article"><i class="fa fa-file-text fa-fw"></i> 我的文章</a></li>
                            <li><a href="<?php echo addon_url('ask/user/index',[':id'=>$user['id']], false); ?>/collection"><i class="fa fa-bookmark fa-fw"></i> 我的收藏</a></li>
                            <li><a href="<?php echo url('index/user/logout'); ?>"><i class="fa fa-sign-out fa-fw"></i> 退出</a></li>
                            <?php else: ?>
                            <li><a href="<?php echo url('index/user/login'); ?>"><i class="fa fa-sign-in fa-fw"></i> 登录</a></li>
                            <li><a href="<?php echo url('index/user/register'); ?>"><i class="fa fa-user-o fa-fw"></i> 注册</a></li>
                            <?php endif; ?>

                        </ul>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
    <!-- E 导航 -->

</header>

<style data-render="style">
    @media (max-width: 480px) {
        h3.search-title {
            font-size: 28px;
        }

        form.index-search-form .search-term {
            width: 60%;
        }

        .swiper-recommend .swiper-slide {
            margin-right: 0px;
        }
    }

    .signin-rank-table > tbody > tr > td {
        vertical-align: middle;
    }
</style>
<div class="full-section search-section-listing">
    <div class="search-area container">
        <h3 class="search-title">你提问，我们来为你解答?</h3>
        <p class="search-tag-line">如果你有任何问题你可以问下面的小伙伴或输入你需要解决的问题!</p>
        <form autocomplete="off" action="<?php echo addon_url('ask/search/index'); ?>" method="get" class="index-search-form clearfix" id="search-form">
            <input type="text" placeholder="请输入你遇到的问题进行搜索" name="keyword" class="search-term" id="searchquestion" autocomplete="off">
            <button type="submit" class="search-btn">搜索问题</button>
        </form>
    </div>
</div>

<div class="container" id="mainbody">
    <!-- S 专家推荐 -->
    <h2 style="font-size:18px;">专家推荐</h2>
    <div class="swiper-container swiper-recommend" style="margin:20px 0;">
        <div class="swiper-wrapper">
            <?php $__DIKUq6lC9t__ = \addons\ask\model\User::getUserList(["id"=>"item","cache"=>"3600","limit"=>"10","condition"=>"1=isexpert","orderby"=>"rand","orderway"=>"desc"]); if(is_array($__DIKUq6lC9t__) || $__DIKUq6lC9t__ instanceof \think\Collection || $__DIKUq6lC9t__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__DIKUq6lC9t__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
            <div class="swiper-slide">
                <div class="thumbnail">
                    <a href="<?php echo $item['url']; ?>" data-toggle="popover" data-title="<?php echo $item['nickname']; ?>" data-placement="auto right" data-type="user" data-id="<?php echo $item['id']; ?>">
                        <img class="img-circle" style="width:80px;height:80px;margin:30px auto 10px auto;" src="<?php echo cdnurl($item['avatar']); ?>" alt="<?php echo $item['nickname']; ?>">
                    </a>
                    <div class="caption">
                        <h4 class="text-center"><a href="<?php echo $item['url']; ?>" title="<?php echo $item['nickname']; ?>"><?php echo $item['nickname']; ?></a></h4>
                        <p class="text-muted text-center" title="<?php echo $item['experttitle']; ?>"><?php echo (isset($item['title']) && ($item['title'] !== '')?$item['title']:"认证专家"); ?></p>
                        <p class="text-center"><a class="btn btn-primary btn-sm" href="<?php echo addon_url('ask/question/post'); ?>?user_id=<?php echo $item['id']; ?>">向TA提问</a></p>
                    </div>
                </div>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__DIKUq6lC9t__; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
    <!-- E 专家推荐 -->

    <div class="row" id="questionlist">
        <div class="col-md-8 col-sm-12">
            <!-- S 首页问题列表 -->
            <ul class="nav nav-tabs nav-noborder mb-10 mt-20">
                <li class="<?php echo $questionType=='new'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=new">最新</a></li>
                <li class="<?php echo $questionType=='hot'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=hot">热门</a></li>
                <li class="<?php echo $questionType=='price'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=price">悬赏</a></li>
                <li class="<?php echo $questionType=='unsolved'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=unsolved">未解决</a></li>
                <li class="<?php echo $questionType=='unanswer'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=unanswer">未回答</a></li>
                <li class="<?php echo $questionType=='solved'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=solved">已解决</a></li>
                <?php if($isAdmin): ?>
                <li class="<?php echo $questionType=='unsettled'?'active':''; ?>"><a href="<?php echo addon_url('ask/question/index'); ?>?type=unsettled" title="已经过期的悬赏">已过期</a></li>
                <?php endif; ?>
            </ul>

            <div class="tab-inner" style="background:#fff;padding:15px;">
                <div class="question-list" data-page="<?php echo intval((\think\Request::instance()->get('page') ?: 1)); ?>" data-more="<?php echo $questionList->hasPages(); ?>">

                    <?php if(is_array($questionList) || $questionList instanceof \think\Collection || $questionList instanceof \think\Paginator): if( count($questionList)==0 ) : echo "" ;else: foreach($questionList as $key=>$question): ?>
                    <section class="question-list-item">
    <div class="qa-rank">
        <div class="answers <?php echo $question['answers']?'answered':''; ?> <?php echo $question['best_answer_id']?'solved':''; ?>">
            <?php echo $question['answers']; ?>
            <small> <?php echo $question['best_answer_id']?'解决':'回答'; ?></small>
        </div>
        <div class="views hidden-xs">
            <?php echo $question['views_format']; ?>
            <small>浏览</small>
        </div>
    </div>
    <div class="summary">
        <ul class="author list-inline">
            <li>
                <?php if($question['isanonymous']): ?>
                <a href="javascript:">匿名</a>
                <?php else: ?>
                <a href="<?php echo $question['user']['url']; ?>" data-toggle="popover" data-title="<?php echo $question['user']['nickname']; ?>" data-type="user" data-id="<?php echo $question['user_id']; ?>"><?php echo $question['user']['nickname']; ?></a>
                <?php endif; ?>
                <span class="split"></span>
                <span class="askDate"><?php echo $question['create_date']; ?></span>
            </li>
        </ul>
        <h2 class="title">
            <a href="<?php echo $question['url']; ?>">
                <?php if($question['flag'] == 'top'): ?>
                <span class="label label-danger" style="float: left;margin-right: 10px;">置顶</span>
                <?php endif; if($question['price']>0): ?>
                <span class="price-tag" data-toggle="tooltip" data-title="如果回答被采纳，回答者将获得<?php echo $question['price']; ?>元"><i class="fa fa-rmb"></i> <?php echo $question['price']; ?></span>
                <?php endif; if($question['score']>0): ?>
                <span class="price-tag" data-toggle="tooltip" data-title="如果回答被采纳，回答者将获得<?php echo $question['score']; ?>积分"><i class="fa fa-database"></i> <?php echo $question['score']; ?></span>
                <?php endif; ?>
                <span style="<?php echo $question['style_text']; ?>"><?php echo htmlentities($question['title']); ?></span>
            </a>
        </h2>
        <div class="tags">
            <?php if(is_array($question['tags']) || $question['tags'] instanceof \think\Collection || $question['tags'] instanceof \think\Paginator): if( count($question['tags'])==0 ) : echo "" ;else: foreach($question['tags'] as $key=>$tag): ?>
            <a href="<?php echo $tag['url']; ?>" class="tag"><?php if($tag['icon']): ?><img src="<?php echo $tag['icon']; ?>" alt=""><?php endif; ?><?php echo htmlentities($tag['name']); ?></a>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <?php if($question['images']): ?>
        <div class="images row">
            <?php if(is_array($question['images_list']) || $question['images_list'] instanceof \think\Collection || $question['images_list'] instanceof \think\Paginator): if( count($question['images_list'])==0 ) : echo "" ;else: foreach($question['images_list'] as $key=>$image): ?>
            <div class="col-xs-6 col-sm-3">
                <a href="<?php echo $question['url']; ?>" class="img-zoom">
                    <div class="embed-responsive embed-responsive-4by3">
                        <img src="<?php echo $image; ?>" class="embed-responsive-item">
                    </div>
                </a>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

                    <?php endforeach; endif; else: echo "" ;endif; ?>

                    <!-- S 分页 -->
                    <!--@formatter:off-->
<?php if((config('ask.loadmode')=='paging' && "[loadmode]"!="infinite") || "[loadmode]"=="paging"): ?>
    
    <!-- S 分页栏 -->
    <div class="text-center pager">
        <?php echo $__pagelist__->render(['type' => in_array('[type]',['simple', 'full'])?'[type]':null]); ?>
    </div>
    <!-- E 分页栏 -->
    <?php if($__pagelist__->isEmpty()): ?>
        <div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无数据</span></div>
    <?php endif; else: if($__pagelist__->isEmpty() || !$__pagelist__->hasPages()): if($__pagelist__->currentPage()>1 || ($__pagelist__->isEmpty() && $__pagelist__->currentPage()==1)): ?>
            <div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无更多数据</span></div>
        <?php endif; else: ?>
        <div class="text-center clearfix">
            <a href="?<?php echo http_build_query(array_merge(request()->get(), ['page'=>$__pagelist__->currentPage()+1])); ?>"
               data-url="?<?php echo http_build_query(array_merge(request()->get(), ['page'=>'__page__'])); ?>"
               data-page="<?php echo $__pagelist__->currentPage()+1; ?>" class="btn btn-default my-4 px-4 btn-loadmore"
               data-autoload="<?php echo in_array('true',['true', 'false'])?'true':'false'; ?>">
                加载更多
            </a>
        </div>
    <?php endif; endif; ?>
<!--@formatter:on-->

                    <!-- E 分页 -->
                </div>
            </div>
            <!-- E 首页问题列表 -->
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="article-sidebar">
                <!-- S 每日签到 -->
                <div class="panel panel-default recommend-article">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            每日签到
                            <div class="pull-right">
                                <a class="btn btn-link btn-xs btn-rank" style="font-size:12px;" href="javascript:">排行榜</a>
                            </div>
                        </h3>
                    </div>
                    <div class="panel-body text-center">
                        <?php if(!isset($signinconfig)): ?>
                        <a href="<?php echo url('index/user/login'); ?>" class="btn btn-danger disabled" disabled=""><i class="fa fa-info-circle"></i> 请在后台安装会员签到插件并启用</a>
                        <?php elseif(!$user): ?>
                        <a href="<?php echo url('index/user/login'); ?>" class="btn btn-primary"><i class="fa fa-user"></i> 请登录后签到</a>
                        <a href="<?php echo url('index/user/register'); ?>" class="btn btn-outline-primary">注册</a>
                        <?php elseif(isset($signin) && $signin): ?>
                        <a href="javascript:" class="btn btn-primary disabled"><i class="fa fa-location-arrow"></i> 今日已签到, 连续签到 <?php echo $signin['successions']; ?> 天</a>
                        <?php else: ?>
                        <a href="javascript:" class="btn btn-primary btn-signin"><i class="fa fa-location-arrow"></i> 立即签到</a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- E 每日签到 -->
                <!-- S 热门文章 -->
<div class="panel panel-default recommend-article">
    <div class="panel-heading">
        <h3 class="panel-title">热门文章</h3>
    </div>
    <div class="panel-body">
        <?php $__ecgVna2X4j__ = \addons\ask\model\Article::getArticleList(["id"=>"item","orderby"=>"views"]); if(is_array($__ecgVna2X4j__) || $__ecgVna2X4j__ instanceof \think\Collection || $__ecgVna2X4j__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__ecgVna2X4j__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <span class="num"><?php echo $i; ?></span>
            </div>
            <div class="media-body">
                <a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__ecgVna2X4j__; ?>
    </div>
</div>
<!-- E 热门文章 -->

<div class="panel panel-blockimg">
    <a href="http://www.fastadmin.net"><img src="http://demo666.com/assets/addons/ask/img/sidebar/howto.png" class="img-responsive"/></a>
</div>

<!-- S 热门问题 -->
<div class="panel panel-default hot-article">
    <div class="panel-heading">
        <h3 class="panel-title">热门问题</h3>
    </div>
    <div class="panel-body">
        <?php $__rJEqAWxfZN__ = \addons\ask\model\Question::getQuestionList(["id"=>"item","orderby"=>"views"]); if(is_array($__rJEqAWxfZN__) || $__rJEqAWxfZN__ instanceof \think\Collection || $__rJEqAWxfZN__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__rJEqAWxfZN__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <span class="num"><?php echo $i; ?></span>
            </div>
            <div class="media-body">
                <a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__rJEqAWxfZN__; ?>
    </div>
</div>
<!-- E 热门问题 -->

<div class="panel panel-blockimg">
    <a href="http://www.fastadmin.net"><img src="http://demo666.com/assets/addons/ask/img/sidebar/aliyun.png" class="img-responsive"/></a>
</div>

<!-- S 热门标签 -->
<div class="panel panel-default hot-tags">
    <div class="panel-heading">
        <h3 class="panel-title">热门标签</h3>
    </div>
    <div class="panel-body">
        <?php $__l4pY8oUegf__ = \addons\ask\model\Tag::getTagList(["id"=>"item","limit"=>"50","orderby"=>"questions"]); if(is_array($__l4pY8oUegf__) || $__l4pY8oUegf__ instanceof \think\Collection || $__l4pY8oUegf__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__l4pY8oUegf__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <a href="<?php echo $item['url']; ?>" class="tag"> <?php if($item['icon']): ?><img src="<?php echo $item['icon']; ?>" alt=""><?php endif; ?> <?php echo $item['name']; ?></a>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__l4pY8oUegf__; ?>
    </div>
</div>
<!-- E 热门标签 -->

<!-- S 等待解答 -->
<div class="panel panel-default recommend-article">
    <div class="panel-heading">
        <h3 class="panel-title">等待解答</h3>
    </div>
    <div class="panel-body">
        <?php $__A0fVQBSNiv__ = \addons\ask\model\Question::getQuestionList(["id"=>"item","condition"=>"0=answers"]); if(is_array($__A0fVQBSNiv__) || $__A0fVQBSNiv__ instanceof \think\Collection || $__A0fVQBSNiv__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__A0fVQBSNiv__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <span class="tag">问</span>
            </div>
            <div class="media-body">
                <a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__A0fVQBSNiv__; ?>
    </div>
</div>
<!-- E 等待解答 -->

<!-- S 推荐专家 -->
<div class="panel panel-default panel-users">
    <div class="panel-heading">
        <h3 class="panel-title">推荐专家</h3>
    </div>
    <div class="panel-body">
        <?php $__df6lUun2tM__ = \addons\ask\model\User::getUserList(["id"=>"item","condition"=>"1=isexpert","limit"=>"5","cache"=>"86400"]); if(is_array($__df6lUun2tM__) || $__df6lUun2tM__ instanceof \think\Collection || $__df6lUun2tM__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__df6lUun2tM__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo $item['nickname']; ?>"><img class="media-object img-circle img-small" alt="<?php echo $item['nickname']; ?>" src="<?php echo cdnurl($item['avatar']); ?>"></a>
            </div>
            <div class="media-body">
                <div class="media-heading"><a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo $item['nickname']; ?>"><?php echo $item['nickname']; ?></a></div>
                <div class="text-muted"><?php echo $item['answers']; ?>个答案 <?php echo $item['adoptions']; ?>次被采纳</div>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__df6lUun2tM__; ?>
    </div>
</div>
<!-- E 推荐专家 -->
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="ranktpl">
    <div style="padding:20px;min-height:300px;">
        <table class="table table-striped table-hover signin-rank-table">
            <thead>
            <tr>
                <th>头像</th>
                <th width="50%">昵称</th>
                <th class="text-center">连续签到</th>
            </tr>
            </thead>
            <tbody>
            <%for(var i=0;i< ranklist.length;i++){%>
            <tr>
                <td><a href="<%=ranklist[i]['user']['url']%>"><img src="<%=ranklist[i]['user']['avatar']%>" height="30" width="30" alt="" class="img-circle"/></a></td>
                <td><a href="<%=ranklist[i]['user']['url']%>"><%=ranklist[i]['user']['nickname']%></a></td>
                <td class="text-center"><%=ranklist[i]['days']%> 天</td>
            </tr>
            <%}%>
            </tbody>
        </table>
    </div>
</script>

<script data-render="script" src="/assets/addons/ask/js/jquery.swiper.min.js"></script>

<script data-render="script">
    $(function () {
        ASK.render.question("#searchquestion");
        var mySwiper = new Swiper('.swiper-container', {
            nextButton: '.swiper-button-next',
            prevButton: '.swiper-button-prev',
            slidesPerView: 5,
            paginationClickable: true,
            spaceBetween: 30,
            breakpoints: {
                1200: {
                    slidesPerView: 4,
                    spaceBetween: 40
                },
                970: {
                    slidesPerView: 3,
                    spaceBetween: 50
                },
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                }
            }
        });
        $(document).on("click", ".btn-signin,.today", function () {
            ASK.api.ajax("<?php echo url('index/signin/dosign'); ?>", function () {
                ASK.api.msg("签到成功!", function () {
                    location.reload();
                });
            });
            return false;
        });
        $(document).on("click", ".btn-rank", function () {
            if (!parseInt("<?php echo isset($signinconfig)?'1':'0'; ?>")) {
                layer.msg("请在后台安装会员签到插件并启用后重试", {icon: 2});
                return false;
            }
            ASK.api.ajax("<?php echo url('index/signin/rank'); ?>", function (data) {
                layer.open({
                    title: '签到排行榜',
                    type: 1,
                    zIndex: 88,
                    area: isMobile ? 'auto' : ["400px"],
                    content: template("ranktpl", data),
                    btn: []
                });
                return false;
            });
            return false;
        });
    })
</script>


<footer class="footer">
    <div class="container-fluid" id="footer">
        <p class="address">
            Copyright&nbsp;©&nbsp;2017-2020 All Rights Reserved <?php echo $site['name']; ?> <?php echo __('Copyrights'); ?> <a href="https://beian.miit.gov.cn" target="_blank"><?php echo $site['beian']; ?></a>
        </p>
    </div>
</footer>

<div class="floatbar" style="margin-top: -220px;">

    <div class="floatbar-item">
        <a href="<?php echo addon_url('ask/question/post'); ?>" class="floatbar-btn">
            <i class="fa fa-pencil"></i>
            <p>
                发布<br>
                问题
            </p>
        </a>
    </div>
    <?php if(isset($__question__)): ?>
    <div class="floatbar-item item-faq">
        <a href="javascript:" class="floatbar-btn btn-share">
            <i class="fa fa-share-alt"></i>
            <p>
                分享<br>
                好友
            </p>
        </a>
    </div>
    <div class="floatbar-item item-faq">
        <a href="javascript:" class="floatbar-btn">
            <i class="fa fa-qrcode"></i>
            <p>
                手机<br>
                浏览
            </p>
        </a>
        <div class="floatbar-box box-qrcode" style="width:220px;">
            <div class="floatbar-container">
                <div class="clearfix">
                    <div class="row">
                        <div class="col-xs-12">
                            <img src="<?php echo addon_url('ask/qrcode/build'); ?>?text=<?php echo urlencode($__question__['fullurl']); ?>" width="150">
                            <div class="text-center">扫码手机浏览</div>
                        </div>
                    </div>
                </div>
                <span class="arrow" style="top:70px;"></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="floatbar-item floatbtn-item-top" style="display:none;">
        <a href="javascript:" class="floatbar-btn backtotop">
            <i class="fa fa-chevron-up"></i>
            <p>
                回到<br>
                顶部
            </p>
        </a>
    </div>
</div>

<!--搜索模板-->
<script type="text/html" id="bodytpl">

    <%if(typeof item.title == 'undefined'){%>
    <%if(item.length>0){%>
    <div class="autocomplete-searchtags">
        <table style="width:100%;">
            <tr>
                <td colspan="2">
                    <div class="search-subject">
                        <%for(var i=0;i< item.length;i++){%>
                        <a href="<%=item[i].url%>" class="tag"><%if(item[i].icon){%><img src="<%=item[i].icon%>" alt=""><%}%><%=#replace(item[i].name)%></a>
                        <%}%>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <%}%>
    <%}else{%>
    <div class="autocomplete-suggestion autocomplete-questions" data-href="<%=item.url%>" data-url="<%=item.url%>">
        <table style="width:100%;">
            <tr>
                <td>
                    <div class="search-subject">
                        <%if(item.price>0){%>
                            <%if(item.type=='question'){%>
                            <span class="question-price-tag" title="如果回答被采纳，回答者将获得<%=item.price%>元">￥<%=item.price%></span>
                            <%}else{%>
                            <span class="question-price-tag" title="此文章需要支付<%=item.price%>元才能查看">￥<%=item.price%></span>
                            <%}%>
                        <%}%>
                        <%if(item.score>0){%>
                            <%if(item.type=='question'){%>
                            <span class="question-price-tag" title="如果回答被采纳，回答者将获得<%=item.price%>积分">￥<%=item.score%></span>
                            <%}else{%>
                            <span class="question-price-tag" title="此文章需要消耗<%=item.price%>积分才能查看">￥<%=item.score%></span>
                            <%}%>
                        <%}%>
                        <a href="<%=item.url%>"><%=#replace(item.title)%></a>
                    </div>
                    <div class="search-meta text-muted small">
                        <%=item.userinfo.nickname%> 发布于 <%=item.create_date%>
                    </div>
                </td>
                <td class="text-muted text-right" style="width:70px;vertical-align: top;">
                    <div class="tag tag-xs <%=item.type=='question'?'':'tag-danger'%>"><%=item.type=='question'?'问题':'文章'%></div>
                </td>
            </tr>
        </table>
    </div>
    <%}%>
</script>

<!--感谢模板-->
<script id="thankstpl" type="text/html">
    <div class="p-4">
        <div class="alert alert-warning" role="alert">
            我们应该多多支持小伙伴的分享及热心帮助！
        </div>

        <div class="">
            <div class="text-center" style="margin-bottom:20px;">
                <img src="<%=userAvatar%>" class="img-circle" width="100" height="100" alt="">
                <h5><a href="<%=userUrl%>" target="_blank"><%=userNickname%></a></h5>
            </div>
            <form action="<?php echo addon_url('ask/thanks/create'); ?>" id="thanks-form" method="post" <?php if(!$isWechat): ?>target="_blank"<?php endif; ?>>
                <input type="hidden" name="id" value="<%=id%>"/>
                <input type="hidden" name="type" value="<%=type%>"/>
                <input type="hidden" name="money" value="1"/>
                <div class="row">
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money active" data-money="1">￥1 元</button>
                    </div>
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money" data-money="5">￥5 元</button>
                    </div>
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money" data-money="10">￥10 元</button>
                    </div>
                </div>
                <div class="row my-4">
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money" data-money="20">￥20 元</button>
                    </div>
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money" data-money="50">￥50 元</button>
                    </div>
                    <div class="col-xs-4">
                        <button type="button" class="btn btn-light btn-block btn-money" data-money="100">￥100 元</button>
                    </div>
                </div>
                <div class="row my-1 text-center">
                    <div class="col-xs-12">
                        <input type="text" class="form-control" value="" placeholder="请输入你的留言，可选"/>
                    </div>
                </div>
                <hr>
                <div class="row my-1 text-center">
                    <div class="col-xs-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paytype" id="paytype-wechat"
                                   value="wechat" checked>
                            <label class="form-check-label" for="paytype-wechat">微信</label>
                        </div>
                    </div>
                    <?php if(!$isWechat): ?>
                    <div class="col-xs-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paytype" id="paytype-alipay"
                                   value="alipay">
                            <label class="form-check-label" for="paytype-alipay">支付宝</label>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-xs-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paytype" id="paytype-balance"
                                   value="balance">
                            <label class="form-check-label" for="paytype-balance" data-toggle="tooltip" data-title="余额：￥<?php echo (isset($user['money']) && ($user['money'] !== '')?$user['money']:0); ?>">余额支付</label>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col text-center">
                        <button type="submit" class="btn btn-primary btn-submit-thanks">立即支付</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</script>

<!--举报模板-->
<script id="reporttpl" type="text/html">
    <div class="p-4">
        <div class="alert alert-info" role="alert">
            让我们一起共建文明社区！您的反馈至关重要！
        </div>

        <div class="">
            <form action="<?php echo addon_url('ask/report/create'); ?>" id="report-form" method="post">
                <input type="hidden" name="id" value="<%=id%>"/>
                <input type="hidden" name="type" value="<%=type%>"/>
                <div class="row radio">
                    <?php if(is_array($config['reportreasonlist']) || $config['reportreasonlist'] instanceof \think\Collection || $config['reportreasonlist'] instanceof \think\Paginator): $i = 0; $__LIST__ = $config['reportreasonlist'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                    <div class="col-xs-6 py-1">
                        <label><input type="radio" name="reason" value="<?php echo $key; ?>" <?php if($i==1): ?>checked<?php endif; ?>> <?php echo $item; ?></label>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>

                <div class="row my-1 text-center">
                    <div class="col-xs-12">
                        <input type="text" class="form-control" value="" placeholder="其它信息，可选"/>
                    </div>
                </div>
                <hr>
                <div class="row mt-2">
                    <div class="col text-center">
                        <button type="submit" class="btn btn-primary btn-submit-thanks">提交反馈</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</script>

<!--支付模板-->
<script type="text/html" id="paynowtpl">
    <div class="" style="padding:20px 20px 20px 20px;min-width:330px;min-height:220px;">
        <div>
            <div class="alert alert-info">
                <div class="row">
                    <div class="col-xs-12">
                        <div style="font-size:14px;"><b>支付金额：￥<%=price%></b></div>
                    </div>
                </div>
            </div>
        </div>
        <form action="<?php echo addon_url('ask/order/submit'); ?>" id="paynow-form" method="post" <?php if(!$isWechat): ?>target="_blank"<?php endif; ?>>
            <input type="hidden" name="id" value="<%=id%>"/>
            <input type="hidden" name="type" value="<%=type%>"/>
            <hr>
            <div class="row my-4 text-center">
                <div class="col-xs-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paytype" id="paytype-wechat"
                               value="wechat" checked>
                        <label class="form-check-label" for="paytype-wechat">
                            <img src="/assets/addons/ask/img/wechat.png" height="30" alt="">
                        </label>
                    </div>
                </div>
                <?php if(!$isWechat): ?>
                <div class="col-xs-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paytype" id="paytype-alipay"
                               value="alipay">
                        <label class="form-check-label" for="paytype-alipay">
                            <img src="/assets/addons/ask/img/alipay.png" height="30" alt="">
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-xs-4">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="paytype" id="paytype-balance" <?php if(!$user): ?>disabled<?php endif; ?>
                        value="balance">
                        <label class="form-check-label" for="paytype-balance" data-toggle="tooltip" data-title="余额：￥<?php echo (isset($user['money']) && ($user['money'] !== '')?$user['money']:0); ?>">
                            <img src="/assets/addons/ask/img/balance.png" height="30" alt="">
                        </label>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-2">
                <div class="col text-center">
                    <button type="submit" class="btn btn-primary btn-submit-pay">立即支付</button>
                </div>
            </div>
        </form>
    </div>
</script>

<!--分享模板-->
<script id="sharetpl" type="text/html">
    <div id="sharenow" style="padding:20px;">
        <div class="row text-center">
            <div class="col-xs-12">
                <img src="<?php echo addon_url('ask/qrcode/build'); ?>?text=<%=url%>&logo=1&logosize=50&padding=0" width="120" alt="">
                <div class="my-2">
                    扫码分享到微信
                </div>
            </div>
        </div>
        <div class="row text-center mt-3 pt-2">
            <div class="col-xs-4">
                <a href="http://service.weibo.com/share/share.php?url=<%=url%>&title=<%=title%>" target="_blank">
                <span class="fa-stack fa-lg text-danger">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <i class="fa fa-weibo fa-stack-1x fa-inverse"></i>
                </span><br>
                    分享到微博
                </a>
            </div>
            <div class="col-xs-4">
                <a href="javascript:" class="btn-share-wechat" onclick="layer.msg('请使用微信扫描上方二维码进行分享');">
                <span class="fa-stack fa-lg text-success">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <i class="fa fa-wechat fa-stack-1x fa-inverse"></i>
                </span><br>
                    分享到微信
                </a>
            </div>
            <div class="col-xs-4">
                <a href="https://connect.qq.com/widget/shareqq/index.html?url=<%=url%>&title=<%=title%>&desc=&summary=&site=<?php echo $site['name']; ?>" target="_blank">
                <span class="fa-stack fa-lg text-info">
                  <i class="fa fa-circle fa-stack-2x"></i>
                  <i class="fa fa-qq fa-stack-1x fa-inverse"></i>
                </span><br>
                    分享到QQ
                </a>
            </div>
        </div>
    </div>
</script>

<?php if($config['debug']): ?>
<script type="text/javascript" src="/assets/libs/jquery/dist/jquery.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/bootstrap/dist/js/bootstrap.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/fastadmin-layer/dist/layer.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/art-template/dist/template-native.js?v=<?php echo $site['version']; ?>"></script>

<script type="text/javascript" src="/assets/addons/ask/js/taboverride.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/bootstrap-markdown.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/jquery.pasteupload.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/jquery.textcomplete.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/markdown.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/turndown.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/summernote.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/summernote-zh-CN.min.js?v=<?php echo $site['version']; ?>"></script>

<script type="text/javascript" src='/assets/addons/ask/js/jquery.autocomplete.js?v=<?php echo $site['version']; ?>'></script>
<script type="text/javascript" src='/assets/addons/ask/js/jquery.tagsinput.js?v=<?php echo $site['version']; ?>'></script>

<script type="text/javascript" src="/assets/addons/ask/js/ask.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/common.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/ask/js/xss.min.js?v=<?php echo $site['version']; ?>"></script>
<?php else: ?>
<script type="text/javascript" src="/assets/addons/ask/js/all.min.js?v=<?php echo $site['version']; ?>" merge="true"></script>
<script type="text/javascript" src="/assets/addons/ask/js/summernote.min.js?v=<?php echo $site['version']; ?>" merge="true"></script>
<script type="text/javascript" src="/assets/addons/ask/js/summernote-zh-CN.min.js?v=<?php echo $site['version']; ?>" merge="true"></script>
<script type="text/javascript" src="/assets/addons/ask/js/xss.min.js?v=<?php echo $site['version']; ?>" merge="true"></script>
<?php endif; ?>

{__SCRIPT__}

<script>
    //你可以在此插入你的统计代码
</script>

</body>
</html>
