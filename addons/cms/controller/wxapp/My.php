<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Comment;
use addons\cms\model\Page;

/**
 * 我的
 */
class My extends Base
{
    protected $noNeedLogin = ['aboutus'];

    /**
     * 我发表的评论
     */
    public function comment()
    {
        $page = (int)$this->request->request('page');
        $commentList = Comment::
        with('archives')
            ->where(['user_id' => $this->auth->id])
            ->where(['type' => 'archives'])
            ->order('id desc')
            ->page($page, 10)
            ->select();
        foreach ($commentList as $index => &$item) {
            $item->create_date = human_date($item->createtime);
            $item->hidden(['ip', 'useragent', 'deletetime', 'aid', 'subscribe', 'status', 'type', 'updatetime']);
            $item->aid = $item->archives->eid;
            if ($item->archives) {
                $item->archives->id = $item['archives']['eid'];
            }
        }

        $this->success('', ['commentList' => $commentList]);
    }

    /**
     * 关于我们
     */
    public function aboutus()
    {
        $pageInfo = Page::getByDiyname('aboutus');
        if (!$pageInfo || $pageInfo['status'] != 'normal') {
            $this->error(__('单页未找到'));
        }
        $pageInfo->image = cdnurl($pageInfo->image, true);
        $pageInfo->visible(['id', 'title', 'image', 'content', 'createtime']);
        $pageInfo = $pageInfo->toArray();
        $this->success('', ['pageInfo' => $pageInfo]);
    }
}
