<?php

namespace addons\ask\model;

use think\Model;

/**
 * 话题模型
 */
class Taggable Extends Model
{

    protected $name = "ask_taggable";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        self::afterDelete(function ($row) {
            $field = "{$row['type']}s";
            $tag = \app\admin\model\ask\Tag::get($row['tag_id']);
            if ($tag && isset($tag[$field])) {
                $tag->setDec($field);
            }
        });
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_tag_image'];
        return cdnurl($value, true);
    }

    public function getIconAttr($value, $data)
    {
        $value = $value ? cdnurl($value, true) : '';
        return $value;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/tag/show', [':id' => $data['id'], ':name' => $data['name']], static::$config['urlsuffix']);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('ask/tag/show', [':id' => $data['id'], ':name' => $data['name']], static::$config['urlsuffix'], true);
    }

}
