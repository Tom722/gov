<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:50:"F:\demo\demogov\addons\cms\view\default\index.html";i:1650535601;s:58:"F:\demo\demogov\addons\cms\view\default\common\layout.html";i:1650535601;s:56:"F:\demo\demogov\addons\cms\view\default\common\item.html";i:1650535601;s:60:"F:\demo\demogov\addons\cms\view\default\common\pageinfo.html";i:1631866112;s:59:"F:\demo\demogov\addons\cms\view\default\common\sidebar.html";i:1650535601;}*/ ?>
<!DOCTYPE html>
<!--[if lt IE 7]>
<html class="lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>
<html class="lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>
<html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html class=""> <!--<![endif]-->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,Chrome=1">
    <meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="renderer" content="webkit">
    <title><?php echo htmlentities(\think\Config::get('cms.title')); ?> - <?php echo \think\Config::get('cms.sitename'); ?></title>
    <meta name="keywords" content="<?php echo htmlentities(\think\Config::get('cms.keywords')); ?>"/>
    <meta name="description" content="<?php echo htmlentities(\think\Config::get('cms.description')); ?>"/>

    <link rel="shortcut icon" href="/assets/img/favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" media="screen" href="/assets/css/bootstrap.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" media="screen" href="/assets/libs/font-awesome/css/font-awesome.min.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" media="screen" href="/assets/libs/fastadmin-layer/dist/theme/default/layer.css?v=<?php echo $site['version']; ?>"/>
    <link rel="stylesheet" media="screen" href="/assets/addons/cms/css/swiper.min.css?v=<?php echo $site['version']; ?>">
    <link rel="stylesheet" media="screen" href="/assets/addons/cms/css/share.min.css?v=<?php echo $site['version']; ?>">
    <link rel="stylesheet" media="screen" href="/assets/addons/cms/css/iconfont.css?v=<?php echo $site['version']; ?>">
    <link rel="stylesheet" media="screen" href="/assets/addons/cms/css/common.css?v=<?php echo $site['version']; ?>"/>

    {__STYLE__}

    <!--@formatter:off-->
    <style>
        <?php if(isset($disturbArr) && $disturbArr): ?>
        <?php echo implode(',',$disturbArr); ?>{font-size:0;height:0;display:inline-block;line-height:0;float:left;}
        <?php endif; ?>
    </style>
    <!--@formatter:on-->

    <!--[if lt IE 9]>
    <script src="/libs/html5shiv.js"></script>
    <script src="/libs/respond.min.js"></script>
    <![endif]-->

    
</head>
<body class="group-page skin-white">

