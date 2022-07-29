<?php

namespace addons\cms\model;

use think\Model;

/**
 * 订单模型
 */
class Order extends Model
{
    protected $name = "cms_order";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    /**
     * 检测订单
     * @deprecated
     */
    public static function checkOrder($id)
    {
        return \addons\cms\library\Order::check($id);
    }

    /**
     * 提交订单
     * @deprecated
     */
    public static function submitOrder($id, $paytype = 'wechat', $openid = '', $method = 'web')
    {
        return \addons\cms\library\Order::submit($id, $paytype, $method, $openid);
    }

    public function archives()
    {
        return $this->belongsTo('Archives', 'archives_id', 'id');
    }
}
