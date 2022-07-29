<?php

namespace addons\cms\controller;

use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Fields;
use addons\cms\model\Special as SpecialModel;
use addons\cms\model\SpiderLog;
use addons\cms\model\Taggable;
use think\Config;
use think\Exception;

/**
 * 专题控制器
 * Class Special
 * @package addons\cms\controller
 */
class Special extends Base
{
    /**
     * 专题页面
     */
    public function index()
    {
        $config = get_addon_config('cms');

        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $special = SpecialModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->param('id', '');
            $special = SpecialModel::get($id);
        }
        if (!$special || $special['status'] != 'normal') {
            $this->error(__('No specified special found'));
        }
        $special->setInc("views", 1);

        Service::appendTextAndList('special', 0, $special);

        //模板名称
        $special['template'] = $special['template'] ? $special['template'] : 'special.html';
        $template = ($this->request->isAjax() ? '/ajax/' : '/') . $special["template"];
        $template = preg_replace('/\.html$/', '', $template);

        $pagelistParams = Service::getPagelistParams($template);
        //分页大小
        $pagesize = $pagelistParams['pagesize'] ?? 10;
        //过滤条件
        $filterPagelist = function ($query) use ($pagelistParams) {
            if (isset($pagelistParams['condition'])) {
                $query->where($pagelistParams['condition']);
            }
        };

        $archivesList = \addons\cms\model\Archives::with(['channel'])
            ->where(function ($query) use ($special) {
                //$query->whereRaw("(`special_ids`!='' AND FIND_IN_SET('{$special->id}', `special_ids`))");
                //if ($special['tag_ids']) {
                //    $query->whereOr('id', 'in', function ($query) use ($special) {
                //        return $query->name("cms_taggable")->where("tag_id", 'in', $special['tag_ids'])->field("archives_id");
                //    });
                //}
                $tableName = (new Taggable)->getTable();
                $archivesIds = Archives::whereRaw("(`special_ids`!='' AND FIND_IN_SET({$special->id}, `special_ids`))")
                    ->whereOr("id IN (SELECT archives_id FROM {$tableName} WHERE tag_id IN (" . ($special->tag_ids ? $special->tag_ids : '0') . "))")
                    ->cache(true)
                    ->column('id');
                $archivesIds = array_filter(array_unique($archivesIds));
                $query->where('id', 'in', $archivesIds);
            })
            ->where($filterPagelist)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->order('weigh DESC,publishtime DESC')
            ->paginate($pagesize, $config['pagemode'] == 'simple');

        $this->view->assign("archivesList", $archivesList);
        $this->view->assign("__PAGELIST__", $archivesList);
        $this->view->assign("__SPECIAL__", $special);

        $channel = \addons\cms\model\Channel::getChannelByLinktype('special', $special['id']);
        $this->view->assign("__CHANNEL__", $channel);

        SpiderLog::record('special', $special['id']);

        //设置TKD
        Config::set('cms.title', isset($special['seotitle']) && $special['seotitle'] ? $special['seotitle'] : $special['title']);
        Config::set('cms.keywords', $special['keywords']);
        Config::set('cms.description', $special['description']);

        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch($template));
        }
        return $this->view->fetch($template);
    }

}
