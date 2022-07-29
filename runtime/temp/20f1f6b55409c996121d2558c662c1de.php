<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:38:"F:\demo\demogov\addons\ask\config.html";i:1639368315;s:58:"F:\demo\demogov\application\admin\view\layout\default.html";i:1653893966;s:55:"F:\demo\demogov\application\admin\view\common\meta.html";i:1653893966;s:57:"F:\demo\demogov\application\admin\view\common\script.html";i:1653893966;}*/ ?>
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
    #config-form div a.btn {
        color: #fff;
        text-decoration: none;
    }
</style>
<!--@formatter:off-->
<?php 
$groupList = [
    'config'=>'system_user_id,admin_user_ids,sitename,sitelogo,title,keywords,description,searchtype,baidupush,loadmode,commentlistmode,editormode,pagesimple,uploadparts,cachelifetime,urlsuffix,isprivate,isanonymous,adoptdays,isstillanswer,isarticlepaidcomment',
    'thumb'=>'default_question_image,default_tag_image,default_block_image,default_zone_image,default_category_image,default_article_image',
    'ratio'=>'peepanswerratio,bestanswerratio,articleratio',
    'wxapp'=>'wxappid,wxappsecret,app_id,app_secret',
    'rewrite'=>'domain,rewrite',
    'audit'=>'isaudit,audittype,nlptype,aip_appid,aip_apikey,aip_secretkey',
    'score'=>'score,scorecyclelimit,limitscore',
    'peep'=>'peeptype,peepprice,peeppricelist,peepscore,peepscorelist',
    'award'=>'awardtype,pricelist,appendpricelist,minprice,maxprice,iscustomprice,scorelist,appendscorelist,minscore,maxscore,iscustomscore',
    'other'=>'',
];
$group = [];
foreach($groupList as $k=>$v){
   $item = explode(',', $v);
   $item = array_flip($item);
   $group = array_merge($group, array_map(function($value) use($k){return $k;}, $item));
}
 ?>
