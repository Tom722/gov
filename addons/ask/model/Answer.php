<?php

namespace addons\ask\model;

use addons\ask\library\Askdown;
use addons\ask\library\FulltextSearch;
use addons\ask\library\Service;
use app\common\library\Auth;
use think\Db;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

/**
 * 回答模型
 */
class Answer Extends Model
{

    protected $name = "ask_answer";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];
    // 追加属性
    protected $append = [
        'create_date'
    ];
    protected static $config = [];

    use SoftDelete;

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
        //发送@消息
        self::afterInsert(function ($row) use ($config) {
            Askdown::instance()->notification('answer', $row->id);
            User::increase('answers', 1, $row->user_id);
            $config = get_addon_config('ask');
            //自己回答不增加积分
            if ($row->user_id != $row->question->user_id) {
                Score::increase('postanswer', $row->user_id);
            }
        });
        self::afterDelete(function ($row) use ($config) {
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
            //自己回答不减少积分
            if ($model && $row->user_id != $model->user_id) {
                Score::decrease('postanswer', $row->user_id);
            }
            //删除动态
            Feed::where('user_id', $row->user_id)->where('action', 'post_answer')->delete();
        });
        self::afterUpdate(function ($row) {
            $changedData = $row->getChangedData();
            if (array_key_exists('adopttime', $changedData)) {
                if ($changedData['adopttime']) {
                    User::increase('adoptions', 1, $row->user_id);
                } else {
                    User::decrease('adoptions', 1, $row->user_id);
                }
            }
            //更新搜索
            FulltextSearch::update($row->question_id);
        });
    }

    public function getCreateDateAttr($value, $data)
    {
        return time() - $data['createtime'] > 7 * 86400 ? date("Y-m-d", $data['createtime']) : human_date($data['createtime']);
    }

    public function getContentOutputAttr($value, $data)
    {
        return str_replace(["<", ">"], ["&lt;", "&gt;"], $data['content']);
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }

    public function getCurrencyAttr($value, $data)
    {
        if (isset($this->data['currency'])) {
            return $this->data['currency'];
        }
        if ($data['price'] > 0) {
            $currency = 'money';
        } elseif ($data['score'] > 0) {
            $currency = 'score';
        } else {
            $config = get_addon_config('ask');
            $currency = $config['peeptype'] == 'score' ? 'score' : 'money';
        }

        return $currency;
    }

    public function getPeepDaysAttr($value, $data)
    {
        $days = abs(self::$config['adoptdays'] - intval((time() - $data['createtime']) / 86400));
        $days = $days <= 0 ? 1 : $days;
        return $days;
    }

    //获取付费查看状态
    public function getPeepStatus($question)
    {
        if (isset($this->data['peep_status'])) {
            return $this->data['peep_status'];
        }
        $user_id = Auth::instance()->id;

        //如果金额为0、提问者、回答者本人、管理员 均可查看
        if ($question->user_id == $user_id || ($this->data['price'] == 0 && $this->data['score'] == 0) || $this->data['user_id'] == $user_id || Service::isAdmin()) {
            return 'noneed';
        } else {
            if ($question->best_answer_id) {
                if ($this->data['id'] == $question->best_answer_id) {
                    //判断是否在有效期内采纳
                    if ($this->data['adopttime'] - ($question['rewardtime'] ? $question['rewardtime'] : $question['createtime']) < self::$config['adoptdays'] * 86400) {
                        $paid = \addons\ask\library\Order::check('answer', $this->data['id']);
                        return $paid ? 'paid' : 'unpaid';
                    } else {
                        //如果在有效期外采纳，则不再需要付费
                        return 'noneed';
                    }
                } else {
                    //未采纳
                    return 'unadopted';
                }
            } else {
                if (time() - $this->data['createtime'] > self::$config['adoptdays'] * 86400) {
                    return 'expired';
                } else {
                    return 'waiting';
                }
            }
        }
    }

    /**
     * 获取回答列表
     * @param int    $question_id
     * @param null   $user_id
     * @param string $order
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public static function getAnswerList($question_id, $user_id = null, $order = 'default')
    {
        $question = Question::get($question_id);
        if (!$question) {
            return [];
        }
        $answerList = self::with('user')
            ->where('question_id', $question['id'])
            ->where(function ($query) use ($user_id) {
                if (!is_null($user_id)) {
                    $query->where('user_id', $user_id);
                }
            })
            ->where('status', 'normal')
            ->whereNotIn('id', $question['best_answer_id'])
            ->field(Db::raw("id,pid,user_id,question_id,reply_user_id,price,score,content,content_fmt,voteup,votedown,thanks,comments,collections,createtime,updatetime,adopttime,status,(1.*`voteup`-`votedown`) AS vote"))
            ->order($order == 'default' ? 'vote DESC, id ASC' : 'createtime DESC')
            ->paginate(self::$config['pagesize']['answer']);

        Collection::render($answerList, 'answer');
        Vote::render($answerList, 'answer');
        return $answerList;
    }

    // 获取答案的回复列表
    public function getCommentListAttr($value, $data)
    {
        $config = get_addon_config('ask');
        //评论列表
        $commentList = Comment::with(['user', 'replyuser'])
            ->where('type', 'answer')
            ->where('source_id', $data['id'])
            ->field('shares,reports,deletetime', true)
            ->cache($data['comments'] >= $config['pagesize']['comment'] ? true : false)
            ->order('id asc')
            ->paginate($config['pagesize']['comment']);

        Vote::render($commentList, 'comment');
        return $commentList;
    }

    public static function notify($order)
    {
        $answer = self::get($order->source_id);
        if ($answer) {
            $answer->setInc("sales");
            $config = get_addon_config('ask');
            if ($config['peepanswerratio']) {
                list($systemRatio, $quizzerRatio, $answerRatio) = explode(':', $config['peepanswerratio']);
                $amount = $answer->price > 0 ? $answer->price : $answer->score;
                $method = $answer->price > 0 ? "money" : "score";
                $method = "\\app\\common\\model\\User::{$method}";
                //付费查看答案分成
                $systemRatio > 0 && call_user_func_array($method, [$systemRatio * $amount, $config['system_user_id'], '付费查看答案分成']);
                $quizzerRatio > 0 && call_user_func_array($method, [$quizzerRatio * $amount, $answer->question->user_id, '付费查看答案分成']);
                $answerRatio > 0 && call_user_func_array($method, [$answerRatio * $amount, $answer->user_id, '付费查看答案分成']);
            }
        }
    }


    /**
     * api接口的权限内容渲染
     * @param object $question
     * @param object $data
     * @param object $value
     * @param int $user_id
     * @return void
     */
    public static function render(&$question, &$data, &$value, $user_id)
    {
        $value->peep_status = $value->getPeepStatus($question);

        //专区检测
        if ((isset($data['zoneProductList']) && !empty($data['zoneProductList'])) || (isset($data['zoneConditionList']) && !empty($data['zoneConditionList']))) {

            $str = '此回答只针对';
            if (!empty($data['zoneConditionList'])) {
                $str .= '[' . implode('/', $data['zoneConditionList']) . ']';
            }
            if (!empty($data['zoneProductList'])) {
                if (!empty($data['zoneConditionList'])) $str .= '且';
                if (count($data['zoneProductList']) > 1) {
                    $str .= ' 同时购买了';
                } else {
                    $str .= ' 购买了';
                }
                foreach ($data['zoneProductList'] as $keys => $items) {
                    if ($keys > 0) $str .= '和';
                    $str .= '[' . $items['productname'] . ']';
                }
            }

            $str .= '的用户可见';

            $value->content = '>' . $str;
            $value->content_fmt = $str;
        } elseif ($question->isprivate && ($question->user_id != $user_id || $value->user_id != $user_id) && !Service::isAdmin()) {

            $value->content = '>此回答仅提问者可以查看';
            $value->content_fmt = '此回答仅提问者可以查看';
        } elseif ($value->peep_status == 'waiting') {

            $str = '此回答需要待提问者采纳最佳答案 ' . ($question->reward_remain_seconds > 0 ? '或' . $question->reward_remain_text : '') . '后可见';
            $value->content = '>' . $str;
            $value->content_fmt = $str;
        } elseif ($value->peep_status == 'unpaid') {

            $str = $value->price > 0 ? "此回答被采纳为最佳答案，付费￥{$value->price} 元可查看" : "此回答被采纳为最佳答案，需要 {$value->score} 积分可查看";
            $value->content = '>' . $str;
            $value->content_fmt = $str;
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
     * 关联文章模型
     */
    public function question()
    {
        return $this->belongsTo('\addons\ask\model\Question', 'question_id', 'id')->field('reports,peeps,deletetime', true)->setEagerlyType(1);
    }

}
