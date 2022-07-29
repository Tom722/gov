<?php

namespace addons\ask\controller;

use addons\ask\library\Service;

/**
 * 消息控制器
 * Class Message
 * @package addons\ask\controller
 */
class Message extends Base
{
    protected $noNeedLogin = [];
    protected $layout = 'default';

    /**
     * 发送消息
     * @throws \think\exception\DbException
     */
    public function post()
    {
        if ($this->request->isPost()) {
            $id = $this->request->request('id');
            $content = $this->request->request('content');
            if (!$this->auth->email) {
                $this->error('暂未绑定或激活邮箱无法发送私信');
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法发送私信');
            }
            if (!$id || !$content) {
                $this->error("消息内容不能为空");
            }
            if (!Service::isContentLegal($content)) {
                $this->error("内容含有非法关键字");
            }
            $user = \app\common\model\User::get($id);
            if (!$user) {
                $this->error("接收方未找到");
            }
            if ($user->id == $this->auth->id) {
                $this->error("你不能给自己发消息");
            }
            $lastMessage = \addons\ask\model\Message::where('from_user_id', $this->auth->id)->order('id', 'desc')->find();
            if ($lastMessage && time() - $lastMessage['createtime'] < 30) {
                $this->error("私信消息发送过快！");
            }
            $data = [
                'from_user_id' => $this->auth->id,
                'to_user_id'   => $id,
                'content'      => $content,
            ];
            \addons\ask\model\Message::create($data);

            //邮件通知
            Service::sendEmail($id, "你收到一条来自小伙伴发来的私信", ['content' => "你在《" . config('site.name') . "问答社区》收到一条来自小伙伴发来的私信，别错过任何一条重要消息哦", 'url' => addon_url('ask/index/index', [], false, true)], 'message');
            $this->success("消息发送成功");
        }
        return;
    }

}
