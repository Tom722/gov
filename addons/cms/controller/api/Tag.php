<?php

namespace addons\cms\controller\api;

use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Tag as TagModel;
use addons\cms\model\Taggable;
use think\Config;

/**
 * 标签控制器
 */
class Tag extends Base
{
    protected $noNeedLogin = ['index'];

    public function index()
    {
        $config = get_addon_config('cms');

        $tag = null;
        $name = $this->request->param('name');
        if ($name && !is_numeric($name)) {
            $tag = TagModel::getByName($name);
        } else {
            $id = $name ? $name : $this->request->param('id', '');
            $tag = TagModel::get($id);
        }
        if (!$tag || $tag['status'] != 'normal') {
            $this->error(__('No specified tags found'));
        }

        $filterList = [];
        $orderList = [];

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = [];
        if ($orderby) {
            $params['orderby'] = $orderby;
        }
        if ($orderway) {
            $params['orderway'] = $orderway;
        }

        //默认排序字段
        $orders = [
            ['name' => 'default', 'field' => 'weigh', 'title' => __('Default')],
            ['name' => 'views', 'field' => 'views', 'title' => __('Views')],
            ['name' => 'id', 'field' => 'id', 'title' => __('Post date')],
        ];

        //获取排序列表
        list($orderList, $orderby, $orderway) = Service::getOrderList($orderby, $orderway, $orders, $params);

        $pageList = Archives::with(['channel'])
            ->where('status', 'normal')
            ->where('id', 'in', function ($query) use ($tag) {
                return $query->name('cms_taggable')->where('tag_id', $tag['id'])->field('archives_id');
            })
            ->field('id,user_id,title,channel_id,dislikes,likes,tags,createtime,image,images,style,comments,views,diyname')
            ->order($orderby, $orderway)
            ->paginate(10, $config['pagemode'] == 'simple');

        $pageList->appends(array_filter($params));

        foreach ($pageList as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }
            //小程序只显示9张图
            $item->images_list = array_slice(array_filter(explode(',', $item['images'])), 0, 9);
            unset($item['weigh'], $item['status'], $item['deletetime'], $item['memo']);
            $item->id = $item->eid;
        }

        $this->success('', [
            'filterList' => $filterList,
            'orderList'  => $orderList,
            'pageList'   => $pageList
        ]);
    }
}
