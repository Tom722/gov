<?php

namespace app\admin\model\ask;

use addons\ask\library\Askdown;
use addons\ask\library\Service;
use addons\ask\model\User;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

class Answer extends Model
{
    use SoftDelete;
    // 表名
    protected $name = 'ask_answer';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    // 追加属性
    protected $append = [
        'question_url',
        'deletetime_text',
        'adopttime_text',
        'status_text'
    ];

    protected static $config;

    public static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        self::afterDelete(function ($row) use ($config) {
            $data = $row->withTrashed()->where('id', $row->id)->find();
            if ($data) {
                $commentList = Comment::where('type', 'answer')->where('source_id', $row['id'])->select();
                foreach ($commentList as $index => $item) {
                    $item->delete();
                }
                $model = Service::getModelByType('question', $row['question_id']);
                if ($model) {
                    try {
                        $model->setDec("answers");
                    } catch (Exception $e) {

                    }
                }
                User::decrease('answers', 1, $row->user_id);
                $config['score']['postanswer'] && \app\common\model\User::score(-$config['score']['postanswer'], $row->user_id, '删除答案');
            } else {
                //永久删除
                $commentList = Comment::where('type', 'answer')->where('source_id', $row['id'])->select();
                foreach ($commentList as $index => $item) {
                    $item->delete(true);
                }
            }
        });

        self::afterUpdate(function ($row) use ($config) {
            $changedData = $row->getChangedData();
            if (array_key_exists('deletetime', $changedData) && is_null($changedData['deletetime'])) {
                $model = Service::getModelByType('question', $row['question_id']);
                if ($model) {
                    try {
                        $model->setInc("answers");
                    } catch (Exception $e) {
                        echo $e->getMessage();
                    }
                }
                User::increase('answers', 1, $row->user_id);
                $config['score']['postanswer'] && \app\common\model\User::score($config['score']['postanswer'], $row->user_id, '恢复答案');
            }
        });

        parent::init();
    }

    public function getQuestionUrlAttr($value, $data)
    {
        return addon_url('ask/question/show', [':id' => $data['question_id']], static::$config['urlsuffix']);
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'closed' => __('Closed')];
    }


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deletetime']) ? $data['deletetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getAdopttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['adopttime']) ? $data['adopttime'] : '');
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

    protected function setAdopttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function question()
    {
        return $this->belongsTo("Question", 'question_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
