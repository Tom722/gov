<?php

namespace addons\cms\model;

use think\Model;

/**
 * 会员余额日志模型
 */
class MoneyLog Extends Model
{

    // 表名
    protected $name = 'user_money_log';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    public function getCreatetimeAttr($value)
    {
       return date('Y-m-d H:i:s',$value);
    }

    public function getMoneyAttr($value){
        return $value>0?'+'.$value:$value;
    }
}
