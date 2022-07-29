<?php

namespace addons\cms\controller\api;

use addons\cms\library\CommentException;
use think\Config;
use think\Exception;

/**
 * 评论
 */
class Comment extends Base
{
    protected $noNeedLogin = ['index'];

    public function _initialize()
    {
        parent::_initialize();
        //检测ID是否加密
        $this->hashids('aid');
    }

    /**
     * 评论列表
     */
    public function index()
    {
        $aid = (int)$this->request->param('aid');
        $page = (int)$this->request->param('page');
        Config::set('paginate.page', $page);
        $commentList = \addons\cms\model\Comment::getCommentList(['aid' => $aid]);
        $commentList = $commentList->getCollection();
        foreach ($commentList as $index => $item) {
            if ($item->user) {
                $item->user->avatar = cdnurl($item->user->avatar, true);
                $item->user->visible(explode(',', 'id,nickname,avatar'));
            }
            $item->hidden(['ip', 'useragent', 'deletetime', 'aid', 'subscribe', 'status', 'type', 'updatetime']);
        }
        $this->success('', ['commentList' => $commentList]);
    }

    /**
     * 发表评论
     */
    public function post()
    {
        try {
            $params = $this->request->post();
            $comment = \addons\cms\model\Comment::postComment($params);
            $comment->user->visible(explode(',', 'id,nickname,avatar'));
            $comment->user->avatar = cdnurl($comment->user->avatar, true);
        } catch (CommentException $e) {
            $this->success($e->getMessage(), ['__token__' => $this->request->token()]);
        } catch (Exception $e) {
            $this->error($e->getMessage(), ['__token__' => $this->request->token()]);
        }

        $this->success(__('评论成功'), ['comment' => $comment, '__token__' => $this->request->token()]);
    }
}
