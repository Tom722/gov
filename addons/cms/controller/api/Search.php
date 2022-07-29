<?php

namespace addons\cms\controller\api;

use addons\cms\library\FulltextSearch;
use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\SearchLog;
use think\Config;
use think\Session;

/**
 * 搜索控制器
 */
class Search extends Base
{
    protected $noNeedLogin = ['index'];

    public function index()
    {
        $config = get_addon_config('cms');

        $search = $this->request->request("search", $this->request->request("q", ""));
        $search = mb_substr($search, 0, 100);
        if ($search) {
            $log = SearchLog::getByKeywords($search);
            if ($log) {
                $log->setInc("nums");
            } else {
                SearchLog::create(['keywords' => $search, 'nums' => 1]);
            }
        }
        $filterList = [];
        $orderList = [];

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $params = ['q' => $search];
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

        $pageList = Archives
            ::where('status', 'normal')
            ->where(function ($query) use ($search) {
                $keywordArr = explode(' ', $search);
                foreach ($keywordArr as $index => $item) {
                    $query->where('title', 'like', '%' . $item . '%');
                }
            })
            ->whereNull('deletetime')
            ->order($orderby, $orderway)
            ->paginate(10, $config['pagemode'] == 'simple');

        foreach ($pageList as $item) {
            $item->append(['images_list']);
        }

        $pageList->appends(array_filter($params));

        Config::set('cms.title', __("Search for %s", $search));
        $this->success('', [
            'filterList' => $filterList,
            'orderList'  => $orderList,
            'pageList'   => $pageList
        ]);
    }

}
