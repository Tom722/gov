<?php

namespace addons\ask\controller;

use addons\ask\library\Converter;
use addons\ask\library\Service;
use addons\ask\model\Category;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\Response;

/**
 * Sitemap控制器
 * Class Api
 * @package addons\ask\controller
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

    public function _initialize()
    {
        parent::_initialize();
        Config::set('default_return_type', 'xml');
    }

    /**
     * Sitemap集合
     */
    public function index()
    {
        $list = [
            ['loc' => addon_url('ask/sitemap/questions', '', false, true),],
            ['loc' => addon_url('ask/sitemap/articles', '', false, true),],
            ['loc' => addon_url('ask/sitemap/tags', '', false, true),],
            ['loc' => addon_url('ask/sitemap/experts', '', false, true),]
        ];
        $this->options = [
            'item_key'  => '',
            'root_node' => 'sitemapindex',
            'item_node' => 'sitemap',
            'root_attr' => ''
        ];
        return xml($list, 200, [], $this->options);
    }

    /**
     * 问题
     */
    public function questions()
    {
        $questionList = \addons\ask\model\Question::where('status', '<>', 'hidden')->cache(3600)->field('id,createtime')->paginate(500000);
        $list = [];
        foreach ($questionList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.8
            ];
        }
        return xml($list, 200, [], $this->options);
    }

    /**
     * 文章
     */
    public function articles()
    {
        $articleList = \addons\ask\model\Article::where('status', '<>', 'hidden')->cache(3600)->field('id,createtime')->paginate(500000);
        $list = [];
        foreach ($articleList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.8
            ];
        }
        return xml($list, 200, [], $this->options);
    }

    /**
     * 话题
     */
    public function tags()
    {
        $tagList = \addons\ask\model\Tag::where('status', '<>', 'hidden')->cache(3600)->field('id,name,createtime')->paginate(500000);
        $list = [];
        foreach ($tagList as $index => $item) {
            $list[] = [
                'loc'      => $item->fullurl,
                'priority' => 0.6
            ];
        }
        return xml($list, 200, [], $this->options);
    }

    /**
     * 专家
     */
    public function experts()
    {
        $userList = \addons\ask\model\User::with(['basic'])->cache(3600)->where('isexpert', '1')->paginate(500000);
        $list = [];
        foreach ($userList as $index => $item) {
            $list[] = [
                'loc'      => $item->basic->url,
                'priority' => 0.6
            ];
        }
        return xml($list, 200, [], $this->options);
    }

}
