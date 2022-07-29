<?php

namespace app\admin\model\ask;

use addons\ask\library\Service;
use think\Model;

class Report extends Model
{
    // 表名
    protected $name = 'ask_report';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'type_text',
        'reason_text',
    ];

    public function getTypeList()
    {
        return ['question' => __('Question'), 'article' => __('Article'), 'answer' => __('Answer'), 'tag' => __('Tag'), 'comment' => __('Comment')];
    }

    public function getReasonList()
    {
        $config = get_addon_config('ask');
        return $config['reportreasonlist'];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getReasonTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['reason']) ? $data['reason'] : '');
        $list = $this->getReasonList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
