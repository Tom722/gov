<?php

namespace addons\cms\model;

use think\Model;

/**
 * 收藏模型
 */
class Collection extends Model
{
    protected $name = "cms_collection";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'create_date',
    ];
    protected static $config = [];

    public function getCreateDateAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['createtime']) ? $data['createtime'] : '');
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 关联模型
     */
    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

}
