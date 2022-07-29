<?php

namespace addons\ask\controller;

use addons\ask\library\FulltextSearch;

/**
 * 搜索控制器
 * Class Search
 * @package addons\ask\controller
 */
class Search extends Base
{

    protected $layout = 'default';

    public function index()
    {
        $config = get_addon_config('ask');
        if ($config['searchtype'] == 'xunsearch') {
            return $this->xunsearch();
        }
        $module = $this->request->request("module", "question");
        $type = $this->request->request("type", "new");
        $keyword = trim($this->request->get('q', $this->request->get('keyword', '')));

        if ($module == 'question') {
            $searchList = \addons\ask\model\Question::getIndexQuestionList($type, null, null, null, $keyword);
            $this->view->assign("questionList", $searchList);
        } else {
            $searchList = \addons\ask\model\Article::getIndexArticleList($type, null, null, null, $keyword);
            $this->view->assign("articleList", $searchList);
        }

        $this->view->assign("title", $keyword);
        $this->view->assign("module", $module);
        $this->view->assign("keyword", $keyword);
        $this->view->assign("type", $type);
        $this->view->assign("searchList", $searchList);

        if ($this->request->isAjax()) {
            return $this->view->fetch('ajax/get_' . $module . '_list');
        }
        return $this->view->fetch();
    }

    /**
     * Xunsearch搜索
     * @return string
     * @throws \think\Exception
     */
    public function xunsearch()
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            $this->error("请安装Xunsearch全文搜索插件并启用后再尝试");
        }
        $orderList = [
            'relevance'       => '默认排序',
            'createtime_desc' => '发布时间从新到旧',
            'createtime_asc'  => '发布时间从旧到新',
            'views_desc'      => '浏览次数从多到少',
            'views_asc'       => '浏览次数从少到多',
            'comments_desc'   => '评论次数从多到少',
            'comments_asc'    => '评论次数从少到多',
        ];

        $q = trim($this->request->get('q', $this->request->get('keyword', '')));
        $page = $this->request->get('page/d', '1');
        $order = $this->request->get('order', '');
        $fulltext = $this->request->get('fulltext/d', '1');
        $fuzzy = $this->request->get('fuzzy/d', '0');
        $synonyms = $this->request->get('synonyms/d', '0');

        $order = isset($orderList[$order]) ? $order : 'relevance';

        $total_begin = microtime(true);
        $search = null;
        $pagesize = 10;

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
            'error'       => '',
            'total'       => $result['total'],
            'count'       => $result['count'],
            'search_cost' => $result['microseconds'],
            'docs'        => $result['list'],
            'pager'       => $result['pager'],
            'corrected'   => $result['corrected'],
            'highlight'   => $result['highlight'],
            'related'     => $result['related'],
            'search'      => $search,
            'fulltext'    => $fulltext,
            'synonyms'    => $synonyms,
            'fuzzy'       => $fuzzy,
            'order'       => $order,
            'orderList'   => $orderList,
            'hot'         => $hot,
            'total_cost'  => $total_cost,
        ];

        $this->view->assign("title", $q);
        $this->view->assign($data);
        return $this->view->fetch('/search/xunsearch');
    }

    public function suggestion()
    {
        $q = $this->request->get('q', '', 'trim');
        $terms = FulltextSearch::suggestion($q);
        return json($terms);
    }

}
