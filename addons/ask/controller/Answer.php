<?php

namespace addons\ask\controller;

use addons\ask\library\OrderException;
use addons\ask\library\Service;
use addons\ask\model\Feed;
use addons\ask\model\Notification;
use think\Db;
use think\Exception;

class Answer extends Base
{
    protected $noNeedLogin = [];
    protected $layout = 'default';

    /**
     * 发布答案
     */
    public function post()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $this->view->engine->layout(false);
        $question_id = $this->request->post('id');
        $price = $this->request->post('price/f', 0);
        $score = $this->request->post('score/d', 0);
        $content = $this->request->post('content', '', 'trim');

        $this->token();

        $this->captcha('postanswer');

        $config = get_addon_config('ask');
        if (!$this->auth->email) {
            $this->error('暂未绑定邮箱无法提交答案');
        }
        if (!$this->auth->mobile) {
            $this->error('暂未绑定手机号无法提交答案');
        }
        if ($this->auth->score < $config['limitscore']['postanswer']) {
            $this->error('你的积分小于' . $config['limitscore']['postanswer'] . '，无法提交答案');
        }
        $config = get_addon_config('ask');
        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error('问题未找到！');
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("问题未找到!");
            }
            if ($question['status'] == 'closed') {
                $this->error("问题已经关闭!");
            }
            //是否解决后继续回答
            if (!$config['isstillanswer'] && $question['status'] == 'solved') {
                $this->error("问题已经解决!");
            }
        }
        if (!$content) {
            $this->error('答案不能为空');
        }

        $price = max(0, $price);
        $score = max(0, $score);

        if ($price || $score) {
            if ($question->is_peep_disabled) {
                $this->error('当前无法设定付费查看，原因：' . $question->peep_disabled_reason);
            }
            if ($price > 0) {
                if ($price < 0.1) {
                    $this->error('付费查看金额不能小于0.1');
                }
                list($minPeepMoney, $maxPeepMoney) = explode('-', $config['peepprice']);
                if ($price < $minPeepMoney || $price > $maxPeepMoney) {
                    $this->error("付费查看金额必须大于{$minPeepMoney}且小于{$maxPeepMoney}");
                }
            }
            if ($score > 0) {
                if ($score < 0.1) {
                    $this->error('付费查看积分不能小于1');
                }

                list($minPeepScore, $maxPeepScore) = explode('-', $config['peepscore']);
                if ($score < $minPeepScore || $score > $maxPeepScore) {
                    $this->error("付费查看积分必须大于{$minPeepScore}且小于{$maxPeepScore}");
                }
            }
        }

        if (!Service::isContentLegal($content)) {
            $this->error("内容含有非法关键字");
        }

        if ($config['postanswerlimittype'] == 'single') {
            $existedAnswer = \addons\ask\model\Answer::where('user_id', $this->auth->id)->where('question_id', $question->id)->find();
            if ($existedAnswer) {
                $this->error("问题只能回答一次,你可以直接补充你的答案");
            }
        }

        //防止灌水
        $last = \addons\ask\model\Answer::where(['user_id' => $this->auth->id])->order('id', 'desc')->find();
        if ($last) {
            if ($last['content'] == $content) {
                $this->error("请勿重复发表相同内容");
            }
            if (time() - $last['createtime'] < 30) {
                $this->error("发表答案速度过快，请喝杯咖啡休息一下");
            }
        }

        Db::startTrans();
        try {
            $data = [
                'user_id'     => $this->auth->id,
                'question_id' => $question->id,
                'content'     => $content,
                'price'       => $price,
                'score'       => $score,
                'status'      => 'normal'
            ];
            //插入回答
            $question->setInc('answers');
            $answer = \addons\ask\model\Answer::create($data, true);
            $answer_id = $answer->id;
            $content = $price ? '' : mb_substr(strip_tags($answer->content_fmt), 0, 100);
            //私有问题不发送动态
            if (!$question->isprivate) {
                //更新动态
                Feed::record($question->title, $content, 'post_answer', 'question', $question->id, $this->auth->id);
            }
            //发送通知
            Notification::record($question->title, $content, 'post_answer', 'question', $question->id, $question->user_id);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("发布答案失败");
        }
        //邮件通知
        Service::sendEmail($question->user_id, "你发布的问题有小伙伴发布了答案", ['content' => "你在《" . config('site.name') . "问答社区》发布的问题有小伙伴发布了答案，快来看看是不是你需要的答案", 'url' => $question->full_url], $question->answers == 1 ? 'firstanswer' : 'secondaryanswer');

        $answer = \addons\ask\model\Answer::get($answer_id, 'user');
        \addons\ask\model\Vote::render($answer, 'answer');
        \addons\ask\model\Collection::render($answer, 'answer');

        //邀请处理
        \addons\ask\model\Invite::settle($question->id);

        $this->view->assign('__question__', $question);
        $this->view->assign('answer', $answer);
        $this->success('添加成功', '', $this->view->fetch('common/answeritem'));
    }

    /**
     * 编辑答案
     */
    public function update()
    {
        $this->view->engine->layout(false);
        $id = $this->request->request('id');
        $answer = \addons\ask\model\Answer::get($id, 'question');
        if (!$answer) {
            $this->error("回答未找到");
        }
        if (!Service::isAdmin()) {
            if ($answer['status'] == 'hidden') {
                $this->error("回答未找到");
            }
            if ($answer->user_id != $this->auth->id) {
                $this->error("无法进行越权操作");
            }
            if ($answer->question->best_answer_id == $answer->id) {
                $this->error("已采纳最佳答案，无法再修改");
            }
        }
        if ($this->request->isPost()) {
            $this->captcha('postanswer');

            $config = get_addon_config('ask');
            if (!$this->auth->email) {
                $this->error('暂未绑定或激活邮箱无法提交答案');
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法提交答案');
            }
            if ($this->auth->score < $config['limitscore']['postanswer']) {
                $this->error('你的积分小于' . $config['limitscore']['postanswer'] . '，无法提交答案');
            }
            $content = $this->request->post('content', '', 'trim');
            if (!$content) {
                $this->error("内容不能为空");
            }
            if (!$this->auth->mobile) {
                $this->error('未绑定手机号无法提交答案');
            }
            if (!Service::isContentLegal($content)) {
                $this->error("内容含有非法关键字");
            }
            //只有管理员在完善答案时可以修改付费阅读的价格
            if (Service::isAdmin()) {
                $price = $this->request->post('price/f', 0);
                $price = max(0, $price);
                $answer->price = $price;
                $score = $this->request->post('score/d', 0);
                $score = max(0, $score);
                $score = $price > 0 ? 0 : $score;
                $answer->score = $score;
            }
            $answer->content = $content;
            $answer->save();
            //邀请处理
            \addons\ask\model\Invite::settle($answer->question->id);

            Service::sendEmail($answer->question->user_id, "你发布的问题有小伙伴更新了答案", ['content' => "你在《" . config('site.name') . "问答社区》发布的问题有小伙伴更新了答案，快来看看是不是你需要的答案", 'url' => $answer->question->full_url], 'updateanswer');
            $this->success("", '', $answer->content_fmt);
        }
        $this->view->assign('__answer__', $answer);
        $this->success('', '', $this->view->fetch('answer/update'));
    }

    /**
     * 付费查看
     */
    public function peep()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $id = $this->request->request('id');
        $answer = \addons\ask\model\Answer::get($id, 'question');
        if (!$answer) {
            $this->error("回答未找到");
        }
        if ($answer->price == 0 && $answer->score == 0) {
            $this->error("无需要付费查看");
        }
        if (!$answer->question->best_answer_id) {
            $this->error("当前暂不支持偷看");
        }
        if ($answer->question->best_answer_id != $answer->id) {
            $this->error("无需要付费查看");
        }
        $title = '付费查看答案';
        try {
            $amount = $answer->price > 0 ? $answer->price : $answer->score;
            $currency = $answer->price > 0 ? 'money' : 'score';
            \addons\ask\library\Order::submit('answer', $answer->id, $amount, 'balance', $title, $currency);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success('', '', $answer->content_fmt);
            } else {
                $this->error($e->getMessage(), '', [
                    'id'    => $id,
                    'type'  => 'question',
                    'title' => $title,
                    'price' => $answer->price,
                    'score' => $answer->score,
                ]);
            }
        }
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $answer_id = $this->request->param('id');

        $answer = \addons\ask\model\Answer::get($answer_id);
        if (!$answer) {
            $this->error("回答未找到!");
        }
        if ($answer['deletetime']) {
            $this->error("回答已删除!");
        }
        if (!Service::isAdmin()) {
            if ($answer['status'] == 'hidden') {
                $this->error("回答未找到!");
            }
            if ($answer->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }

        Db::startTrans();
        try {
            //如果是最佳回答
            if ($answer->adopttime) {
                $answer->question->best_answer_id = 0;
                $answer->question->status = 'normal';
                $answer->question->save();
            }
            $answer->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("删除失败");
        }
        $this->success("删除成功");
    }
}
