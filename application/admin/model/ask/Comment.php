<?php

namespace app\admin\model\ask;

use addons\ask\library\Askdown;
use addons\ask\library\Service;
use addons\ask\model\User;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

class Comment extends Model
{
    use SoftDelete;
    // 表名
    protected $name = 'ask_comment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    // 追加属性
    protected $append = [
        'type_text',
        'deletetime_text',
        'status_text'
    ];
    protected static $config = [];

    //自定义初始化
    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        self::afterDelete(function ($row) use ($config) {
            $data = $row->withTrashed()->where('id', $row->id)->find();
            if ($data) {
                $model = Service::getModelByType($row['type'], $row['source_id']);
                if ($model) {
                    try {
                        $model->setDec("comments");
                    } catch (Exception $e) {
                    }
                }
                User::decrease('comments', 1, $row->user_id);
                //减少积分
                $config['score']['postcomment'] && \app\common\model\User::score(-$config['score']['postcomment'], $row->user_id, '删除评论');
            }
        });

        self::afterUpdate(function ($row) use ($config) {
            $changedData = $row->getChangedData();
            if (array_key_exists('deletetime', $changedData) && is_null($changedData['deletetime'])) {
                $model = Service::getModelByType($row['type'], $row['source_id']);
                if ($model) {
                    try {
                        $model->setInc("comments");
                    } catch (Exception $e) {
                    }
                }
                User::increase('comments', 1, $row->user_id);
                $config['score']['postcomment'] && \app\common\model\User::score($config['score']['postcomment'], $row->user_id, '恢复评论');
            }
        });
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }


    public function getTypeList()
    {
        return ['question' => __('Question'), 'article' => __('Article'), 'answer' => __('Answer')];
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


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deletetime']) ? $data['deletetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setDeletetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
