<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:38:"F:\demo\demogov\addons\cms\config.html";i:1650535600;s:58:"F:\demo\demogov\application\admin\view\layout\default.html";i:1653893966;s:55:"F:\demo\demogov\application\admin\view\common\meta.html";i:1653893966;s:57:"F:\demo\demogov\application\admin\view\common\script.html";i:1653893966;}*/ ?>
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
    #config-form .alert-info-light .dropdown-menu li a {
        text-decoration: none;
    }
</style>
<!--@formatter:off-->
<?php 
$groupList = [
    'config'=>'system_user_id,sitename,sitelogo,title,keywords,description,indexpagesize,theme,qrcode,wxapp,donateimage,userpage,openedsite,searchtype,autopinyin,baidupush,usersidenav,loadmode,pagemode,indexloadmode,indexpagemode,cachelifetime,urlsuffix',
    'thumb'=>'default_archives_img,default_channel_img,default_block_img,default_page_img,default_special_img',
    'wxapp'=>'wxappid,wxappsecret',
    'rewrite'=>'domain,rewrite,urlsuffix,moduleurlsuffix',
    'audit'=>'isarchivesaudit,iscommentaudit,audittype,nlptype,aip_appid,aip_apikey,aip_secretkey',
    'dict'=>'downloadtype,spiders,flagtype,autolinks',
    'other'=>'archivesratio,score,limitscore,ispaylogin,paytypelist,apikey,archiveseditmode,auditnotice,noticetemplateid,channelallocate,archivesdatalimit,specialdatalimit,pagedatalimit,diyformdatalimit',
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
        <div style="margin-top:10px;">
            <a href="<?php echo addon_url('cms/index/index'); ?>" class="btn btn-warning" target="_blank"><i class="fa fa-home"></i> CMS首页</a>
            <a href="/index/user/index" class="btn btn-info" target="_blank"><i class="fa fa-user"></i> 会员中心</a>
            <div class="btn-group">
                <a class="btn btn-primary" href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/all.xml" target="_blank"><i class="fa fa-sitemap fa-fw"></i> Sitemap</a>
                <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="javascript:;" aria-expanded="true">
                    <span class="fa fa-caret-down"></span></a>
                <ul class="dropdown-menu">
                    <li role="presentation"><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/all.xml" target="_blank">全部</a></li>
                    <li role="presentation" class="divider"></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/archives.xml" target="_blank">文档</a></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/tags.xml" target="_blank">标签</a></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/users.xml" target="_blank">会员</a></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/specials.xml" target="_blank">专题</a></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/pages.xml" target="_blank">单页</a></li>
                    <li><a href="<?php echo addon_url('cms/sitemap/index', [], false, false); ?>/type/diyforms.xml" target="_blank">自定义表单</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="panel panel-default panel-intro">
        <div class="panel-heading">
            <ul class="nav nav-tabs nav-group">
                <li class="active"><a href="#all" data-toggle="tab">全部</a></li>
                <li><a href="#config" data-toggle="tab">基础</a></li>
                <li><a href="#thumb" data-toggle="tab">缩略图</a></li>
                <li><a href="#wxapp" data-toggle="tab">微信小程序</a></li>
                <li><a href="#rewrite" data-toggle="tab">伪静态</a></li>
                <li><a href="#audit" data-toggle="tab">审核</a></li>
                <li><a href="#dict" data-toggle="tab">字典</a></li>
                <li><a href="#other" data-toggle="tab">其它</a></li>
                <li class="pull-right"><a href="<?php echo url('cms/ajax/config?name=signin'); ?>" title="签到配置" class="dialogit">签到</a></li>
                <li class="pull-right"><a href="<?php echo url('cms/ajax/config?name=sms'); ?>" title="短信配置" class="dialogit">短信</a></li>
                <li class="pull-right"><a href="<?php echo url('cms/ajax/config?name=third'); ?>" title="登录配置" class="dialogit">登录</a></li>
                <li class="pull-right"><a href="<?php echo url('cms/ajax/config?name=oss'); ?>" title="云存储配置" class="dialogit">云存储</a></li>
                <li class="pull-right"><a href="<?php echo url('cms/ajax/config?name=epay'); ?>" title="支付配置" class="dialogit">支付</a></li>
            </ul>
        </div>

        <div class="panel-body">
            <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="one">

                    <table class="table table-striped table-config">
                        <tbody>
                        <?php foreach($addon['config'] as $item): ?>
                        <tr data-type="<?php echo isset($group[$item['name']])?$group[$item['name']]:'other'; ?>">
                            <td width="15%">
                                <?php echo $item['title']; if($item['type']=='array' && $item['tip']): ?>
                                <a href="javascript:" class="text-info" data-toggle="popover" data-content="<?php echo $item['tip']; ?>" data-trigger="click" data-title="配置提示" data-html="true"><i class="fa fa-info-circle"></i></a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="row">
                                    <div class="col-sm-8 col-xs-12">
                                        <?php switch($item['type']): case "string": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-rule="<?php echo $item['rule']; ?>" data-tip="<?php echo $item['tip']; ?>"/>
                                        <?php break; case "password": ?>
                                        <input <?php echo $item['extend']; ?> type="password" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-rule="<?php echo $item['rule']; ?>" data-tip="<?php echo $item['tip']; ?>"/>
                                        <?php break; case "text": ?>
                                        <textarea <?php echo $item['extend']; ?> name="row[<?php echo $item['name']; ?>]" class="form-control" data-rule="<?php echo $item['rule']; ?>" rows="5" data-tip="<?php echo $item['tip']; ?>"><?php echo htmlentities($item['value']); ?></textarea>
                                        <?php break; case "array": ?>
                                        <dl class="fieldlist" data-name="row[<?php echo $item['name']; ?>]">
                                            <dd>
                                                <ins><?php echo __('Array key'); ?></ins>
                                                <ins><?php echo __('Array value'); ?></ins>
                                            </dd>
                                            <dd><a href="javascript:;" class="btn btn-sm btn-success btn-append"><i class="fa fa-plus"></i> <?php echo __('Append'); ?></a></dd>
                                            <textarea name="row[<?php echo $item['name']; ?>]" cols="30" rows="5" class="hide"><?php echo htmlentities(json_encode($item['value'])); ?></textarea>
                                        </dl>
                                        <?php break; case "date": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "time": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "datetime": ?>
                                        <input <?php echo $item['extend']; ?> type="text" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control datetimepicker" data-date-format="YYYY-MM-DD HH:mm:ss" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "number": ?>
                                        <input <?php echo $item['extend']; ?> type="number" name="row[<?php echo $item['name']; ?>]" value="<?php echo htmlentities($item['value']); ?>" class="form-control" data-tip="<?php echo $item['tip']; ?>" data-rule="<?php echo $item['rule']; ?>"/>
                                        <?php break; case "checkbox": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                        <label for="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>][]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>][]" type="checkbox" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                        <span class="msg-box n-right" for="c-<?php echo $item['name']; ?>"></span>
                                        <?php break; case "radio": if(is_array($item['content']) || $item['content'] instanceof \think\Collection || $item['content'] instanceof \think\Paginator): if( count($item['content'])==0 ) : echo "" ;else: foreach($item['content'] as $key=>$vo): ?>
                                        <label for="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>"><input id="row[<?php echo $item['name']; ?>]-<?php echo $key; ?>" name="row[<?php echo $item['name']; ?>]" type="radio" value="<?php echo $key; ?>" data-tip="<?php echo $item['tip']; ?>" <?php if(in_array(($key), is_array($item['value'])?$item['value']:explode(',',$item['value']))): ?>checked<?php endif; ?> /> <?php echo $vo; ?></label>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                        <span class="msg-box n-right" for="c-<?php echo $item['name']; ?>"></span>
                                        <?php break; case "select": case "selects": if($item['name']==='spiderfollow'): $item['content'] = get_addon_config('cms')['spiders']??[]; endif; ?>
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
                        <label class="control-label col-xs-12 col-sm-2" style="width:15%;"></label>
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
        };

        var customrule = function () {
            $.validator.config({
                rules: {
                    config: function (element) {
                        if (this.key == "row[archivesratio]") {
                            var valueArr = this.value.split(/:/);
                            if (isNaN(valueArr[0]) || isNaN(valueArr[1])) {
                                return '格式不正确';
                            }
                            if (parseFloat(valueArr[0]) + parseFloat(valueArr[1]) != 1) {
                                return '分成占比相加必须等于1';
                            }
                        } else if (this.key == "row[cachelifetime]") {
                            if (isNaN(this.value) && ['true', 'false'].indexOf(this.value) < 0) {
                                return "格式不正确,只支持 数字/true";
                            }
                        } else if (this.key == "row[theme]") {
                            if (!/^([a-zA-Z0-9\-_]+)$/.test(this.value)) {
                                return "只支持字母数字下划线";
                            }
                        }
                        return true;
                        return $.ajax({
                            url: 'cms/ajax/check_config_available',
                            type: 'POST',
                            data: {name: element.name, value: element.value},
                            dataType: 'json'
                        });
                    },
                }
            });
        };
        define('backend/addon', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                config: function () {
                    customrule();
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
        define('backend/cms/config', ['jquery', 'form'], function ($, Form) {
            var Controller = {
                index: function () {
                    customrule();
                    Form.api.bindevent($("form[role=form]"));
                    tabevent();
                }
            };
            return Controller;
        });
    }
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
