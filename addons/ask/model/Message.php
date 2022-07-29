<?php

namespace addons\ask\model;

use think\Model;

/**
 * 消息模型
 */
class Message Extends Model
{

    protected $name = "ask_message";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'create_date'
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        self::afterInsert(function ($row) {
            User::increase('messages', 1, $row->to_user_id);
        });
    }

    public function getCreateDateAttr($value, $data)
    {
        return time() - $data['createtime'] > 7 * 86400 ? date("Y-m-d", $data['createtime']) : human_date($data['createtime']);
    }

    public function from()
    {
        return $this->belongsTo('\app\common\model\User', 'from_user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

    public function to()
    {
        return $this->belongsTo('\app\common\model\User', 'to_user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

}
