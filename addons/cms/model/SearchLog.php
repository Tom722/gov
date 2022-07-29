<?php

namespace addons\cms\model;

use think\Model;

/**
 * 搜索日志模型
 */
class SearchLog extends Model
{
    protected $name = "cms_search_log";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static function init()
    {
        static::$config = get_addon_config('cms');
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('cms/search/index', ['q' => $data['keywords']], static::$config['urlsuffix']);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('cms/search/index', ['q' => $data['keywords']], static::$config['urlsuffix'], true);
    }
}