<form id="config-form" class="edit-form form-horizontal" role="form" data-toggle="validator" method="POST" action="">
    <div class="alert <?php echo (isset($addon['tips']['extend']) && ($addon['tips']['extend'] !== '')?$addon['tips']['extend']:'alert-info-light'); ?>" style="margin-bottom:10px;">
        <a href="<?php echo addon_url('ask/index/index'); ?>" class="btn btn-warning" target="_blank"><i class="fa fa-home"></i> 问答首页</a>
        <a href="/index/user/index" class="btn btn-info" target="_blank"><i class="fa fa-user"></i> 会员中心</a>
    </div>

    <div class="panel panel-default panel-intro">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-group">
                <li class="active"><a href="#all" data-toggle="tab">全部</a></li>
                <li><a href="#config" data-toggle="tab">基础配置</a></li>
                <li><a href="#thumb" data-toggle="tab">图片配置</a></li>
                <li><a href="#ratio" data-toggle="tab">分成配置</a></li>
                <li><a href="#wxapp" data-toggle="tab">小程序&APP</a></li>
                <li><a href="#rewrite" data-toggle="tab">伪静态配置</a></li>
                <li><a href="#audit" data-toggle="tab">审核配置</a></li>
                <li><a href="#score" data-toggle="tab">积分配置</a></li>
                <li><a href="#peep" data-toggle="tab">付费查看</a></li>
                <li><a href="#award" data-toggle="tab">悬赏配置</a></li>
                <li><a href="#other" data-toggle="tab">其它</a></li>
            </ul>
        </div>

        <div class="panel-body">
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="one">

                    <table class="table table-striped table-config">
                        <tbody>
                        <?php foreach($addon['config'] as $item): ?>
                        <tr data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                            <td width="15%"><?php echo $item['title']; ?></td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-8 col-xs-12">
                                        <?php switch($item['type']): case "string": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-rule="<?php echo $item['rule']; ?>" data-tip="<?php echo $item['tip']; ?>"/>
                                        <?php break; case "password": ?>
                                        <input <?php echo $item['extend']; ?> type="password" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-rule="<?php echo $item['rule']; ?>" data-tip="<?php echo $item['tip']; ?>"/>
                                        <?php break; case "text": ?>
                                        <textarea <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]" class="form-control" data-rule="<?php echo $item['rule']; ?>" rows="5" data-tip="<?php echo $item['tip']; ?>"><?php echo htmlentities($item['value']); ?></textarea>
                                        <?php break; case "array": if($item['tip']): ?>
                                            <div class="alert alert-info-light">
                                                <?php echo $item['tip']; if(in_array($item['name'], ['score', 'scorecyclelimit', 'limitscore'])): ?>
                                                <a href="javascript:" data-toggle="popover" data-title="键(类型)说明" data-trigger="hover" data-html="true" data-content="postquestion:发布问题<br>postanswer:发布答案<br>postarticle:发布文章<br>postcomment:发布评论<br>peepsetting:设定付费答案<br>bestanswer:最佳答案"><i class="fa fa-info-circle"></i> 类型说明</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; if($item['name']=='scorecyclelimit'): ?>
                                        <dl <?php echo $item['extend']; ?> class="fieldlist" data-name="row[<?php echo $item['name']; ?>]" data-template="scorecyclelimittpl">
                                            <dd>
                                                <ins style="width:110px;">类型</ins>
                                                <ins style="width:70px;">日</ins>
                                                <ins style="width:70px;">月</ins>
                                                <ins style="width:70px;">年</ins>
                                            </dd>
                                            <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                                            <textarea name="row[<?php echo $item['name']; ?>]" class="form-control hide" cols="30" rows="5"><?php echo htmlentities(json_encode($item['value'])); ?></textarea>
                                        </dl>
                                        <?php else: ?>
                                        <dl class="fieldlist" data-name="row[<?php echo $item['name']; ?>]">
                                            <dd>
                                                <ins>键</ins>
                                                <ins>值</ins>
                                            </dd>
                                            <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                                            <textarea name="row[<?php echo $item['name']; ?>]" cols="30" rows="5" class="hide"><?php echo htmlentities(json_encode($item['value'])); ?></textarea>
                                        </dl>
                                        <?php endif; break; case "date": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "time": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "datetime": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "number": ?>
                                        <input <?php echo $item['extend']; ?> type="number" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "checkbox": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                        <label for="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>][]" type="checkbox" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                        <?php endforeach; endif; else: echo "" ;endif; break; case "radio": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                        <label for="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>]" type="radio" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                        <?php endforeach; endif; else: echo "" ;endif; break; case "select": case "selects": ?>
                                        <select <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]<?php echo $item['type']=='selects'?'[]':''; ?>" class="form-control selectpicker" data-tip="<?php echo $item['tip']; ?>" <?php echo $item['type']=='selects'?'multiple':''; ?>>
                                            <?php if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                            <option value="<?php echo $key; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>selected<?php endif; ?>><?php echo $vo; ?></option>
                                            <?php endforeach; endif; else: echo "" ;endif; ?>
                                        </select>
                                        <?php break; case "image": case "images": ?>
                                        <div class="form-inline">
                                            <input id="c-<?php echo $item['name']; ?>" class="form-control" size="35" name="row[<?php echo $item['name']; ?>]" type="text" value="<?php echo htmlentities($item['value']); ?>" data-tip="<?php echo $item['tip']; ?>">
                                            <span><button type="button" id="plupload-<?php echo $item['name']; ?>" class="btn btn-danger plupload" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/*" data-multiple="<?php echo $item['type']=='image'?'false':'true'; ?>" data-preview-id="p-<?php echo $item['name']; ?>"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                                            <span><button type="button" id="fachoose-<?php echo $item['name']; ?>" class="btn btn-primary fachoose" data-input-id="c-<?php echo $item['name']; ?>" data-mimetype="image/*" data-multiple="<?php echo $item['type']=='image'?'false':'true'; ?>"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                            <ul class="row list-inline plupload-preview" id="p-<?php echo $item['name']; ?>"></ul>
                                        </div>
                                        <?php break; case "file": case "files": ?>
                                        <div class="form-inline">
                                            <input id="c-<?php echo $item['name']; ?>" class="form-control" size="35" name="row[<?php echo $item['name']; ?>]" type="text" value="<?php echo htmlentities($item['value']); ?>" data-tip="<?php echo $item['tip']; ?>">
                                            <span><button type="button" id="plupload-<?php echo $item['name']; ?>" class="btn btn-danger plupload" data-input-id="c-<?php echo $item['name']; ?>" data-multiple="<?php echo $item['type']=='file'?'false':'true'; ?>"><i class="fa fa-upload"></i> <?php echo __('Upload'); ?></button></span>
                                            <span><button type="button" id="fachoose-<?php echo $item['name']; ?>" class="btn btn-primary fachoose" data-input-id="c-<?php echo $item['name']; ?>" data-multiple="<?php echo $item['type']=='file'?'false':'true'; ?>"><i class="fa fa-list"></i> <?php echo __('Choose'); ?></button></span>
                                        </div>
                                        <?php break; case "bool": ?>
                                        <label for="row[<?php echo $item['name']; ?>]-yes"><input id="row[<?php echo $item['name']; ?>]-yes" name="row[<?php echo $item['name']; ?>]" type="radio" value="1" <?php echo !empty($item['value'])?'checked':''; ?> data-tip="<?php echo $item['tip']; ?>" /> <?php echo __('Yes'); ?></label>
                                        <label for="row[<?php echo $item['name']; ?>]-no"><input id="row[<?php echo $item['name']; ?>]-no" name="row[<?php echo $item['name']; ?>]" type="radio" value="0" <?php echo !empty($item['value'])?'':'checked'; ?> data-tip="<?php echo $item['tip']; ?>" /> <?php echo __('No'); ?></label>
                                        <?php break; default: ?><?php echo $item['value']; endswitch; ?>
                                    </div>
                                    <div class="col-sm-4"></div>
                                </div>

                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="form-group layer-footer">
                        <label class="control-label col-xs-12 col-sm-2"></label>
                        <div class="col-xs-12 col-sm-8">
                            <button type="submit" class="btn btn-primary btn-embossed disabled"><?php echo __('OK'); ?></button>
                            <button type="reset" class="btn btn-default btn-embossed"><?php echo __('Reset'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!--@formatter:on-->
<script>
    require.callback = function () {
        var tabevent = function () {
            $(document).on("click", ".nav-group li a[data-toggle='tab']", function () {
                var type = $(this).attr("href").substring(1);
                if (type == 'all') {
                    $(".table-config tr").show();
                } else {
                    $(".table-config tr").hide();
                    $(".table-config tr[data-type='" + type + "']").show();
                }
            });
            $('body').on('click', function (e) {
                if ($(e.target).data('toggle') !== 'popover'
                    && $(e.target).parents('[data-toggle="popover"]').length === 0
                    && $(e.target).parents('.popover.in').length === 0) {
                    $('[data-toggle="popover"]').popover('hide');
                }
            });
            $('[data-toggle="popover"]').popover();
        };
        define('backend/addon', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                config: function () {
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
        define('backend/ask/config', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                index: function () {
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
    }
</script>

<script type="text/html" id="scorecyclelimittpl">
    <dd class="form-inline">
        <input type="text" name="<%=name%>[<%=index%>][type]" class="form-control" value="<%=row.type%>" style="width:110px;"/>
        <input type="text" name="<%=name%>[<%=index%>][day]" id="c-downloadurl-<%=index%>" class="form-control" value="<%=row.day%>" style="width:70px;"/>
        <input type="text" name="<%=name%>[<%=index%>][month]" class="form-control" value="<%=row.month%>" style="width:70px;"/>
        <input type="text" name="<%=name%>[<%=index%>][year]" class="form-control" value="<%=row.year%>" style="width:70px;"/>
        <span class="btn btn-sm btn-danger btn-remove"><i class="fa fa-times"></i></span> <span class="btn btn-sm btn-primary btn-dragsort"><i class="fa fa-arrows"></i></span>
    </dd>
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
