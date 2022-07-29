<?php

namespace app\admin\model\ask;

use think\Db;
use think\Model;

class User extends Model
{
    // 表名
    protected $name = 'ask_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'flag_text'
    ];

    public static function init()
    {
        self::afterWrite(function ($row) {
            $changedData = $row->getChangedData();
            if (isset($changedData['experttitle'])) {
                Db::name("user")->where('id', $row['user_id'])->update(['title' => $row['isexpert'] ? $row['experttitle'] : '']);
            }
        });
    }

    public function getFlagList()
    {
        return ['index' => __('Index'), 'recommend' => __('Recommend'), 'new' => __('New')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    public function basic()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function category()
    {
        return $this->belongsTo("Category", 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
