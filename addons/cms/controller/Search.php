<?php

namespace addons\cms\controller;

use addons\cms\library\FulltextSearch;
use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Modelx;
use addons\cms\model\SearchLog;
use think\Config;
use think\Session;

/**
 * 搜索控制器
 * Class Search
 * @package addons\cms\controller
 */
class Search extends Base
{
    public function index()
    {
        $config = get_addon_config('cms');

        $search = $this->request->request("search", $this->request->request("q", ""));
        $search = strip_tags($search);
        $search = mb_substr($search, 0, 100);

        if (!$search) {
            $this->error("关键字不能为空");
        }
        //搜索入库
        $token = $this->request->request("__searchtoken__");
        if ($search && $token && $token == Session::get("__searchtoken__")) {
            $log = SearchLog::getByKeywords($search);
            if ($log) {
                $log->setInc("nums");
            } else {
                SearchLog::create(['keywords' => $search, 'nums' => 1]);
            }
        }

        if ($config['searchtype'] == 'xunsearch') {
            return $this->xunsearch();
        }

        $channel = $model = null;
        $channel_id = $this->request->get('channel_id');
        $model_id = $this->request->get('model_id');
        if ($channel_id || $model_id) {
            $channel = \addons\cms\model\Channel::get($channel_id);
            $model_id = $channel ? $channel['model_id'] : $model_id;

            //加载模型数据
            $model = Modelx::get($model_id);
            if (!$model) {
                $this->error(__('No specified model found'));
            }
        }

        $filterList = [];
        $orderList = [];

        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '');
        $orderway = $orderway && in_array(strtolower($orderway), ['asc', 'desc']) ? $orderway : 'desc';
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

        //模板名称
        $template = ($this->request->isAjax() ? '/ajax' : '/') . 'search';

        $pagelistParams = Service::getPagelistParams($template);
        //分页大小
        $pagesize = $pagelistParams['pagesize'] ?? 10;
        //过滤条件
        $filterPagelist = function ($query) use ($pagelistParams) {
            if (isset($pagelistParams['condition'])) {
                $query->where($pagelistParams['condition']);
            }
        };

        $pageList = new Archives();
        if ($model) {
            $pageList->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
                ->field('id,content', true, config('database.prefix') . $model['table'], 'n')
                ->where('model_id', $model->id);
        }
        $pageList = $pageList->with(['channel', 'user'])->alias('a')
            ->where('a.status', 'normal')
            ->where(function ($query) use ($search) {
                $keywordArr = explode(' ', $search);
                foreach ($keywordArr as $index => $item) {
                    $query->where('a.title', 'like', '%' . $item . '%');
                }
            })
            ->whereNull('a.deletetime')
            ->field('a.*')
            ->where(function ($query) use ($channel) {
                if ($channel) {
                    $query->where(function ($query) use ($channel) {
                        if ($channel['listtype'] <= 2) {
                            $query->whereOr("channel_id", $channel['id']);
                        }
                        if ($channel['listtype'] == 1 || $channel['listtype'] == 3) {
                            $query->whereOr('channel_id', 'in', function ($query) use ($channel) {
                                $query->name("cms_channel")->where('parent_id', $channel['id'])->field("id");
                            });
                        }
                        if ($channel['listtype'] == 0 || $channel['listtype'] == 4) {
                            $childrenIds = \addons\cms\model\Channel::getChannelChildrenIds($channel['id'], false);
                            if ($childrenIds) {
                                $query->whereOr('channel_id', 'in', $childrenIds);
                            }
                        }
                    })
                        ->whereOr("(`channel_ids`!='' AND FIND_IN_SET('{$channel['id']}', `channel_ids`))");
                }
            })
            ->where($filterPagelist)
            ->order($orderby, $orderway)
            ->paginate($pagesize, $config['pagemode'] == 'simple');

