<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:72:"F:\demo\demogov\public/../application/admin\view\cms\archives\index.html";i:1658804072;s:58:"F:\demo\demogov\application\admin\view\layout\default.html";i:1653893966;s:55:"F:\demo\demogov\application\admin\view\common\meta.html";i:1653893966;s:57:"F:\demo\demogov\application\admin\view\common\script.html";i:1653893966;}*/ ?>
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
    .form-commonsearch .form-group {
        margin-left: 0;
        margin-right: 0;
        padding: 0;
    }

    form.form-commonsearch .control-label {
        padding-right: 0;
    }

    .tdtitle {
        margin-bottom: 5px;
        font-weight: 600;
    }

    #channeltree {
        margin-left: -6px;
    }

    #channelbar .panel-heading {
        height: 55px;
        line-height: 25px;
        font-size: 14px;
    }

    @media (max-width: 1230px) {
        .fixed-table-toolbar .search .form-control {
            display: none;
        }
    }

    @media (min-width: 1200px) {
    }

    .archives-label span.label {
        font-weight: normal;
    }

    .archives-title {
        max-width: 400px;
        min-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .setflag label {
        font-weight: 400;
    }

    .nav > li.toggle-channel {
        display: none;
    }

    .col-full-width .nav > li.toggle-channel {
        display: inline-block;
    }

</style>
<div class="row">
    <div class="col-md-3 hidden-xs hidden-sm" id="channelbar" style="padding-right:0;">
        <div class="panel panel-default panel-intro">
            <div class="panel-heading">
                <div class="panel-lead">
                    <div class="pull-left">
                        <em><?php echo __('Channel list'); ?></em>
                    </div>
                    <div class="pull-right">
                        <a href="javascript:;" class="btn btn-link btn-xs btn-channel hidden-xs hidden-sm"><i class="fa fa-bars"></i></a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <span class="text-muted"><input type="checkbox" name="" id="checkall"/> <label for="checkall"><small><?php echo __('Check all'); ?></small></label></span>
                <span class="text-muted"><input type="checkbox" name="" id="expandall" checked=""/> <label for="expandall"><small><?php echo __('Expand all'); ?></small></label></span>
                <div id="channeltree">
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-md-9" id="archivespanel">
        <div class="panel panel-default panel-intro" style="background: #f1f4f6;padding-top: 0;padding-bottom: 0;box-shadow: none;">
            <div class="panel-heading">
                <?php echo build_heading(null,FALSE); ?>
                <ul class="nav nav-tabs" data-field="status">
                    <li class="toggle-channel"><a href="javascript:;" class="btn-channel"><i class="fa fa-bars"></i></a></li>
                    <li class="active"><a href="#t-all" data-value="" data-toggle="tab"><?php echo __('All'); ?></a></li>
                    <?php if(is_array($statusList) || $statusList instanceof \think\Collection || $statusList instanceof \think\Paginator): if( count($statusList)==0 ) : echo "" ;else: foreach($statusList as $key=>$vo): ?>
                    <li><a href="#t-<?php echo $vo; ?>" data-value="<?php echo $key; ?>" data-toggle="tab"><?php echo $vo; ?></a></li>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </ul>
            </div>
            <div id="myTabContent" class="tab-content" style="background:#fff;padding:15px;">
                <div class="tab-pane fade active in" id="one">
                    <div class="widget-body no-padding">
                        <div id="toolbar" class="toolbar">
                            <?php echo build_toolbar('refresh,add,del'); ?>
                            <a class="btn btn-info btn-move dropdown-toggle btn-disabled disabled"><i class="fa fa-arrow-right"></i> <?php echo __('Move'); ?></a>
                            <div class="dropdown btn-group <?php echo $auth->check('cms/archives/multi')?'':'hide'; ?>">
                                <a class="btn btn-primary btn-more dropdown-toggle btn-disabled disabled" data-toggle="dropdown"><i class="fa fa-cog"></i> <?php echo __('More'); ?></a>
                                <ul class="dropdown-menu text-left" role="menu">
                                    <li><a class="btn btn-link btn-copyselected btn-disabled disabled" href="javascript:;"><i class="fa fa-copy"></i> <?php echo __('Copy selected'); ?></a></li>
                                    <li><a class="btn btn-link btn-setspecial btn-disabled disabled" href="javascript:;"><i class="fa fa-newspaper-o"></i> <?php echo __('Join to special'); ?></a></li>
                                    <li><a class="btn btn-link btn-settag btn-disabled disabled" href="javascript:;"><i class="fa fa-tags"></i> <?php echo __('Join to tag'); ?></a></li>
                                    <li><a class="btn btn-link btn-setflag btn-disabled disabled" href="javascript:;"><i class="fa fa-flag"></i> <?php echo __('Set flag'); ?></a></li>
                                    <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=normal"><i class="fa fa-eye"></i> <?php echo __('Set to normal'); ?></a></li>
                                    <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=hidden"><i class="fa fa-eye-slash"></i> <?php echo __('Set to hidden'); ?></a></li>
                                    <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=rejected"><i class="fa fa-exclamation-circle"></i> <?php echo __('Set to rejected'); ?></a></li>
                                    <li><a class="btn btn-link btn-multi btn-disabled disabled" href="javascript:;" data-params="status=pulloff"><i class="fa fa-arrow-circle-down"></i> <?php echo __('Set to pulloff'); ?></a></li>
                                </ul>
                            </div>
                            <a class="btn btn-success btn-recyclebin btn-dialog" href="cms/archives/recyclebin" title="<?php echo __('Recycle bin'); ?>"><i class="fa fa-recycle"></i> <?php echo __('Recycle bin'); ?></a>

                            <div class="dropdown btn-group <?php echo $auth->check('cms/archives/content')?'':'hide'; ?>">
                                <a href="javascript:;" class="btn btn-primary dropdown-toggle" title="<?php echo __('Addon list'); ?>" data-toggle="dropdown"><i class="fa fa-file"></i> <?php echo __('Addon list'); ?></a>
                                <ul class="dropdown-menu text-left" role="menu">
                                    <?php if(is_array($modelList) || $modelList instanceof \think\Collection || $modelList instanceof \think\Paginator): $i = 0; $__LIST__ = $modelList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$item): $mod = ($i % 2 );++$i;?>
                                    <li><a class="btn btn-link btn-addtabs" href="<?php echo url('cms.archives/content'); ?>/model_id/<?php echo $item['id']; ?>" title="<?php echo $item['name']; ?>"><i class="fa fa-file"></i> <?php echo $item['name']; ?></a></li>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </ul>
                            </div>
                        </div>
                        <table id="table" class="table table-striped table-bordered table-hover table-nowrap"
                               data-operate-edit="<?php echo $auth->check('cms/archives/edit'); ?>"
                               data-operate-del="<?php echo $auth->check('cms/archives/del'); ?>"
                               width="100%">
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<script id="channeltpl" type="text/html">
    <div class="">
        <div class="alert alert-warning-light ui-sortable-handle" style="cursor: move;">
            <b><?php echo __('Warning'); ?></b><br>
            <?php echo __('Move tips'); ?>
        </div>
        <!-- /.box-body -->
        <div class="text-black">
            <div class="row">
                <div class="col-sm-12">
                    <select name="channel" class="form-control">
                        <option value="0"><?php echo __('Please select channel'); ?></option>
                        <?php echo $channelOptions; ?>
                    </select>
                </div>
            </div>
            <!-- /.row -->
        </div>
    </div>
