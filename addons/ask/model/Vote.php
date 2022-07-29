<?php

namespace addons\ask\model;

use app\common\library\Auth;
use think\Model;

/**
 * 投票模型
 */
class Vote Extends Model
{

    protected $name = "ask_vote";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public static function render(&$list, $type)
    {
        $user_id = Auth::instance()->id;
        $ids = [];
        $values = [];
        if ($user_id) {
            if ($list instanceof Model) {
                $list->vote = self::where('user_id', $user_id)
                    ->where('type', $type)
                    ->where('source_id', 'in', $list->id)
                    ->value('value');
                return $list;
            }
            foreach ($list as $index => $item) {
                $ids[] = $item->id;
            }
            if ($ids) {
                $values = self::where('user_id', $user_id)->where('type', $type)->where('source_id', 'in', $ids)->column('source_id,value');
            }
        } else {
            if ($list instanceof Model) {
                $list->vote = '';
                return $list;
            }
        }
        foreach ($list as $index => &$item) {
            $item->vote = isset($values[$item->id]) ? $values[$item->id] : '';
        }
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

    /**
     * 关联问题模型
     */
    public function question()
    {
        return $this->belongsTo('\app\common\model\Question', 'question_id', 'id')->field('reports,peeps,deletetime',true)->setEagerlyType(1);
    }

}
