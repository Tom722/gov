<?php

namespace addons\cms\controller;

use addons\cms\library\CommentException;
use addons\cms\model\Comment as CommentModel;
use think\addons\Controller;
use think\Exception;

/**
 * 评论控制器
 * Class Comment
 * @package addons\cms\controller
 */
class Comment extends Base
{
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        //检测ID是否加密
        $this->hashids('aid');
    }

    /**
     * 发表评论
     */
    public function post()
    {
        try {
            $params = $this->request->post();
            CommentModel::postComment($params);
        } catch (CommentException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage(), null, ['token' => $this->request->token()]);
            } else {
                $this->error($e->getMessage(), null, ['token' => $this->request->token()]);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage(), null, ['token' => $this->request->token()]);
        }
        $this->success(__('评论成功！'), null, ['token' => $this->request->token()]);
    }

    /**
     * 取消评论订阅
     */
    public function unsubscribe()
    {
        $id = (int)$this->request->param('id');
        $key = $this->request->param('key');
        $comment = CommentModel::get($id, 'user');
        if (!$comment) {
            $this->error("评论未找到");
        }
        if ($key !== md5($comment['id'] . $comment['user']['email'])) {
            $this->error("无法进行该操作");
        }
        if (!$comment['subscribe']) {
            $this->error("评论已经取消订阅，请勿重复操作");
        }
        $comment->subscribe = 0;
        $comment->save();
        $this->success('取消评论订阅成功');
    }
}
