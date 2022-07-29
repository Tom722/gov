<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:74:"F:\demo\demogov\public/../application/admin\view\cms\statistics\index.html";i:1658804072;s:58:"F:\demo\demogov\application\admin\view\layout\default.html";i:1653893966;s:55:"F:\demo\demogov\application\admin\view\common\meta.html";i:1653893966;s:57:"F:\demo\demogov\application\admin\view\common\script.html";i:1653893966;}*/ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
<title><?php echo (isset($title) && ($title !== '')?$title:''); ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="renderer" content="webkit">
<meta name="referrer" content="never">
<meta name="robots" content="noindex, nofollow">

<link rel="shortcut icon" href="/assets/img/favicon.ico" />
<!-- Loading Bootstrap -->
<link href="/assets/css/backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">

<?php if(\think\Config::get('fastadmin.adminskin')): ?>
<link href="/assets/css/skins/<?php echo \think\Config::get('fastadmin.adminskin'); ?>.css?v=<?php echo \think\Config::get('site.version'); ?>" rel="stylesheet">
<?php endif; ?>

<!-- HTML5 shim, for IE6-8 support of HTML5 elements. All other JS at the end of file. -->
<!--[if lt IE 9]>
  <script src="/assets/js/html5shiv.js"></script>
  <script src="/assets/js/respond.min.js"></script>
<![endif]-->
<script type="text/javascript">
    var require = {
        config:  <?php echo json_encode($config); ?>
    };
</script>

    </head>

    <body class="inside-header inside-aside <?php echo defined('IS_DIALOG') && IS_DIALOG ? 'is-dialog' : ''; ?>">
        <div id="main" role="main">
            <div class="tab-content tab-addtabs">
                <div id="content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <section class="content-header hide">
                                <h1>
                                    <?php echo __('Dashboard'); ?>
                                    <small><?php echo __('Control panel'); ?></small>
                                </h1>
                            </section>
                            <?php if(!IS_DIALOG && !\think\Config::get('fastadmin.multiplenav') && \think\Config::get('fastadmin.breadcrumb')): ?>
                            <!-- RIBBON -->
                            <div id="ribbon">
                                <ol class="breadcrumb pull-left">
                                    <?php if($auth->check('dashboard')): ?>
                                    <li><a href="dashboard" class="addtabsit"><i class="fa fa-dashboard"></i> <?php echo __('Dashboard'); ?></a></li>
                                    <?php endif; ?>
                                </ol>
                                <ol class="breadcrumb pull-right">
                                    <?php foreach($breadcrumb as $vo): ?>
                                    <li><a href="javascript:;" data-url="<?php echo $vo['url']; ?>"><?php echo $vo['title']; ?></a></li>
                                    <?php endforeach; ?>
                                </ol>
                            </div>
                            <!-- END RIBBON -->
                            <?php endif; ?>
                            <div class="content">
                                <style>
    .panel-statistics h4 {
        color: #444;
        font-weight: bold;
        font-size: 14px;
    }

    .panel-statistics h3 {
        font-weight: 500;
        font-size: 14px;
        color: #333;
    }

    .panel-statistics .statistics-value {
        font-size:14px;
        color: #666;
    }

    .panel-statistics em {
        font-style: normal;
    }

    .panel-statistics .pull-right {
        padding-right: 10px;
    }

    .panel-statistics .table thead tr th {
        font-weight: normal;
    }

    .panel-statistics .table tbody tr td {
        font-weight: normal;
        vertical-align: middle;
    }

    .panel-statistics .table tbody tr td p {
        margin: 0;
    }

    #echarts1 textarea {
        display: block;
    }

    select.model_id {
        min-width: 60px;
    }
