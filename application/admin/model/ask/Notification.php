<?php

namespace app\admin\model\ask;

use think\Model;

class Notification extends Model
{
    // 表名
    protected $name = 'ask_notification';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [

    ];

    public function fromuser()
    {
        return $this->belongsTo("\app\common\model\User", 'from_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function touser()
    {
        return $this->belongsTo("\app\common\model\User", 'to_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
