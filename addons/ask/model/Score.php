<?php

namespace addons\ask\model;

use app\common\library\Auth;
use think\Model;
use traits\model\SoftDelete;

/**
 * 积分模型
 */
class Score Extends Model
{

    protected $name = "ask_score";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    protected $deleteTime = '';

    protected $auto = [];
    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    /**
     * 根据类型增加积分
     *
     * @param string $type    类型
     * @param null   $user_id 会员ID
     */
    public static function increase($type, $user_id = null)
    {
        $config = get_addon_config('ask');
        $typeArr = ['postanswer' => '发布答案', 'postquestion' => '发布问题', 'postarticle' => '发布文章', 'postcomment' => '发布评论', 'bestanswer' => '采纳最佳答案'];
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        //增加积分
        $score = $config['score'][$type] ?? 0;
        if ($score > 0) {
            $today = null;
            //判断是否超限制
            $scorecyclelimit = isset($config['scorecyclelimit']) ? $config['scorecyclelimit'] : [];
            $today = self::where(['user_id' => $user_id, 'type' => $type, 'date' => date("Ymd")])->find();
            if ($scorecyclelimit) {
                $scorecyclelimit = array_column($scorecyclelimit, null, 'type');
                if (isset($scorecyclelimit[$type])) {
                    $daylimit = $scorecyclelimit[$type]['day'];
                    $monthlimit = $scorecyclelimit[$type]['month'];
                    $yearlimit = $scorecyclelimit[$type]['year'];
                    if ($daylimit > 0) {
                        if ($today && $today['score'] + $score > $daylimit) {
                            return;
                        }
                    }
                    if ($monthlimit > 0) {
                        $thismonth = self::where(['user_id' => $user_id, 'type' => $type])->where('date', 'like', date("Ym") . "%")->sum('score');
                        if ($thismonth && $thismonth + $score > $monthlimit) {
                            return;
                        }
                    }
                    if ($yearlimit > 0) {
                        $thisyear = self::where(['user_id' => $user_id, 'type' => $type])->where('date', 'like', date("Y") . "%")->sum('score');
                        if ($thisyear && $thisyear + $score > $yearlimit) {
                            return;
                        }
                    }
                }
            }
            if ($today) {
                $today->setInc('score', $score);
            } else {
                self::create(['user_id' => $user_id, 'type' => $type, 'score' => $score, 'date' => date("Ymd")]);
            }
            \app\common\model\User::score($score, $user_id, $typeArr[$type] ?? $type);
        }
    }

    /**
     * 根据类型减少积分，减少不做上限判断
     *
     * @param string $type    类型
     * @param null   $user_id 会员ID
     */
    public static function decrease($type, $user_id = null)
    {
        $config = get_addon_config('ask');
        $typeArr = ['postanswer' => '删除答案', 'postquestion' => '删除问题', 'postarticle' => '删除文章', 'postcomment' => '删除评论', 'bestanswer' => '采纳最佳答案'];
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        //增加积分
        $score = $config['score'][$type] ?? 0;
        if ($score > 0) {
            \app\common\model\User::score(-$score, $user_id, $typeArr[$type] ?? $type);
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
