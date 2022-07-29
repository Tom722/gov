<?php

namespace addons\ask\model;

use think\Model;

/**
 * 分类模型
 */
class Category Extends Model
{

    protected $name = "ask_category";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_category_image'];
        return cdnurl($value);
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/category/show', [':id' => $data['id']], static::$config['urlsuffix']);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('ask/category/show', [':id' => $data['id']], static::$config['urlsuffix'], true);
    }

    public static function getIndexCategoryList($type, $pid = null)
    {
        return self::where('type', $type)
            ->where('status', 'normal')
            ->where(function ($query) use ($pid) {
                if ($pid) {
                    $query->where('pid', $pid);
                }
            })
            ->order('weigh DESC,id DESC')
            ->select();
    }

}
