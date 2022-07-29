<?php

namespace addons\ask\model;

use addons\ask\library\Service;
use think\Model;
use traits\model\SoftDelete;

/**
 * 话题模型
 */
class Tag Extends Model
{

    protected $name = "ask_tag";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    // 追加属性
    protected $append = [
        'url',
        'fullurl',
    ];
    protected static $config = [];
    use SoftDelete;

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_tag_image'];
        return cdnurl($value, true);
    }

    public function getIconAttr($value, $data)
    {
        $value = $value ? cdnurl($value, true) : '';
        return $value;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/tag/show', [':id' => $data['id'], ':name' => $data['name']], static::$config['urlsuffix']);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('ask/tag/show', [':id' => $data['id'], ':name' => $data['name']], static::$config['urlsuffix'], true);
    }

    public function getFollowedAttr($value, $data)
    {
        if (isset($this->data['followed'])) {
            return $this->data['followed'];
        }
        $this->data['followed'] = Attention::check('tag', $data['id']) ? true : false;
        return $this->data['followed'];
    }

    /**
     * 获取话题集合
     * @param string $type
     * @param int    $source_id
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getTags($type, $source_id)
    {
        $tags = Tag::alias('t')
            ->join('ask_taggable ta', 'ta.tag_id = t.id', 'RIGHT')
            ->where('ta.source_id', $source_id)
            ->where('ta.type', '=', $type)
            ->field('t.*,ta.source_id,ta.id AS taggable_id')
            ->select();
            
        foreach($tags as $item){
            $item->hidden(['reports','deletetime']);
        }
        return $tags;
    }

    /**
     * 获取话题列表
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
        $cache = !isset($params['cache']) ? true : (int)$params['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        $order = $orderby == 'rand'
            ? 'rand()'
            : (in_array($orderby, ['id', 'questions', 'articles', 'followers', 'createtime', 'updatetime'])
                ? "{$orderby} {$orderway}"
                : "id {$orderway}");

        $list = self::where($where)
            ->where($condition)
            ->field($field)
            ->order($order)
            ->limit($limit)
            ->cache($cache)
            ->select();
        return $list;
    }

    public static function render(&$list, $type = null)
    {
        $ids = [];
        foreach ($list as $index => &$item) {
            $item->data('tags', []);
            $ids[] = $item['id'];
        }
        unset($item);
        if ($ids) {
            $tagList = Taggable::alias('ta')
                ->join('ask_tag t', 'ta.tag_id = t.id', 'LEFT')
                ->where('source_id', 'in', $ids)
                ->where('type', '=', $type)
                ->whereNull('t.deletetime')
                ->field('t.*,ta.source_id')
                ->select();
            foreach ($tagList as $index => &$item) {
                $index = array_search($item['source_id'], $ids);
                $item->hidden(['reports','deletetime']);
                if (isset($list[$index]) && is_object($list[$index])) {
                    $list[$index]->setTagData($item);
                }
            }
        }
    }

    /**
     * 刷新标签
     * @param string $tags      标签
     * @param string $type      类型
     * @param string $source_id 资源ID
     * @return bool
     */
    public static function refresh($tags, $type, $source_id)
    {
        $model = Service::getModelByType($type, $source_id);
        if (!$model) {
            return false;
        }
        $field = "{$type}s";
        $tags = str_replace('，', ',', $tags);
        $tagsArr = explode(',', $tags);
        $tagsArr = array_unique(array_filter(array_map('strtolower', $tagsArr)));

        //取出所有标签列表
        $tagsList = Tag::withTrashed()->alias('t')
            ->join('ask_taggable ta', 'ta.tag_id = t.id', 'RIGHT')
            ->where('ta.source_id', $source_id)
            ->where('ta.type', '=', $type)
            ->field('t.*,ta.source_id,ta.id AS taggable_id')
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
        $tagList = self::withTrashed()->where('name', 'in', $tagsArr)->select();
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
            $insertList[] = ['tag_id' => $tag_id, 'source_id' => $source_id, 'type' => $type, 'createtime' => time()];
        }
        if ($insertList) {
            (new Taggable())->insertAll($insertList);
        }
        return true;
    }

}
