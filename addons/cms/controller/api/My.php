<?php

namespace addons\cms\controller\api;

use addons\cms\model\Comment;
use addons\cms\model\Page;

/**
 * 我的
 */
class My extends Base
{
    protected $noNeedLogin = ['aboutus', 'agreement'];

    /**
     * 我发表的评论
     */
    public function comment()
    {
        $commentList = Comment::with('archives')
            ->where(['user_id' => $this->auth->id])
            ->where(['type' => 'archives'])
            ->where(['status' => 'normal'])
            ->order('id desc')
            ->field('id,aid,type,pid,content,createtime')
            ->paginate();

        foreach ($commentList as $index => $item) {
            $item->create_date = human_date($item->createtime);
            if ($item->archives) {
                $item->aid = $item->archives->eid;
                $item->archives->eid = $item->archives->eid;
            }
        }
        $commentList = $commentList->toArray();

        foreach ($commentList['data'] as $index => &$datum) {
            if (isset($datum['archives']) && $datum['archives']) {
                $datum['archives']['id'] = $datum['archives']['eid'];
                unset($datum['archives']['channel']);
            }
        }

        $this->success('', ['commentList' => $commentList]);
    }

    /**
     * 我的消费订单
     */
    public function order()
    {
        $orderList = \addons\cms\model\Order::field('id,amount,archives_id,title,payamount,createtime')->with([
            'archives' => function ($query) {
                $query->field('id,user_id,title,channel_id,dislikes,likes,tags,createtime,image,images,style,comments,views,diyname');
            }
        ])->where('user_id', $this->auth->id)
            ->where('status', 'paid')
            ->order('id', 'desc')
            ->paginate(10, null);

        foreach ($orderList as $item) {
            $item->createtime = date('Y-m-d H:i:s', $item->createtime);
            if ($item->archives) {
                $item->archives_id = $item->archives->eid;
                $item->archives->eid = $item->archives->eid;
            }
        }
        $orderList = $orderList->toArray();

        foreach ($orderList['data'] as $index => &$datum) {
            if (isset($datum['archives']) && $datum['archives']) {
                $datum['archives']['id'] = $datum['archives']['eid'];
                unset($datum['archives']['channel']);
            }
            unset($datum['id']);
        }

        $this->success('', ['orderList' => $orderList]);
    }

    /**
     * 关于我们
     */
    public function aboutus()
    {
        $pageInfo = $this->getPage('aboutus');
        $this->success('', ['pageInfo' => $pageInfo]);
    }

    /**
     * 用户协议
     */
    public function agreement()
    {
        $pageInfo = $this->getPage('agreement');
        $this->success('', ['pageInfo' => $pageInfo]);
    }

    /**
     * 获取指定单页
     * @param $name
     * @return mixed
     */
    protected function getPage($name)
    {
        $pageInfo = Page::getByDiyname($name);
        if (!$pageInfo || $pageInfo['status'] != 'normal') {
            $this->error(__('单页未找到'));
        }
        unset($pageInfo['status'], $pageInfo['showtpl'], $pageInfo['parsetpl']);
        $view = new \think\View();
        $pageInfo['content'] = $view->fetch($pageInfo['content'], ["__PAGE__" => $pageInfo], [], [], true);
        return $pageInfo->toArray();
    }
}
