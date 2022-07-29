<?php

namespace app\admin\model\ask;

use think\Model;

class Zone extends Model
{

    // 表名
    protected $name = 'ask_zone';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];

    protected static function init()
    {
        $refreshTags = function ($row) {
            $tags = input('post.tags');
            Tag::where('zone_id', $row['id'])->update(['zone_id' => 0]);
            Tag::where('id', 'in', $tags)->update(['zone_id' => $row['id']]);
        };
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
        self::beforeUpdate(function ($row) {
            $row['updatetime'] = time();
        });
        self::afterInsert($refreshTags);
        self::afterUpdate($refreshTags);
    }

    public function getTagsAttr($value, $data)
    {
        $tags = Tag::where('zone_id', $data['id'])->column('id');
        return implode(',', $tags);
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
