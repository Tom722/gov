<?php

namespace addons\cms\model;

use think\Db;
use think\Model;

/**
 * 标签模型
 */
class Taggable extends Model
{
    protected $name = "cms_taggable";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];

    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function model()
    {
        return $this->belongsTo("Modelx");
    }

}
