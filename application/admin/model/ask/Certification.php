<?php

namespace app\admin\model\ask;

use addons\ask\library\Service;
use think\Model;

class Certification extends Model
{
    // 表名
    protected $name = 'ask_certification';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    // 追加属性
    protected $append = [
        'handletime_text',
        'status_text'
    ];

    public static function init()
    {
        self::beforeUpdate(function ($row) {
            $changedData = $row->getChangedData();
            if (isset($changedData['status'])) {
                $row->handletime = time();
            }
        });
        self::afterUpdate(function ($row) {           
            $changedData = $row->getChangedData();
            if (isset($changedData['status'])) {
                $user = User::get($row->user_id);
                if ($user) {
                    if ($changedData['status'] == 'agreed') {                        
                        $user->save(['isexpert' => 1,'category_id'=>$row->category_id]);
                        $user = \app\common\model\User::get($row->user_id);
                        if ($user) {
                            $user->save(['title' => '认证专家']);
                            //邮件通知
                            Service::sendEmail($row->user_id, "你的专家认证已通过！", "你在《" . config('site.name') . "问答社区》申请的专家认证已经通过审核。", 'ceritification');
                        }
                    } else if ($changedData['status'] == 'rejected') {
                        $user = \app\common\model\User::get($row->user_id);
                        if ($user) {
                            $user->save(['title' => '']);
                        }
                        //邮件通知
                        Service::sendEmail($row->user_id, "你的专家认证被拒绝！", "你在《" . config('site.name') . "问答社区》申请的专家认证没有通过审核，原因：{$row->memo}", 'ceritification');
                    }
                }
            }
        });
        parent::init();
    }

    public function getStatusList()
    {
        return ['hidden' => __('Hidden'), 'rejected' => __('Rejected'), 'agreed' => __('Agreed')];
    }

    public function getHandletimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['handletime']) ? $data['handletime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setHandletimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function category()
    {
        return $this->belongsTo("Category", 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
