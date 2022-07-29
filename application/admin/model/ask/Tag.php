<?php

namespace app\admin\model\ask;

use addons\ask\model\Taggable;
use think\Model;
use traits\model\SoftDelete;

class Tag extends Model
{
    use SoftDelete;
    // 表名
    protected $name = 'ask_tag';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'flag_text',
        'deletetime_text',
        'status_text'
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        self::afterDelete(function ($row) {
            $data = $row->withTrashed()->where('id', $row->id)->find();
            if ($data) {

            } else {
                //删除标签
                $tagableList = Taggable::where('tag_id', $row->id)->select();
                foreach ($tagableList as $index => $item) {
                    $item->delete();
                }
            }
        });
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
        return $tags;
    }

    public function getFlagList()
    {
        return ['index' => __('Index'), 'recommend' => __('Recommend'), 'hot' => __('Hot')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deletetime']) ? $data['deletetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setDeletetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