</style>
<div class="btn-refresh hidden" id="resetecharts"></div>
<div class="row">
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总订单金额</h4>
                    <h3>￥<?php echo sprintf('%.2f',$totalOrderAmount); ?></h3>
                </div>

                <div class="pull-right" style="color:#c8cfff;">
                    <i class="fa fa-cny fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日订单金额</h4>
                    <h3>￥<?php echo sprintf('%.2f',$todayOrderAmount); ?> <em data-toggle="tooltip" data-title="昨日：￥<?php echo sprintf('%.2f',$yesterdayOrderAmount); ?>" class="text-<?php echo $todayOrderRatio>=0?'success':'danger'; ?>"><?php echo $todayOrderRatio>=0?'+':''; ?><?php echo $todayOrderRatio; ?>%</em></h3>
                </div>

                <div class="pull-right" style="color:#ffc8c8;">
                    <i class="fa fa-calendar fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>总用户数</h4>
                    <h3><?php echo $totalUser; ?></h3>
                </div>

                <div class="pull-right" style="color:#c8e3ff;">
                    <i class="fa fa-users fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-3">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <div class="pull-left">
                    <h4>今日新增用户数</h4>
                    <h3><?php echo $todayUser; ?> <em data-toggle="tooltip" data-title="昨日：<?php echo $yesterdayUser; ?>" class="text-<?php echo $todayUserRatio>=0?'success':'danger'; ?>"><?php echo $todayUserRatio>=0?'+':''; ?><?php echo $todayUserRatio; ?>%</em></h3>
                </div>

                <div class="pull-right" style="color:#ffe9c8;">
                    <i class="fa fa-user fa-4x"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-top:20px;">
    <div class="col-xs-12 col-sm-6">
        <div class="panel">
            <div class="panel-body">
                <div id="echarts1" style="height:360px;"></div>
                <a href="javascript:" class="btn btn-refresh hidden" data-type="sale">订单统计</a>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-6">
        <div class="panel">
            <div class="panel-body">
                <div id="echarts2" style="height:360px;"></div>
                <a href="javascript:" class="btn btn-refresh hidden" data-type="percent">付费占比</a>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-3" style="margin-bottom:15px;">
                <div class="panel panel-default panel-intro panel-statistics">
                    <div class="panel-body">
                        <h3>总文档数</h3>
                        <span class="statistics-value"><?php echo $totalArchives; ?> 篇</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-3" style="margin-bottom:15px;">
                <div class="panel panel-default panel-intro panel-statistics">
                    <div class="panel-body">
                        <h3>待审核文档</h3>
                        <span class="statistics-value"><?php echo $unsettleArchives; ?> 篇</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-3" style="margin-bottom:15px;">
                <div class="panel panel-default panel-intro panel-statistics">
                    <div class="panel-body">
                        <h3>总评论数</h3>
                        <span class="statistics-value"><?php echo $totalComment; ?> 条</span>
                    </div>
                </div>
            </div>
            <div class="col-xs-3" style="margin-bottom:15px;">
                <div class="panel panel-default panel-intro panel-statistics">
                    <div class="panel-body">
                        <h3>待审核评论</h3>
                        <span class="statistics-value"><?php echo $unsettleComment; ?> 条</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--@formatter:off-->
