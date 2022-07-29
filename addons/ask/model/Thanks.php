<?php

namespace addons\ask\model;

use addons\ask\library\Service;
use think\Exception;
use think\Model;

/**
 * 感谢模型
 */
class Thanks Extends Model
{

    protected $name = "ask_thanks";
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

    public static function notify($order)
    {
        $thanks = self::get($order->source_id);
        if ($thanks && $thanks['status'] == 'created') {
            $thanks->status = 'paid';
            $thanks->save();

            try {
                $model = Service::getModelByType($thanks['type'], $thanks->source_id);
                if ($model) {
                    $to_user_id = $thanks['type'] == 'user' ? $thanks['source_id'] : (isset($model['user_id']) ? $model['user_id'] : 0);
                    \app\common\model\User::money($thanks->money, $to_user_id, "收到打赏");
                    $model->setInc("thanks");
                    $title = isset($model['title']) ? $model['title'] : '';
                    $title = $title ? $title : ($thanks['type'] == 'answer' ? $model->question->title : "");
                    //更新动态
                    Feed::record($title, $thanks->content, 'thanks', $thanks['type'], $thanks->source_id, $thanks->user_id);
                    //发送通知
                    Notification::record($title, "打赏金额 {$thanks->money} 元", 'thanks', $thanks['type'], $thanks['source_id'], $to_user_id, $thanks->user_id);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            if (isset($model) && $model && isset($to_user_id) && $to_user_id) {
                $type = ($thanks['type'] == 'answer' ? "答案" : ($thanks['type'] == 'question' ? "问题" : "文章"));
                $fullurl = isset($model['fullurl']) ? $model['fullurl'] : '';
                $fullurl = $fullurl ? $fullurl : ($thanks['type'] == 'answer' ? $model->question->fullurl : "");
                Service::sendEmail($to_user_id, "你收到了一笔新的打赏！", ['content' => "你在《" . config('site.name') . "问答社区》发布的{$type}，有小伙伴表示了感谢，打赏了一笔金额，快来看看吧！", 'url' => $fullurl], 'thanks');
            }
        }
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

}
