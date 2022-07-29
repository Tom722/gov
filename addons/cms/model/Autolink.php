<?php

namespace addons\cms\model;

use think\Db;
use think\Model;

/**
 * 自动链接模型
 */
class Autolink extends Model
{
    protected $name = "cms_autolink";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

}