<div class="row" style="margin-top:5px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>今日付费文章排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($todayPaidList) || $todayPaidList instanceof \think\Collection || $todayPaidList instanceof \think\Paginator): if( count($todayPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($todayPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['archives']['url']; ?>" target="_blank"><?php echo $item['archives']['title']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本周付费文章排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($weekPaidList) || $weekPaidList instanceof \think\Collection || $weekPaidList instanceof \think\Paginator): if( count($weekPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($weekPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['archives']['url']; ?>" target="_blank"><?php echo $item['archives']['title']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本月付费文章排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">标题</th>
                        <th width="20%" class="text-center">金额</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($monthPaidList) || $monthPaidList instanceof \think\Collection || $monthPaidList instanceof \think\Paginator): if( count($monthPaidList)==0 ) : echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;else: foreach($monthPaidList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['archives']['url']; ?>" target="_blank"><?php echo $item['archives']['title']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['amount']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "<tr><td colspan='3' class='text-center'>暂无数据</td></tr>" ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--@formatter:on-->
<div class="row" style="margin-top:20px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>热门搜索</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">关键字</th>
                        <th class="text-center">搜索次数</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($hotSearchList) || $hotSearchList instanceof \think\Collection || $hotSearchList instanceof \think\Paginator): if( count($hotSearchList)==0 ) : echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($hotSearchList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['url']; ?>" target="_blank"><?php echo $item['keywords']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>热门标签</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">名称</th>
                        <th width="20%" class="text-center">文档数量</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($hotTagList) || $hotTagList instanceof \think\Collection || $hotTagList instanceof \think\Paginator): if( count($hotTagList)==0 ) : echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($hotTagList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['url']; ?>" target="_blank"><?php echo $item['name']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>热门文章</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="80%">标题</th>
                        <th width="20%" class="text-center">浏览量</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($hotArchivesList) || $hotArchivesList instanceof \think\Collection || $hotArchivesList instanceof \think\Paginator): if( count($hotArchivesList)==0 ) : echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($hotArchivesList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['url']; ?>" target="_blank"><?php echo $item['title']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="mb-0 text-center"><?php echo $item['views']; ?></h5>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='2' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>订单趋势</h4>
                <div id="datefilter">
                    <form id="form1" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh"><i class="fa fa-refresh"></i></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <select name="model_id" class="form-control model_id">
                            <option value="0"><?php echo __('All'); ?></option>
                            <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): if( count($modelList)==0 ) : echo "" ;else: foreach($modelList as $key=>$item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="order" placeholder="指定日期" style="width:270px;"/>
                        </div>
                    </form>
                </div>
                <div id="echarts3" style="height:400px;width:100%;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top:15px;">
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>今日投稿排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">昵称</th>
                        <th width="20%" class="text-center">数量</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($todayContributeList) || $todayContributeList instanceof \think\Collection || $todayContributeList instanceof \think\Paginator): if( count($todayContributeList)==0 ) : echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($todayContributeList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['user']['url']; ?>" target="_blank"><?php echo $item['user']['nickname']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本周投稿排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">昵称</th>
                        <th width="20%" class="text-center">数量</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($weekContributeList) || $weekContributeList instanceof \think\Collection || $weekContributeList instanceof \think\Paginator): if( count($weekContributeList)==0 ) : echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($weekContributeList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['user']['url']; ?>" target="_blank"><?php echo $item['user']['nickname']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>本月投稿排行</h4>
                <table class="table" style="width:100%">
                    <thead>
                    <tr>
                        <th width="60%">昵称</th>
                        <th width="20%" class="text-center">数量</th>
                        <th class="text-center">占比</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(is_array($monthContributeList) || $monthContributeList instanceof \think\Collection || $monthContributeList instanceof \think\Paginator): if( count($monthContributeList)==0 ) : echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;else: foreach($monthContributeList as $key=>$item): ?>
                    <tr>
                        <td>
                            <p><a href="<?php echo $item['user']['url']; ?>" target="_blank"><?php echo $item['user']['nickname']; ?></a></p>
                        </td>
                        <td>
                            <h5 class="text-center"><?php echo $item['nums']; ?></h5>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success" data-toggle="tooltip" data-title="<?php echo $item['percent']; ?>%" style="width: <?php echo $item['percent']; ?>%"></div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; else: echo "
                    <tr>
                        <td colspan='3' class='text-center'>暂无数据</td>
                    </tr>
                    " ;endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row" style="margin-top:15px;">
    <div class="col-xs-12">
        <div class="panel panel-default panel-intro panel-statistics">
            <div class="panel-body">
                <h4>管理员发文趋势</h4>
                <div class="datefilter">
                    <form id="form2" action="" role="form" novalidate class="form-inline">
                        <a href="javascript:;" class="btn btn-primary btn-refresh"><i class="fa fa-refresh"></i></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Today'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Yesterday'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 7 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last 30 Days'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('Last month'); ?></a>
                        <a href="javascript:;" class="btn btn-success btn-filter"><?php echo __('This month'); ?></a>
                        <select name="model_id" class="form-control model_id">
                            <option value="0"><?php echo __('All'); ?></option>
                            <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): if( count($modelList)==0 ) : echo "" ;else: foreach($modelList as $key=>$item): ?>
                            <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                            <?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control input-inline datetimerange" data-type="archives" placeholder="指定日期" style="width:270px;"/>
                        </div>
                    </form>
                </div>
                <div id="echarts4" style="height:400px;width:100%;margin-top:15px;"></div>
            </div>
        </div>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
