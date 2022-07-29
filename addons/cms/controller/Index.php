<?php

namespace addons\cms\controller;

use addons\cms\model\SpiderLog;
use think\Config;

/**
 * CMS首页控制器
 * Class Index
 * @package addons\cms\controller
 */
class Index extends Base
{
    public function index()
    {
        $config = get_addon_config('cms');

        //设置TKD
        Config::set('cms.title', $config['title'] ?: __('Home'));
        Config::set('cms.keywords', $config['keywords']);
        Config::set('cms.description', $config['description']);

        //首页分页大小
        $pagesize = $config['indexpagesize'] ?? 10;
        //首页加载和分页模式
        $simple = $config['indexloadmode'] == 'paging' && $config['indexpagemode'] == 'full' ? false : true;
        $simple = $simple ? 'true' : \addons\cms\model\Archives::where('status', 'normal')->cache(true)->count();

        $archivesList = \addons\cms\model\Archives::getArchivesList(['cache' => false, 'paginate' => "{$pagesize},{$simple},page"]);
        $this->view->assign("__PAGELIST__", $archivesList);

        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch('ajax/index'));
        }

        SpiderLog::record('index', 0);

        return $this->view->fetch('/index');
    }

}