</script>
<script id="specialtpl" type="text/html">
    <div class="">
        <div class="text-black">
            <div class="row">
                <div class="col-sm-12">
                    <select name="special" class="form-control selectpicker" data-live-search="true">
                        <option value="0"><?php echo __('Please select special'); ?></option>
                        <?php if(is_array($specialList) || $specialList instanceof \think\Collection || $specialList instanceof \think\Paginator): if( count($specialList)==0 ) : echo "" ;else: foreach($specialList as $key=>$item): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo $item['title']; ?></option>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
            </div>
            <!-- /.row -->
        </div>
    </div>
</script>
<script id="flagtpl" type="text/html">
    <div class="setflag">
        <div class="text-black">
            <div class="row">
                <div class="col-sm-12">
                    <p><b>标志：</b></p>
                    <!--@formatter:off-->
                        <?php if(is_array($flagList) || $flagList instanceof \think\Collection || $flagList instanceof \think\Paginator): if( count($flagList)==0 ) : echo "" ;else: foreach($flagList as $key=>$vo): ?>
                        <label for="flag-<?php echo $key; ?>"><input type="checkbox" id="flag-<?php echo $key; ?>" value="<?php echo $key; ?>" name="flag[]"> <?php echo $vo; ?></label>
                        <?php endforeach; endif; else: echo "" ;endif; ?>
                    <!--@formatter:on-->
                    <p><b>操作：</b></p>
                    <p>
                        <label for="type-add"><input type="radio" name="type" value="add" id="type-add" checked> 添加</label>
                        <label for="type-remove"><input type="radio" name="type" value="del" id="type-remove"> 移除</label>
                    </p>
                </div>
            </div>
            <!-- /.row -->
        </div>
    </div>
</script>

<script id="tagtpl" type="text/html">
    <div class="">
        <div class="text-black">
            <div class="row">
                <div class="col-sm-12">
                    <div style="margin-bottom:5px;">
                        请输入一个或多个标签
                    </div>
                </div>
                <div class="col-sm-12">
                    <input id="c-tags" data-rule="" class="form-control" placeholder="输入后空格确认" name="tags" type="text" value="">
                </div>
            </div>
            <!-- /.row -->
        </div>
    </div>
</script>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="/assets/js/require<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js" data-main="/assets/js/require-backend<?php echo \think\Config::get('app_debug')?'':'.min'; ?>.js?v=<?php echo htmlentities($site['version']); ?>"></script>
    </body>
</html>
