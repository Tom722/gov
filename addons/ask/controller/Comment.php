<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use addons\ask\model\Feed;
use addons\ask\model\Notification;
use think\Db;
use think\Exception;

class Comment extends Base
{

    protected $noNeedLogin = [];
    protected $layout = 'default';

    /**
     * 发布评论
     */
    public function post()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $this->view->engine->layout(false);
        $source_id = $this->request->param('id');
        $reply_user_id = $this->request->param('reply_user_id');
        $type = $this->request->param('type');
        $content = $this->request->post('content', '', 'trim');

        $this->token();

        $this->captcha('postcomment');

        $config = get_addon_config('ask');
        if (!$this->auth->email) {
            $this->error('暂未绑定或激活邮箱无法进行回复');
        }
        if (!$this->auth->mobile) {
            $this->error('暂未绑定手机号无法进行回复');
        }
        if ($this->auth->score < $config['limitscore']['postcomment']) {
            $this->error('你的积分小于' . $config['limitscore']['postcomment'] . '，无法发表回复');
        }

        if (!$content) {
            $this->error('回复内容不能为空');
        }

        if (!Service::isContentLegal($content)) {
            $this->error("内容含有非法关键字");
        }

        //防止灌水
        $last = \addons\ask\model\Comment::where(['user_id' => $this->auth->id])->order('id', 'desc')->find();
        if ($last) {
            if ($last['content'] == $content) {
                $this->error("请勿重复发表相同内容");
            }
            if (time() - $last['createtime'] < 30) {
                $this->error("发表评论速度过快，请喝杯咖啡休息一下");
            }
        }

        // 付费文章评论判断
        if ($type == 'article') {
            $model = Service::getModelByType($type, $source_id);
            if ($model && $model->price > 0 && $model->user_id != $this->auth->id && !Service::isAdmin()) {
                if ($config['isarticlepaidcomment'] && !$model->paid) {
                    $this->error("只有付费查看后的用户才能发表评论");
                }
            }
        }

        Db::startTrans();
        try {
            $data = [
                'user_id'       => $this->auth->id,
                'type'          => $type,
                'source_id'     => $source_id,
                'reply_user_id' => $reply_user_id,
                'content'       => $content,
                'status'        => 'normal'
            ];
            Db::name("ask_{$type}")->where('id', $source_id)->setInc('comments');
            $comment = \addons\ask\model\Comment::create($data, true);
            $comment_id = $comment->id;

            $model = Service::getModelByType($type, $source_id);
            if ($model) {
                $title = isset($model['title']) ? $model['title'] : '';
                $title = $title ? $title : ($type == 'answer' ? $model->question->title : "");

                $content = mb_substr(strip_tags($comment->content_fmt), 0, 100);
                //更新动态
                Feed::record($title, $content, 'post_comment', $type, $source_id, $this->auth->id);
                $to_user_id = $type == 'user' ? $source_id : (isset($model['user_id']) ? $model['user_id'] : 0);
                //发送通知
                Notification::record($title, $content, 'post_comment', $type, $source_id, $to_user_id);
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("发表评论失败");
        }

        $comment = \addons\ask\model\Comment::get($comment_id, 'user');
        \addons\ask\model\Vote::render($comment, 'comment');
        $this->view->assign('comment', $comment);
        $this->success('添加成功', '', $this->view->fetch('common/commentitem'));
    }

    /**
     * 编辑评论
     */
    public function update()
    {
        $this->view->engine->layout(false);
        $id = $this->request->request('id');
        $comment = \addons\ask\model\Comment::get($id);
        if (!$comment) {
            $this->error("评论未找到");
        }
        if (!Service::isAdmin()) {
            if ($comment['status'] == 'hidden') {
                $this->error("评论未找到");
            }
            if ($comment->user_id != $this->auth->id) {
                $this->error("无法进行越权操作");
            }
        }
        if ($this->request->isPost()) {
            $this->captcha('postanswer');

            $content = $this->request->post('content', '', 'trim');
            if (!$content) {
                $this->error("内容不能为空");
            }
            $comment->content = $content;
            $comment->save();
            $this->success("", '', $comment->content_fmt);
        }
        $this->view->assign('__comment__', $comment);
        $this->success('', '', $this->view->fetch('comment/update'));
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $comment_id = $this->request->param('id');

        $comment = \addons\ask\model\Comment::get($comment_id);
        if (!$comment) {
            $this->error("回答未找到!");
        }
        if ($comment['deletetime']) {
            $this->error("评论已删除!");
        }
        if (!Service::isAdmin()) {
            if ($comment['status'] == 'hidden') {
                $this->error("评论未找到!");
            }
            if ($comment->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }

        Db::startTrans();
        try {
            $comment->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("删除失败");
        }
        $this->success("删除成功");
    }
}