<header class="header">
    <!-- S 导航 -->
    <nav class="navbar navbar-default navbar-white navbar-fixed-top" role="navigation">
        <div class="container">

            <div class="navbar-header">
                <button type="button" class="navbar-toggle sidebar-toggle">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="<?php echo \think\Config::get('cms.indexurl'); ?>"><img src="<?php echo cdnurl((\think\Config::get('cms.sitelogo') ?: '/assets/addons/cms/img/logo.png')); ?>" style="height:100%;" alt=""></a>
            </div>

            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav" data-current="<?php echo (isset($__CHANNEL__['id']) && ($__CHANNEL__['id'] !== '')?$__CHANNEL__['id']:0); ?>">
                    <!--如果你需要自定义NAV,可使用channellist标签来完成,这里只设置了2级,如果显示无限级,请使用cms:nav标签-->
                    <?php $__Cq2wsUYlnf__ = \addons\cms\model\Channel::getChannelList(["id"=>"nav","type"=>"top","condition"=>"1=isnav"]); if(is_array($__Cq2wsUYlnf__) || $__Cq2wsUYlnf__ instanceof \think\Collection || $__Cq2wsUYlnf__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__Cq2wsUYlnf__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$nav): $mod = ($i % 2 );++$i;?>
                    <!--判断是否有子级或高亮当前栏目-->
                    <li class="<?php if($nav['has_child']): ?>dropdown<?php endif; if($nav->is_active): ?> active<?php endif; ?>">
                        <a href="<?php echo $nav['url']; ?>" <?php if($nav['has_child']): ?> data-toggle="dropdown" <?php endif; ?>><?php echo htmlentities($nav['name']); if($nav['has_nav_child']): ?> <b class="caret"></b><?php endif; ?></a>
                        <ul class="dropdown-menu <?php if(!$nav['has_nav_child']): ?>hidden<?php endif; ?>" role="menu">
                            <?php $__VjcNZ0GgKv__ = \addons\cms\model\Channel::getChannelList(["id"=>"sub","type"=>"son","typeid"=>$nav['id'],"condition"=>"1=isnav"]); if(is_array($__VjcNZ0GgKv__) || $__VjcNZ0GgKv__ instanceof \think\Collection || $__VjcNZ0GgKv__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__VjcNZ0GgKv__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                            <li><a href="<?php echo $sub['url']; ?>"><?php echo htmlentities($sub['name']); ?></a></li>
                            <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__VjcNZ0GgKv__; ?>
                        </ul>
                    </li>
                    <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__Cq2wsUYlnf__; ?>

                    <!--如果需要无限级请使用cms:nav标签-->
                    
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li>
                        <form class="form-inline navbar-form" action="<?php echo addon_url('cms/search/index'); ?>" method="get">
                            <div class="form-search hidden-sm hidden-md">
                                <input class="form-control" name="q" data-suggestion-url="<?php echo addon_url('cms/search/suggestion'); ?>" type="search" id="searchinput" value="<?php echo htmlentities((\think\Request::instance()->request('q') ?: '')); ?>" placeholder="搜索">
                                <div class="search-icon"></div>
                            </div>
                            <?php echo token('__searchtoken__'); ?>
                        </form>
                    </li>
                    <?php if(config('fastadmin.usercenter')): ?>
                    <li class="dropdown navbar-userinfo">
                        <?php if($user): ?>
                        <a href="<?php echo url('index/user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="avatar-img pull-left"><img src="<?php echo htmlentities(cdnurl($user['avatar'])); ?>" style="width:27px;height:27px;border-radius:50%;" alt=""></span>
                            <span class="visible-xs pull-left ml-2 pt-1"><?php echo htmlentities($user['nickname']); ?> <b class="caret"></b></span>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo url('index/user/index'); ?>" class="dropdown-toggle" data-toggle="dropdown">会员<span class="hidden-sm">中心</span> <b class="caret"></b></a>
                        <?php endif; ?>
                        <ul class="dropdown-menu">
                            <?php if($user): ?>
                            <li><a href="<?php echo url('index/user/index'); ?>"><i class="fa fa-user fa-fw"></i> 会员中心</a></li>
                            <li><a href="<?php echo addon_url('cms/user/index', [':id'=>$user['id']]); ?>"><i class="fa fa-user-circle fa-fw"></i> 我的个人主页</a></li>
                            <?php $sidenav = array_filter(explode(',', config('cms.usersidenav'))); if(in_array('myarchives', $sidenav)): ?>
                            <li><a href="<?php echo url('index/cms.archives/my'); ?>"><i class="fa fa-list fa-fw"></i> 我发布的文档</a></li>
                            <?php endif; if(in_array('postarchives', $sidenav)): ?>
                            <li><a href="<?php echo url('index/cms.archives/post'); ?>"><i class="fa fa-pencil fa-fw"></i> 发布文档</a></li>
                            <?php endif; if(in_array('myorder', $sidenav)): ?>
                            <li><a href="<?php echo url('index/cms.order/index'); ?>"><i class="fa fa-shopping-bag fa-fw"></i> 我的消费订单</a></li>
                            <?php endif; if(in_array('mycomment', $sidenav)): ?>
                            <li><a href="<?php echo url('index/cms.comment/index'); ?>"><i class="fa fa-comments fa-fw"></i> 我发表的评论</a></li>
                            <?php endif; if(in_array('mycollection', $sidenav)): ?>
                            <li><a href="<?php echo url('index/cms.collection/index'); ?>"><i class="fa fa-bookmark fa-fw"></i> 我的收藏</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo url('index/user/logout'); ?>"><i class="fa fa-sign-out fa-fw"></i> 注销</a></li>
                            <?php else: ?>
                            <li><a href="<?php echo url('index/user/login'); ?>"><i class="fa fa-sign-in fa-fw"></i> 登录</a></li>
                            <li><a href="<?php echo url('index/user/register'); ?>"><i class="fa fa-user-o fa-fw"></i> 注册</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </nav>
    <!-- E 导航 -->

</header>

<main class="main-content">
    

<div class="container" id="content-container">

    <!--<div style="margin-bottom:20px;">-->
    <!---->
    <!--</div>-->

    <div class="row">

        <main class="col-xs-12 col-md-8">
            <div class="swiper-container index-focus">
                <!-- S 焦点图 -->
                <div id="index-focus" class="carousel slide carousel-focus" data-ride="carousel">
                    <ol class="carousel-indicators">
                        <?php $__kEoiqQ1zX9__ = \addons\cms\model\Block::getBlockList(["id"=>"block","name"=>"indexfocus","row"=>"5"]); if(is_array($__kEoiqQ1zX9__) || $__kEoiqQ1zX9__ instanceof \think\Collection || $__kEoiqQ1zX9__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__kEoiqQ1zX9__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$block): $mod = ($i % 2 );++$i;?>
                        <li data-target="#index-focus" data-slide-to="<?php echo $i-1; ?>" class="<?php if($i==1): ?>active<?php endif; ?>"></li>
                        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__kEoiqQ1zX9__; ?>
                    </ol>
                    <div class="carousel-inner" role="listbox">
                        <?php $__5JbT67Of0y__ = \addons\cms\model\Block::getBlockList(["id"=>"block","name"=>"indexfocus","row"=>"5"]); if(is_array($__5JbT67Of0y__) || $__5JbT67Of0y__ instanceof \think\Collection || $__5JbT67Of0y__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__5JbT67Of0y__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$block): $mod = ($i % 2 );++$i;?>
                        <div class="item <?php if($i==1): ?>active<?php endif; ?>">
                            <a href="<?php echo $block['url']; ?>">
                                <div class="carousel-img" style="background-image:url('<?php echo $block['image']; ?>');"></div>
                                <div class="carousel-caption hidden-xs">
                                    <h3><?php echo htmlentities($block['title']); ?></h3>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__5JbT67Of0y__; ?>
                    </div>
                    <a class="left carousel-control" href="#index-focus" role="button" data-slide="prev">
                        <span class="icon-prev fa fa-chevron-left" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="right carousel-control" href="#index-focus" role="button" data-slide="next">
                        <span class="icon-next fa fa-chevron-right" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
                <!-- E 焦点图 -->
            </div>

            <div class="panel panel-default index-gallary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span>热门图集</span>
                        <div class="more">
                            <a href="<?php echo addon_url('cms/channel/index', [':id'=>2, ':diyname'=>'product']); ?>">查看更多</a>
                        </div>
                    </h3>

                </div>
                <div class="panel-body">
                    <div class="related-article">
                        <div class="row">
                            <!-- S 热门图集 -->
                            <?php $__ieKAbOW9r7__ = \addons\cms\model\Archives::getArchivesList(["id"=>"item","model"=>"2","orderby"=>"views","row"=>"4"]); if(is_array($__ieKAbOW9r7__) || $__ieKAbOW9r7__ instanceof \think\Collection || $__ieKAbOW9r7__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__ieKAbOW9r7__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                            <div class="col-sm-3 col-xs-6">
                                <a href="<?php echo $item['url']; ?>" class="img-zoom">
                                    <div class="embed-responsive embed-responsive-4by3">
                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlentities($item['title']); ?>" class="embed-responsive-item">
                                    </div>
                                </a>
                                <h5><?php echo htmlentities($item['title']); ?></h5>
                            </div>
                            <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__ieKAbOW9r7__; ?>
                            <!-- E 热门图集 -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <span>最近更新</span>

                        <div class="more hidden-xs">
                            <ul class="list-unstyled list-inline">
                                <!-- E 栏目筛选 -->
                                <?php $__SVWBvHprUN__ = \addons\cms\model\Channel::getChannelList(["id"=>"item","condition"=>"'list'=type","limit"=>"6"]); if(is_array($__SVWBvHprUN__) || $__SVWBvHprUN__ instanceof \think\Collection || $__SVWBvHprUN__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__SVWBvHprUN__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                                <li><?php echo $item['textlink']; ?></li>
                                <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__SVWBvHprUN__; ?>
                                <!-- E 栏目筛选 -->
                            </ul>
                        </div>
                    </h3>
                </div>
                <div class="panel-body p-0">
                    <div class="article-list">
                        <!-- S 首页列表 -->
                        <?php $__pLwuBTaQzm__ = $__PAGELIST__; if(is_array($__pLwuBTaQzm__) || $__pLwuBTaQzm__ instanceof \think\Collection || $__pLwuBTaQzm__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__pLwuBTaQzm__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                        <article class="article-item">
    <?php if($item['images'] && count($item['images_list'])>1): ?>
    <div class="gallery-article">
        <h3 class="article-title">
            <a href="<?php echo $item['url']; ?>" <?php if($item['style']): ?>style="<?php echo $item['style_text']; ?>"<?php endif; ?>><?php echo $item->highlight(htmlentities($item->title), $__SEARCHTERM__??''); ?></a>
        </h3>
        <div class="row">
            <?php if(is_array($item['images_list']) || $item['images_list'] instanceof \think\Collection || $item['images_list'] instanceof \think\Paginator): $i = 0;$__LIST__ = is_array($item['images_list']) ? array_slice($item['images_list'],0,4, true) : $item['images_list']->slice(0,4, true); if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$img): $mod = ($i % 2 );++$i;?>
            <div class="col-sm-3 col-xs-6">
                <a href="<?php echo $item['url']; ?>" class="img-zoom">
                    <div class="embed-responsive embed-responsive-4by3">
                        <img src="<?php echo $img; ?>" alt="<?php echo htmlentities($item['title']); ?>" class="embed-responsive-item">
                    </div>
                </a>
            </div>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <div class="media">
            <div class="media-body ml-0">
                <div class="article-intro">
                    <?php echo htmlentities($item['description']); ?>
                </div>
                <div class="article-tag">
                    <a href="<?php echo $item['channel']['url']; ?>" class="tag tag-primary"><?php echo htmlentities($item['channel']['name']); ?></a>
                    <span itemprop="date"><?php echo date("Y年m月d日", $item['publishtime']); ?></span>
                    <span class="hidden-xs" itemprop="likes" title="点赞次数"><i class="fa fa-thumbs-up"></i> <?php echo $item['likes']; ?> 点赞</span>
                    <span class="hidden-xs" itemprop="comments"><a href="<?php echo $item['url']; ?>#comments" target="_blank" title="评论数"><i class="fa fa-comments"></i> <?php echo $item['comments']; ?></a> 评论</span>
                    <span class="hidden-xs" itemprop="views" title="浏览次数"><i class="fa fa-eye"></i> <?php echo $item['views']; ?> 浏览</span>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="media">
        <div class="media-left">
            <a href="<?php echo $item['url']; ?>">
                <div class="embed-responsive embed-responsive-4by3 img-zoom">
                    <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlentities($item['title']); ?>">
                </div>
            </a>
        </div>
        <div class="media-body">
            <h3 class="article-title">
                <a href="<?php echo $item['url']; ?>" <?php if($item['style']): ?>style="<?php echo $item['style_text']; ?>"<?php endif; ?>><?php echo $item->highlight($item->title, $__SEARCHTERM__??''); ?></a>
            </h3>
            <div class="article-intro">
                <?php echo htmlentities($item['description']); ?>
            </div>
            <div class="article-tag">
                <a href="<?php echo $item['channel']['url']; ?>" class="tag tag-primary"><?php echo htmlentities($item['channel']['name']); ?></a>
                <span itemprop="date"><?php echo date("Y年m月d日", $item['publishtime']); ?></span>
                <span class="hidden-xs" itemprop="likes" title="点赞次数"><i class="fa fa-thumbs-up"></i> <?php echo $item['likes']; ?> 点赞</span>
                <span class="hidden-xs" itemprop="comments"><a href="<?php echo $item['url']; ?>#comments" target="_blank" title="评论数"><i class="fa fa-comments"></i> <?php echo $item['comments']; ?></a> 评论</span>
                <span class="hidden-xs" itemprop="views" title="浏览次数"><i class="fa fa-eye"></i> <?php echo $item['views']; ?> 浏览</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</article>

                        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__pLwuBTaQzm__; ?>
                        <!-- E 首页列表 -->
                    </div>

                    <!-- S 分页 -->
                    <!--@formatter:off-->
<?php if((config('cms.loadmode')=='paging' && "infinite"!="infinite") || "infinite"=="paging"): ?>
    
    <!-- S 分页栏 -->
    <div class="text-center pager">
        <?php echo $__PAGELIST__->render(['type' => in_array('[type]',['simple', 'full'])?'[type]':config('cms.pagemode')]); ?>
    </div>
    <!-- E 分页栏 -->
    <?php if($__PAGELIST__->isEmpty()): ?>
        <div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无数据</span></div>
    <?php endif; else: if($__PAGELIST__->isEmpty() || !$__PAGELIST__->hasPages()): if($__PAGELIST__->currentPage()>1 || ($__PAGELIST__->isEmpty() && $__PAGELIST__->currentPage()==1)): ?>
            <div class="loadmore loadmore-line loadmore-nodata"><span class="loadmore-tips">暂无更多数据</span></div>
        <?php endif; else: ?>
        <div class="text-center clearfix">
            <a href="?<?php echo http_build_query(array_merge(request()->get(), ['page'=>$__PAGELIST__->currentPage()+1])); ?>"
               data-url="?<?php echo http_build_query(array_merge(request()->get(), ['page'=>'__page__'])); ?>"
               data-page="<?php echo $__PAGELIST__->currentPage()+1; ?>" class="btn btn-default my-4 px-4 btn-loadmore"
               data-autoload="<?php echo in_array('[autoload]',['true', 'false'])?'[autoload]':'false'; ?>">
                加载更多
            </a>
        </div>
    <?php endif; endif; ?>
<!--@formatter:on-->

                    <!-- E 分页 -->
                </div>
            </div>
        </main>

        <aside class="col-xs-12 col-md-4">
            <div class="panel panel-default lasest-update">
                <!-- S 最近更新 -->
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo __('Recently update'); ?></h3>
                </div>
                <div class="panel-body">
                    <ul class="list-unstyled">
                        <?php $__K9jqk8DWdf__ = \addons\cms\model\Archives::getArchivesList(["id"=>"new","row"=>"8","orderby"=>"id","orderway"=>"desc"]); if(is_array($__K9jqk8DWdf__) || $__K9jqk8DWdf__ instanceof \think\Collection || $__K9jqk8DWdf__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__K9jqk8DWdf__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$new): $mod = ($i % 2 );++$i;?>
                        <li>
                            <span>[<a href="<?php echo $new['channel']['url']; ?>"><?php echo htmlentities($new['channel']['name']); ?></a>]</span>
                            <a class="link-dark" href="<?php echo $new['url']; ?>" title="<?php echo htmlentities($new['title']); ?>"><?php echo htmlentities($new['title']); ?></a>
                        </li>
                        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__K9jqk8DWdf__; ?>
                    </ul>
                </div>
                <!-- E 最近更新 -->
            </div>

            <div class="panel panel-blockimg">

            </div>

            
<div class="panel panel-blockimg">
    
</div>

<!-- S 热门资讯 -->
<div class="panel panel-default hot-article">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Recommend news'); ?></h3>
    </div>
    <div class="panel-body">
        <?php $__0Ke1DGkSQN__ = \addons\cms\model\Archives::getArchivesList(["id"=>"item","model"=>"1","row"=>"10","flag"=>"recommend","orderby"=>"id","orderway"=>"asc"]); if(is_array($__0Ke1DGkSQN__) || $__0Ke1DGkSQN__ instanceof \think\Collection || $__0Ke1DGkSQN__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__0Ke1DGkSQN__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <span class="num tag"><?php echo $i; ?></span>
            </div>
            <div class="media-body">
                <a class="link-dark" href="<?php echo $item['url']; ?>" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__0Ke1DGkSQN__; ?>
    </div>
</div>
<!-- E 热门资讯 -->

<div class="panel panel-blockimg">
    
</div>

<!-- S 热门标签 -->
<div class="panel panel-default hot-tags">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Hot tags'); ?></h3>
    </div>
    <div class="panel-body">
        <div class="tags">
            <?php $__nH8tZCSzUl__ = \addons\cms\model\Tag::getTagList(["id"=>"tag","orderby"=>"rand","limit"=>"30"]); if(is_array($__nH8tZCSzUl__) || $__nH8tZCSzUl__ instanceof \think\Collection || $__nH8tZCSzUl__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__nH8tZCSzUl__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i;?>
            <a href="<?php echo $tag['url']; ?>" class="tag"> <span><?php echo htmlentities($tag['name']); ?></span></a>
            <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__nH8tZCSzUl__; ?>
        </div>
    </div>
</div>
<!-- E 热门标签 -->

<!-- S 推荐下载 -->
<div class="panel panel-default recommend-article">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo __('Recommend download'); ?></h3>
    </div>
    <div class="panel-body">
        <?php $__nfcTOEKVBP__ = \addons\cms\model\Archives::getArchivesList(["id"=>"item","model"=>"3","row"=>"10","flag"=>"recommend","orderby"=>"id","orderway"=>"asc"]); if(is_array($__nfcTOEKVBP__) || $__nfcTOEKVBP__ instanceof \think\Collection || $__nfcTOEKVBP__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__nfcTOEKVBP__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
        <div class="media media-number">
            <div class="media-left">
                <span class="num tag"><?php echo $i; ?></span>
            </div>
            <div class="media-body">
                <a href="<?php echo $item['url']; ?>" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
            </div>
        </div>
        <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__nfcTOEKVBP__; ?>
    </div>
</div>
<!-- E 推荐下载 -->

<div class="panel panel-blockimg">
    
</div>


        </aside>
    </div>
</div>

<div class="container hidden-xs">
    <div class="panel panel-default">
        <!-- S 热门导航 -->
        <div class="panel-heading">
            <h3 class="panel-title">
                热门导航
                <small>为你推荐以下热门网站</small>
                <a href="<?php echo addon_url('cms/diyform/post',[':diyname'=>'navigation']); ?>" class="more">申请导航</a>
            </h3>
        </div>
        <div class="panel-body">
            <ul class="list-unstyled list-partner">
                <?php $__2XUdplmZIt__ = \addons\cms\model\Diyform::getDiydataList(["diyform"=>"3","id"=>"item","row"=>"20"]); if(is_array($__2XUdplmZIt__) || $__2XUdplmZIt__ instanceof \think\Collection || $__2XUdplmZIt__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__2XUdplmZIt__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                    <li><a href="<?php echo $item['website']; ?>" target="_blank"><img src="<?php echo cdnurl($item['image']); ?>"></a></li>
                <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__2XUdplmZIt__; ?>
            </ul>
        </div>
        <!-- E 热门导航 -->

        <!-- S 友情链接 -->
        <div class="panel-heading">
            <h3 class="panel-title">友情链接
                <small>申请友情链接请务必先做好本站链接</small>
                <a href="<?php echo addon_url('cms/diyform/post',[':diyname'=>'friendlink']); ?>" class="more">申请友链</a></h3>
        </div>
        <div class="panel-body">
            <div class="list-unstyled list-links">
                <?php $__tB8Y6rviy9__ = \addons\cms\model\Diyform::getDiydataList(["diyform"=>"2","id"=>"item","row"=>"20"]); if(is_array($__tB8Y6rviy9__) || $__tB8Y6rviy9__ instanceof \think\Collection || $__tB8Y6rviy9__ instanceof \think\Paginator): $i = 0; $__LIST__ = $__tB8Y6rviy9__;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                <a href="<?php echo $item['website']; ?>" target="_blank" title="<?php echo htmlentities($item['title']); ?>"><?php echo htmlentities($item['title']); ?></a>
                <?php endforeach; endif; else: echo "" ;endif; $__LASTLIST__=$__tB8Y6rviy9__; ?>
            </div>
        </div>
        <!-- E 友情链接 -->
    </div>

</div>

</main>

<footer>
    <div id="footer">
        <div class="container">
            <div class="row footer-inner">
                <div class="col-xs-12">
                    <div class="footer-logo pull-left mr-4">
                        <a href="<?php echo addon_url('cms/index/index'); ?>"><i class="fa fa-bookmark"></i></a>
                    </div>
                    <div class="pull-left">
                        Copyright&nbsp;©&nbsp;<?php echo date("Y"); ?> All rights reserved. <?php echo \think\Config::get('cms.sitename'); ?>
                        <a href="https://beian.miit.gov.cn" target="_blank"><?php echo htmlentities($site['beian']); ?></a>

                    <ul class="list-unstyled list-inline mt-2">
                        <li><a href="<?php echo addon_url('cms/page/index', [':diyname'=>'aboutus']); ?>">关于我们</a></li>
                        <li><a href="<?php echo addon_url('cms/page/index', [':diyname'=>'agreement']); ?>">用户协议</a></li>
                        <?php if(config('fastadmin.usercenter')): ?>
                        <li><a href="<?php echo url('index/user/index'); ?>">会员中心</a></li>
                        <?php endif; ?>
                    </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</footer>

<div id="floatbtn">
    <!-- S 浮动按钮 -->

    <?php if(isset($config['wxapp'])&&$config['wxapp']): ?>
    <a href="javascript:;">
        <i class="iconfont icon-wxapp"></i>
        <div class="floatbtn-wrapper">
            <div class="qrcode"><img src="<?php echo htmlentities(cdnurl($config['wxapp'])); ?>"></div>
            <p>微信小程序</p>
            <p>微信扫一扫体验</p>
        </div>
    </a>
    <?php endif; if(in_array('postarchives', explode(',', config('cms.usersidenav'))) && config('fastadmin.usercenter')): ?>
    <a class="hover" href="<?php echo url('index/cms.archives/post'); ?>" target="_blank">
        <i class="iconfont icon-pencil"></i>
        <em>立即<br>投稿</em>
    </a>
    <?php endif; ?>

    <div class="floatbtn-item floatbtn-share">
        <i class="iconfont icon-share"></i>
        <div class="floatbtn-wrapper" style="height:50px;top:0">
            <div class="social-share" data-initialized="true" data-mode="prepend">
                <a href="#" class="social-share-icon icon-weibo" target="_blank"></a>
                <a href="#" class="social-share-icon icon-qq" target="_blank"></a>
                <a href="#" class="social-share-icon icon-qzone" target="_blank"></a>
                <a href="#" class="social-share-icon icon-wechat"></a>
            </div>
        </div>
    </div>

    <?php if($config['qrcode']): ?>
    <a href="javascript:;">
        <i class="iconfont icon-qrcode"></i>
        <div class="floatbtn-wrapper">
            <div class="qrcode"><img src="<?php echo htmlentities(cdnurl($config['qrcode'])); ?>"></div>
            <p>微信公众账号</p>
            <p>微信扫一扫加关注</p>
        </div>
    </a>
    <?php endif; if(isset($__ARCHIVES__)): ?>
    <a id="feedback" class="hover" href="#comments">
        <i class="iconfont icon-feedback"></i>
        <em>发表<br>评论</em>
    </a>
    <?php endif; ?>

    <a id="back-to-top" class="hover" href="javascript:;">
        <i class="iconfont icon-backtotop"></i>
        <em>返回<br>顶部</em>
    </a>
    <!-- E 浮动按钮 -->
</div>


<script type="text/javascript" src="/assets/libs/jquery/dist/jquery.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/bootstrap/dist/js/bootstrap.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/fastadmin-layer/dist/layer.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/libs/art-template/dist/template-native.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/cms/js/jquery.autocomplete.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/cms/js/swiper.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/cms/js/share.min.js?v=<?php echo $site['version']; ?>"></script>
<script type="text/javascript" src="/assets/addons/cms/js/cms.js?v=<?php echo $site['version']; ?>"></script>

<?php if($isWechat): ?>
<script type="text/javascript" src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
<?php endif; ?>

<script type="text/javascript" src="/assets/addons/cms/js/common.js?v=<?php echo $site['version']; ?>"></script>

{__SCRIPT__}


</body>
</html>
