<?php

namespace addons\ask\model;

use think\Model;

/**
 * 动态模型
 */
class Feed Extends Model
{

    protected $name = "ask_feed";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
        'url',
        'create_date'
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public function getCreateDateAttr($value, $data)
    {
        return time() - $data['createtime'] > 7 * 86400 ? date("Y-m-d", $data['createtime']) : human_date($data['createtime']);
    }

    public function getUrlAttr($value, $data)
    {
        $url = 'javascript:';
        if ($data['type'] == 'question') {
            $url = addon_url('ask/question/show', [':id' => $data['source_id']], static::$config['urlsuffix']);
        } else if ($data['type'] == 'article') {
            $url = addon_url('ask/article/show', [':id' => $data['source_id']], static::$config['urlsuffix']);
        } else if ($data['type'] == 'answer') {
            $answer = Answer::get($data['source_id']);
            if ($answer) {
                $url = addon_url('ask/question/show', [':id' => $answer['question_id']], static::$config['urlsuffix']);
            }
        }
        return $url;
    }

    /**
     * 记录动态信息
     * @param string $title
     * @param string $content
     * @param string $action
     * @param string $type
     * @param int    $source_id
     * @param int    $user_id
     */
    public static function record($title, $content, $action, $type, $source_id, $user_id)
    {
        $data = [
            'title'     => $title,
            'content'   => $content,
            'action'    => $action,
            'type'      => $type,
            'source_id' => $source_id,
            'user_id'   => $user_id,
        ];
        self::create($data);
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

}