        $pageList->appends(array_filter($params));
        $this->view->assign("__FILTERLIST__", $filterList);
        $this->view->assign("__ORDERLIST__", $orderList);
        $this->view->assign("__PAGELIST__", $pageList);
        $this->view->assign("__SEARCHTERM__", $search);

        Config::set('cms.title', __("Search for %s", $search));

        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch($template));
        }
        return $this->view->fetch($template);
    }

    public function typeahead()
    {
        $search = $this->request->post("search", $this->request->post("q", ""));
        $search = mb_substr($search, 0, 100);

        $list = Archives
            ::where('status', 'normal')
            ->whereNull('deletetime')
            ->where('title', 'like', "%{$search}%")
            ->order('id', 'desc')
            ->field('id,title,diyname,channel_id,likes,dislikes,tags,createtime')
            ->limit(10)
            ->select();
        $result = collection($list)->toArray();
        $result[] = ['id' => 0, 'title' => __('Search more %s', $search), 'url' => addon_url("cms/search/index", [':search' => $search, 'search' => $search])];
        return json($result);
    }

    /**
     * Xunsearch搜索
     * @return string
     * @throws \think\Exception
     */
    public function xunsearch()
    {
        $orderList = [
            'relevance'       => '默认排序',
            'createtime_desc' => '发布时间从新到旧',
            'createtime_asc'  => '发布时间从旧到新',
            'views_desc'      => '浏览次数从多到少',
            'views_asc'       => '浏览次数从少到多',
            'comments_desc'   => '评论次数从多到少',
            'comments_asc'    => '评论次数从少到多',
        ];

        $q = $this->request->request('q', $this->request->request('search', ''));
        $q = strip_tags($q);
        $q = mb_substr($q, 0, 100);

        $page = $this->request->get('page/d', '1');
        $order = $this->request->get('order', '');
        $fulltext = $this->request->get('fulltext/d', '1');
        $fuzzy = $this->request->get('fuzzy/d', '0');
        $synonyms = $this->request->get('synonyms/d', '0');

        $order = isset($orderList[$order]) ? $order : 'relevance';

        $total_begin = microtime(true);
        $search = null;
        $pagesize = 10;
        $error = '';

        $result = FulltextSearch::search($q, $page, $pagesize, $order, $fulltext, $fuzzy, $synonyms);

        if (!$result) {
            $this->error('请检查Xunsearch配置');
        }

        // 计算总耗时
        $total_cost = microtime(true) - $total_begin;

        //获取热门搜索
        $hot = FulltextSearch::hot();

        $data = [
            'q'           => $q,
            'error'       => $error,
            'total'       => $result['total'] ?? 0,
            'count'       => $result['count'] ?? 0,
            'search_cost' => $result['microseconds'] ?? 0,
            'docs'        => $result['list'] ?? [],
            'pager'       => $result['pager'] ?? '',
            'corrected'   => $result['corrected'] ?? [],
            'highlight'   => $result['highlight'] ?? [],
            'related'     => $result['related'] ?? [],
            'search'      => $search,
            'fulltext'    => $fulltext,
            'synonyms'    => $synonyms,
            'fuzzy'       => $fuzzy,
            'order'       => $order,
            'orderList'   => $orderList,
            'hot'         => $hot,
            'total_cost'  => $total_cost,
        ];

        Config::set('cms.title', __("Search for %s", $q));
        $this->view->assign("title", $q);
        $this->view->assign($data);
        return $this->view->fetch('/xunsearch');
    }

    public function suggestion()
    {
        $q = trim($this->request->get('q', ''));
        $q = mb_substr($q, 0, 100);

        $terms = [];
        $config = get_addon_config('cms');
        if ($config['searchtype'] == 'xunsearch') {
            $terms = FulltextSearch::suggestion($q);
        } else {
            $terms = SearchLog::where("keywords", "LIKE", "{$q}%")->where("nums", ">", 0)->where("status", "normal")->column("keywords");
        }
        return json($terms);
    }
}
