<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Db;
use think\Model;

/**
 * 标签模型
 */
class Tag extends Model
{
    protected $name = "cms_tag";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];

    protected static $config = [];

    protected static $tagCount = 0;

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function model()
    {
        return $this->belongsTo("Modelx");
    }

    public function getUrlAttr($value, $data)
    {
        return $this->buildUrl($value, $data);
    }

    public function getFullurlAttr($value, $data)
    {
        return $this->buildUrl($value, $data, true);
    }

    private function buildUrl($value, $data, $domain = false)
    {
        $diyname = isset($data['name']) && $data['name'] ? $data['name'] : $data['id'];
        $time = $data['createtime'] ?? time();

        $vars = [
            ':id'      => $data['id'],
            ':name'    => $diyname,
            ':diyname' => $diyname,
            ':year'    => date("Y", $time),
            ':month'   => date("m", $time),
            ':day'     => date("d", $time)
        ];
        $suffix = static::$config['moduleurlsuffix']['tag'] ?? static::$config['urlsuffix'];
        return addon_url('cms/tag/index', $vars, $suffix, $domain);
    }

    /**
     * 获取标签列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getTagList($params)
    {
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('taglist', $params);

        self::$tagCount++;

        $where = [];

        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");

        $tagModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('tpage' . self::$tagCount, $params);
            $list = $tagModel->paginate($listRows, $simple, $config);
        } else {
            $list = $tagModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        foreach ($list as $k => $v) {
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['name'] . '</a>';
        }
        return $list;
    }

    /**
     * 刷新标签
     * @param string $tags        标签,多个标签以,分隔
     * @param int    $archives_id 文档ID
     * @return bool
     */
    public static function refresh($tags, $archives_id)
    {
        $field = "nums";
        $tags = str_replace('，', ',', $tags);
        $tagsArr = explode(',', $tags);
        $tagsArr = array_unique(array_filter(array_map('strtolower', $tagsArr)));

        //取出所有标签列表
        $tagsList = Tag::alias('t')
            ->join('cms_taggable ta', 'ta.tag_id = t.id', 'RIGHT')
            ->where('ta.archives_id', $archives_id)
            ->field('t.*,ta.archives_id,ta.id AS taggable_id')
            ->select();
        foreach ($tagsList as $index => $item) {
            $item['name'] = strtolower($item['name']);
            if (!in_array($item['name'], $tagsArr)) {
                //统计减1
                isset($item[$field]) && $item->setDec($field);
                //删除标签
                Taggable::where('id', $item['taggable_id'])->delete();
            } else {
                $tagsArr = array_diff($tagsArr, [$item['name']]);
            }
        }
        if (!$tagsArr) {
            return true;
        }
        $insertTagIds = [];
        //取出剩余标签
        $tagList = self::where('name', 'in', $tagsArr)->select();
        foreach ($tagList as $index => $item) {
            $item['name'] = strtolower($item['name']);
            $item->setInc($field);
            $tagsArr = array_diff($tagsArr, [$item['name']]);
            $insertTagIds[] = $item['id'];
        }
        //剩余未插入的话题
        if ($tagsArr) {
            $originTagsArr = explode(',', $tags);
            foreach ($originTagsArr as $index => $item) {
                $name = strtolower($item);
                if (in_array($name, $tagsArr)) {
                    $tagsArr = array_diff($tagsArr, [$name]);
                    $object = self::create(['name' => $item, $field => 1]);
                    $id = $object->id;
                    $insertTagIds[] = $id;
                }
            }
        }
        //插入到taggable表
        $insertList = [];
        foreach ($insertTagIds as $index => $tag_id) {
            $insertList[] = ['tag_id' => $tag_id, 'archives_id' => $archives_id, 'createtime' => time()];
        }
        if ($insertList) {
            (new Taggable())->insertAll($insertList);
        }
        return true;
    }
}
