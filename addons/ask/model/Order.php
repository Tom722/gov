<?php

namespace addons\ask\model;

use think\Model;

/**
 * 订单模型
 */
class Order Extends Model
{

    protected $name = "ask_order";
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
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public function getRemainSecondsAttr($value, $data)
    {
        return max(0, 1800 - (time() - $data['createtime']));
    }

    public function getUrlAttr($value, $data)
    {
        $url = '/';
        if ($data['type'] == 'answer') {
            $answer = Answer::get($data['source_id']);
            if ($answer) {
                $url = $answer->question->url;
            }
        } elseif ($data['type'] == 'thanks') {
            $thanks = Thanks::get($data['source_id']);
            if ($thanks) {
                $model = \addons\ask\library\Service::getModelByType($thanks['type'], $thanks['source_id']);
                $url = $thanks['type'] == 'answer' ? $model->question->url : $model->url;
            }
        } elseif ($data['type'] == 'article') {
            $article = Article::get($data['source_id']);
            if ($article) {
                $url = $article->url;
            }
        }
        return $url;
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

}
