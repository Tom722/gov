<?php

namespace app\admin\model\ask;

use think\Model;

class Order extends Model
{
    // 表名
    protected $name = 'ask_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'type_text',
        'paytime_text',
        'status_text'
    ];

    public static function init()
    {
        self::afterUpdate(function ($row) {
            $changedData = $row->getChangedData();
            if (isset($changedData['status']) && $changedData['status'] == 'paid') {
                //先将订单设置为未处理
                $row->where('id', $row->id)->update(['status' => 'created']);
                //处理订单
                \addons\ask\library\Order::settle($row->orderid, $row->amount);
            }
        });
        parent::init();
    }

    public function getTypeList()
    {
        return ['question' => __('Question'), 'article' => __('Article'), 'answer' => __('Answer'), 'thanks' => __('Thanks')];
    }

    public function getStatusList()
    {
        return ['created' => __('Created'), 'paid' => __('Paid')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytime']) ? $data['paytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPaytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
