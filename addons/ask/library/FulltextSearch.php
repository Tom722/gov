<?php

namespace addons\ask\library;

use addons\xunsearch\library\Xunsearch;
use think\Config;
use think\Exception;
use think\Log;
use think\View;

class FulltextSearch
{

    /**
     * 获取配置
     * @return array
     */
    public static function config()
    {
        $data = [
            [
                'name'   => 'ask',
                'title'  => '问答',
                'fields' => [
                    ['name' => 'pid', 'type' => 'id', 'title' => '主键'],
                    ['name' => 'id', 'type' => 'numeric', 'title' => 'ID'],
                    ['name' => 'title', 'type' => 'title', 'title' => '标题'],
                    ['name' => 'content', 'type' => 'body', 'title' => '内容',],
                    ['name' => 'type', 'type' => 'string', 'title' => '类型', 'index' => 'self',],
                    ['name' => 'category_id', 'type' => 'numeric', 'title' => '分类ID', 'index' => 'self',],
                    ['name' => 'user_id', 'type' => 'numeric', 'title' => '会员ID', 'index' => 'self',],
                    ['name' => 'url', 'type' => 'string', 'title' => '链接',],
                    ['name' => 'price', 'type' => 'string', 'title' => '价格',],
                    ['name' => 'score', 'type' => 'string', 'title' => '积分',],
                    ['name' => 'collections', 'type' => 'numeric', 'title' => '收藏次数',],
                    ['name' => 'views', 'type' => 'numeric', 'title' => '浏览次数',],
                    ['name' => 'comments', 'type' => 'numeric', 'title' => '评论次数',],
                    ['name' => 'answers', 'type' => 'numeric', 'title' => '回答次数',],
                    ['name' => 'voteup', 'type' => 'numeric', 'title' => '点赞',],
                    ['name' => 'votedown', 'type' => 'numeric', 'title' => '点踩',],
                    ['name' => 'createtime', 'type' => 'numeric', 'title' => '发布时间',],
                ]
            ]
        ];
        return $data;
    }

    /**
     * 重置搜索索引数据库
     */
    public static function reset()
    {
        \addons\ask\model\Question::where('status', '<>', 'hidden')->chunk(100, function ($list) {
            foreach ($list as $item) {
                self::add($item);
            }
        });
        \addons\ask\model\Article::where('status', '<>', 'hidden')->chunk(100, function ($list) {
            foreach ($list as $item) {
                self::add($item);
            }
        });
        return true;
    }

    /**
     * 添加索引
     * @param $row
     */
    public static function add($row)
    {
        self::update($row, true);
    }

    /**
     * 更新索引
     * @param      $row
     * @param bool $add
     */
    public static function update($row, $add = false)
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            return;
        }
        $data = [];
        if ($row instanceof \addons\ask\model\Question || $row instanceof \addons\ask\model\Article) {
            $data['id'] = isset($row['id']) ? $row['id'] : 0;
            $data['title'] = isset($row['title']) ? $row['title'] : '';
            $data['category_id'] = isset($row['category_id']) ? $row['category_id'] : 0;
            $data['user_id'] = isset($row['user_id']) ? $row['user_id'] : 0;
            $data['content'] = isset($row['content']) ? $row['content'] : '';
            $data['price'] = isset($row['price']) ? $row['price'] : 0;
            $data['collections'] = isset($row['collections']) ? $row['collections'] : 0;
            $data['comments'] = isset($row['comments']) ? $row['comments'] : 0;
            $data['voteup'] = isset($row['voteup']) ? $row['voteup'] : 0;
            $data['votedown'] = isset($row['votedown']) ? $row['votedown'] : 0;
            $data['createtime'] = isset($row['createtime']) ? $row['createtime'] : 0;
            $data['views'] = isset($row['views']) ? $row['views'] : 0;
        }
        if ($row instanceof \addons\ask\model\Question) {
            $data['type'] = 'question';
            $data['url'] = $row->fullurl;
            $data['answers'] = isset($row['answers']) ? $row['answers'] : 0;
        } elseif ($row instanceof \addons\ask\model\Article) {
            $data['type'] = 'article';
            $data['url'] = $row->fullurl;
            $data['score'] = isset($row['score']) ? $row['score'] : 0;
        }
        if ($data) {
            $data['pid'] = substr($data['type'], 0, 1) . $data['id'];
            try {
                Xunsearch::instance('ask')->update($data, $add);
            } catch (\Exception $e) {
                Log::record($e->getMessage());
            }
        }
    }

    /**
     * 删除
     * @param $row
     */
    public static function del($row)
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            return;
        }
        $pid = null;
        if ($row instanceof \addons\ask\model\Question) {
            $pid = 'q' . $row->id;
        } elseif ($row instanceof \addons\ask\model\Article) {
            $pid = 'a' . $row->id;
        }
        if ($pid) {
            try {
                Xunsearch::instance('ask')->del($pid);
            } catch (\Exception $e) {
                Log::record($e->getMessage());
            }

        }
    }

    /**
     * 获取搜索结果
     * @return array
     */
    public static function search($q, $page = 1, $pagesize = 20, $order = '', $fulltext = true, $fuzzy = false, $synonyms = false)
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            return [];
        }
        return Xunsearch::instance('ask')->search($q, $page, $pagesize, $order, $fulltext, $fuzzy, $synonyms);
    }

    /**
     * 获取建议搜索关键字
     * @param string $q     关键字
     * @param int    $limit 返回条数
     * @return array
     */
    public static function suggestion($q, $limit = 10)
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            return [];
        }
        return Xunsearch::instance('ask')->suggestion($q, $limit);
    }

    /**
     * 获取搜索热门关键字
     * @return array
     * @throws \XSException
     */
    public static function hot()
    {
        $info = get_addon_info('xunsearch');
        if (!$info || !$info['state']) {
            return [];
        }
        return Xunsearch::instance('ask')->getXS()->search->getHotQuery();
    }

}
