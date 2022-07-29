<?php

namespace addons\ask\model;

use app\common\library\Auth;
use think\Cache;
use think\Db;
use think\Model;

/**
 * 关注模型
 */
class Attention Extends Model
{

    protected $name = "ask_attention";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
        'show'
    ];
    protected static $config = [];

    public function getShowAttr(){
        return false;
    }
    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    public static function check($type, $source_id)
    {
        $user_id = Auth::instance()->id;
        if (!$user_id) {
            return null;
        }
        return self::where('type', $type)->where('source_id', $source_id)->where('user_id', $user_id)->find();
    }

    public static function render(&$list, $type)
    {
        $user_id = Auth::instance()->id;
        $ids = [];
        $values = [];
        if ($user_id) {
            if ($list instanceof Model) {
                $list->followed = self::where('user_id', $user_id)
                    ->where('type', $type)
                    ->where('source_id', 'in', $list->id)
                    ->find() ? true : false;
                return $list;
            }
            foreach ($list as $index => $item) {
                $ids[] = $item->id;
            }
            if ($ids) {
                $values = self::where('user_id', $user_id)->where('type', $type)->where('source_id', 'in', $ids)->column('source_id');
            }
        } else {
            if ($list instanceof Model) {
                $list->followed = false;
                return $list;
            }
        }
        foreach ($list as $index => &$item) {
            $item->followed = in_array($item->id, $values) ? true : false;
        }
        return $list;
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

    public function expert()
    {
        return $this->belongsTo('\app\common\model\User', 'source_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }

    public function question()
    {
        return $this->belongsTo('\addons\ask\model\Question', 'source_id', 'id')->field('reports,peeps,deletetime',true)->setEagerlyType(1);
    }

    public function answer()
    {
        return $this->belongsTo('\addons\ask\model\Answer', 'source_id', 'id')->field('sales,reports,shares,deletetime',true)->setEagerlyType(1);
    }


}
