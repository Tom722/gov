<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use think\Db;
use think\Exception;
use think\Validate;

/**
 * 邀请控制器
 * Class Invite
 * @package addons\ask\controller
 */
class Invite extends Base
{
    protected $noNeedLogin = [];
    protected $layout = 'default';

    public function index()
    {
        return '';
    }

    /**
     * 发送消息
     */
    public function post()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $question_id = $this->request->param('id');

        $question = \addons\ask\model\Question::get($question_id);
        if (!$question || $question['status'] == 'hidden') {
            $this->error("问题未找到!");
        }

        $type = $this->request->post('type');
        $qq = $this->request->post('qq');
        $email = $this->request->post('email');
        $user_id = $this->request->post('user/d');
        $price = $this->request->post('price/f');
        $price = max(0, $price);
        $user = null;
        if ($type == 'site') {
            $user = \app\common\model\User::get($user_id);
            if (!$user) {
                $this->error("未找到邀请的用户");
            }
            if ($user->id == $this->auth->id) {
                $this->error("你不能邀请你自己");
            }
            $email = $user->email;
        } else if ($type == 'qq') {
            if (!Validate::is($qq, 'integer') || strlen($qq) > 11) {
                $this->error("请输入正确的QQ号码");
            }
            $email = "{$qq}@qq.com";
        } else if ($type == 'email') {
            if (!Validate::is($email, "email")) {
                $this->error("请输入正确的邮箱");
            }
        }

        $config = get_addon_config('ask');
        //免费邀请上限
        if (!$price) {
            $count = \addons\ask\model\Invite::where('user_id', $this->auth->id)
                ->where('price', '0')->whereTime('createtime', 'today')->count();
            if ($count > $config['maxinvitelimit']) {
                $this->error("每日邀请超过上限");
            }
        }

        if ($type == 'site' && $price > 0) {
            if ($this->auth->money < $price) {
                $this->error("当前余额不足，无法进行付费邀请");
            }
        }
        $data = [
            'user_id'        => $this->auth->id,
            'invite_user_id' => $user_id ? $user_id : 0,
            'question_id'    => $question->id,
            'price'          => $type == 'site' ? $price : 0,
            'email'          => $email ? $email : '',
            'isanswered'     => 0
        ];
        if ($user && $data['invite_user_id']) {
            //邮件通知
            if ($price > 0) {
                Service::sendEmail($data['invite_user_id'], "你收到一条来自小伙伴的付费邀请信息", ['content' => "你在《" . config('site.name') . "问答社区》收到一条来自小伙伴发来的付费邀请信息，邀请你解答小伙伴的问题《{$question->title}》，在小伙伴采纳最佳答案前解答即可获得赏金，快来解答吧", 'url' => $question->fullurl], 'priceinvite');
            } else {
                Service::sendEmail($data['invite_user_id'], "你收到一条来自小伙伴的邀请信息", ['content' => "你在《" . config('site.name') . "问答社区》收到一条来自小伙伴发来的付费邀请信息，邀请你解答小伙伴的问题《{$question->title}》，快来帮帮小伙伴吧", 'url' => $question->fullurl], 'invite');
            }
        } else {
            //邮件邀请始终发送
            if (!$user) {
                Service::sendEmail($email, "你收到一条来自小伙伴的邀请信息", ['content' => "你在《" . config('site.name') . "问答社区》收到一条来自小伙伴发来的邀请信息，邀请你帮忙解答问题《{$question->title}》，快来帮帮Ta吧。", 'url' => $question->full_url]);
            }
        }

        Db::startTrans();
        try {
            if ($type == 'site' && $price > 0) {
                \app\common\model\User::money(-$price, $this->auth->id, "邀请回答");
            }
            \addons\ask\model\Invite::create($data, true);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("邀请失败，请重试");
        }

        $this->success("邀请成功", addon_url("ask/question/index"));
        return;
    }

    public function email()
    {
        $this->view->engine->layout(false);
        $this->view->assign(['content' => '邀请你帮忙解答问题，感谢你的支持', 'siteurl' => '/']);
        return $this->view->fetch('common/email');
    }

    //邀请列表
    public function search()
    {
        $q = $this->request->request('q', '', 'trim');
        $user = new \app\common\model\User;
        if ($q) {
            $user->where('username|nickname', 'like', '%' . $q . '%');
        } else {
            $user->where('title', '<>', '');
        }
        $userList = $user
            ->field('id,nickname,avatar')
            ->orderRaw('rand()')
            ->limit(6)
            ->select();
        if (!$q && !$userList) {
            $auth = $this->auth;
            $userList = \app\common\model\User::where('id', 'in', function ($query) use ($auth) {
                $query->name("ask_attention")->where('user_id', $auth->id)->where('type', 'user')->field('source_id');
            })
                ->field('id,nickname,avatar')
                ->orderRaw('rand()')
                ->limit(6)
                ->select();
        }
        $data = collection($userList)->toArray();
        $this->success("", "", $data);
    }

}
