<?php

namespace addons\cms\controller\api;

use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Fields;
use addons\cms\model\Special as SpecialModel;
use addons\cms\model\Taggable;
use think\Exception;
use think\Config;

/**
 * 专题控制器
 */
class Special extends Base
{

    protected $noNeedLogin = ['*'];

    /**
     * 专题列表
     */
    public function special()
    {
        $list = SpecialModel::field('id,diyname,image,intro,keywords,label,title,views,createtime')
            ->where('status', 'normal')
            ->paginate(10);
        $this->success('获取成功', $list);
    }


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
        $special->banner = cdnurl($special->banner, true);

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
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->field('id,user_id,title,channel_id,dislikes,likes,tags,createtime,image,images,style,comments,views,diyname')
            ->order('weigh DESC,publishtime DESC')
            ->paginate(10, $config['pagemode'] == 'simple');

        foreach ($archivesList as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }
            //小程序只显示9张图
            $item->images_list = array_slice(array_filter(explode(',', $item['images'])), 0, 9);
            unset($item['weigh'], $item['status'], $item['deletetime'], $item['memo']);
            if ($item->user) {
                $item->user->visible(explode(',', 'id,nickname,avatar'));
            }
            $item->id = $item->eid;
        }

        $this->success('获取成功', [
            'special'      => $special,
            'archivesList' => $archivesList
        ]);
    }
}
