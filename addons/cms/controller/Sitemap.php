<?php

namespace addons\cms\controller;

use think\Config;
use think\Response;

/**
 * Sitemap控制器
 * Class Sitemap
 * @package addons\cms\controller
 */
class Sitemap extends Base
{
    protected $noNeedLogin = ['*'];
    protected $options = [
        'item_key'  => '',
        'root_node' => 'urlset',
        'item_node' => 'url',
        'root_attr' => 'xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/"'
    ];
    //默认配置
    protected $config = [
        'pagesize' => 5000,
        'cache'    => 3600
    ];

    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('cms');
        $this->config['pagesize'] = $config['sitemappagesize'] ?? 5000;
        $this->config['cache'] = $config['sitemapcachelifetime'] ?? 3600;
        $this->config['cache'] = intval($this->config['cache']) < 0 ? false : $this->config['cache'];
        Config::set('default_return_type', 'xml');
    }

    /**
     * Sitemap集合
     */
    public function index()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $type = $this->request->param('type', '');
        $type = str_replace('.xml', '', $type);
        $type = $type === 'all' ? '' : $type;
        $list = [];
        $pagesizeStr = $pagesize != $this->config['pagesize'] ? "pagesize/{$pagesize}/" : '';
        if (!$type || $type == 'channel') {
            $path = "/addons/cms/sitemap/channels/{$pagesizeStr}page/[PAGE]";
            $channelsList = \addons\cms\model\Channel::where('status', 'normal')->field('id,name,diyname,createtime')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $channelsList->lastPage();
            foreach ($channelsList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'archives') {
            $path = "/addons/cms/sitemap/archives/{$pagesizeStr}page/[PAGE]";
            $archivesList = \addons\cms\model\Archives::with(['channel'])->where('status', 'normal')->field('id,channel_id,diyname,createtime,publishtime')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $archivesList->lastPage();
            foreach ($archivesList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'tags') {
            $path = "/addons/cms/sitemap/tags/{$pagesizeStr}page/[PAGE]";
            $tagsList = \addons\cms\model\Tag::where('status', 'normal')->field('id,name')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $tagsList->lastPage();
            foreach ($tagsList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'users') {
            $path = "/addons/cms/sitemap/users/{$pagesizeStr}page/[PAGE]";
            $usersList = \addons\cms\model\User::where('status', 'normal')->field('id')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $usersList->lastPage();
            foreach ($usersList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'specials') {
            $path = "/addons/cms/sitemap/specials/{$pagesizeStr}page/[PAGE]";
            $usersList = \addons\cms\model\Special::where('status', 'normal')->field('id')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $usersList->lastPage();
            foreach ($usersList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'pages') {
            $path = "/addons/cms/sitemap/pages/{$pagesizeStr}page/[PAGE]";
            $usersList = \addons\cms\model\Page::where('status', 'normal')->field('id')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $usersList->lastPage();
            foreach ($usersList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        if (!$type || $type == 'diyforms') {
            $path = "/addons/cms/sitemap/diyforms/{$pagesizeStr}page/[PAGE]";
            $usersList = \addons\cms\model\Diyform::where('status', 'normal')->field('id')->paginate($pagesize, false, ['path' => $path]);
            $lastPage = $usersList->lastPage();
            foreach ($usersList->getUrlRange(1, $lastPage) as $index => $item) {
                $list[] = ['loc' => url($item, '', 'xml', true)];
            }
        }
        $this->options = [
            'item_key'  => '',
            'root_node' => 'sitemapindex',
            'item_node' => 'sitemap',
            'root_attr' => ''
        ];
        return $this->xml($list);
    }

    /**
     * 栏目
     */
    public function channels()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $archivesList = \addons\cms\model\Channel::where('status', 'normal')->cache($this->config['cache'])->field('id,name,diyname,createtime')->paginate($pagesize, true);
        $list = [];
        foreach ($archivesList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 文章
     */
    public function archives()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $archivesList = \addons\cms\model\Archives::with(['channel'])->where('status', 'normal')->cache($this->config['cache'])->field('id,channel_id,diyname,createtime')->paginate($pagesize, true);
        $list = [];
        foreach ($archivesList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.8
            ];
        }
        return $this->xml($list);
    }

    /**
     * 标签
     */
    public function tags()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $tagsList = \addons\cms\model\Tag::where('status', 'normal')->cache($this->config['cache'])->field('id,name')->paginate($pagesize, true);
        $list = [];
        foreach ($tagsList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 用户
     */
    public function users()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $userList = \addons\cms\model\User::where('status', 'normal')->cache($this->config['cache'])->field('id')->paginate($pagesize, true);
        $list = [];
        foreach ($userList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 专题
     */
    public function specials()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $specialList = \addons\cms\model\Special::where('status', 'normal')->cache($this->config['cache'])->field('id,diyname,createtime')->paginate($pagesize, true);
        $list = [];
        foreach ($specialList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 单页
     */
    public function pages()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $specialList = \addons\cms\model\Page::where('status', 'normal')->cache($this->config['cache'])->field('id,diyname,createtime')->paginate($pagesize, true);
        $list = [];
        foreach ($specialList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 自定义表单
     */
    public function diyforms()
    {
        $pagesize = $this->request->param('pagesize/d', $this->config['pagesize']);
        $specialList = \addons\cms\model\Diyform::where('status', 'normal')->cache($this->config['cache'])->field('id,diyname,createtime')->paginate($pagesize, true);
        $list = [];
        foreach ($specialList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return $this->xml($list);
    }

    /**
     * 输出XML
     */
    protected function xml($data = [])
    {
        foreach ($data as $index => &$item) {
            $item['loc'] = htmlspecialchars($item['loc']);
        }
        return Response::create($data, 'xml', 200, [], $this->options);
    }
}
