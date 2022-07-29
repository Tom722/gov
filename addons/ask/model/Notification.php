<?php

namespace addons\ask\model;

use app\common\library\Auth;
use think\Cache;
use think\Db;
use think\Model;

/**
 * 通知模型
 */
class Notification Extends Model
{

    protected $name = "ask_notification";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
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
        self::afterInsert(function ($row) {
            User::increase('notifications', 1, $row->to_user_id);
        });
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
        } else if ($data['type'] == 'answer') {
            $answer = Answer::get($data['source_id']);
            if ($answer) {
                $url = addon_url('ask/question/show', [':id' => $answer['question_id']], static::$config['urlsuffix']);
            }
        } else if ($data['type'] == 'article') {
            $url = addon_url('ask/article/show', [':id' => $data['source_id']], static::$config['urlsuffix']);
        }
        return $url;
    }

    /**
     * 记录通知信息
     * @param string $title
     * @param string $content
     * @param string $action
     * @param string $type
     * @param int $source_id
     * @param int $to_user_id
     * @param int $from_user_id
     */
    public static function record($title, $content, $action, $type, $source_id, $to_user_id, $from_user_id = null)
    {
        $from_user_id = is_null($from_user_id) ? Auth::instance()->id : $from_user_id;
        $from_user_id = $from_user_id ? $from_user_id : 0;
        if ($from_user_id == $to_user_id) {
            return;
        }
        $data = [
            'title'        => $title,
            'content'      => $content,
            'action'       => $action,
            'type'         => $type,
            'source_id'    => $source_id,
            'from_user_id' => $from_user_id,
            'to_user_id'   => $to_user_id,
        ];
        self::create($data);
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
