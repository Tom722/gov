<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use addons\ask\model\Answer;
use addons\ask\model\Article;
use addons\ask\model\Attention;
use addons\ask\model\Collection;
use addons\ask\model\Feed;
use addons\ask\model\Invite;
use addons\ask\model\Message;
use addons\ask\model\Notification;
use addons\ask\model\Question;
use addons\ask\model\Vote;

class User extends Base
{

    protected $layout = 'default';
    protected $user;

    public function _initialize()
    {
        parent::_initialize();
        $id = $this->request->param('id', 0);
        $user = \addons\ask\model\User::get($id);
        if (!$user) {
            $this->error("未找到指定用户");
        }
        $user->combine();

        $this->user = $user;

        if (!Service::isAdmin()) {
            if ($user['status'] != 'normal') {
                $this->error("未找到指定用户");
            }
        }
        $this->view->assign('__user__', $user);

    }

    /**
     * 我的主页
     * @return string
     */
    public function index()
    {
        if ($this->auth->id == $this->user->id) {
            $user_id = $this->auth->id;
            //读取所有我关注的动态
            $feedList = Feed::with('user')
                ->where('user_id', 'in', function ($query) use ($user_id) {
                    $query->name("ask_attention")
                        ->where('user_id', $user_id)
                        ->where('type', 'user')
                        ->field('source_id');
                })->whereOr('user_id', $user_id)
                ->order('id', 'desc')
                ->paginate();
        } else {
            $feedList = Feed::with('user')->where('user_id', $this->user->id)->order('id', 'desc')->paginate();
            \addons\ask\model\User::increase('views', 1, $this->user->id);
        }

        $this->view->assign('feedList', $feedList);
        $this->view->assign('action', '');
        $this->view->assign('title', $this->user->me ? "最新动态" : $this->user->nickname . "的动态");
        if ($this->request->isAjax()) {
            return $this->view->fetch('user/ajax/get_feed_list');
        }
        return $this->view->fetch();
    }

    /**
     * 分发
     * @return string
     * @throws \think\Exception
     */
    public function dispatch()
    {
        $dispatch = $this->request->param('dispatch', '');
        if (!preg_match("/^[a-zA-Z0-9]+$/i", $dispatch)) {
            $this->error("参数错误");
        }
        //是否为分发路由
        if ($dispatch && method_exists($this, $dispatch)) {
            $this->request->action($dispatch);
            $this->view->assign('action', $dispatch);
            return $this->$dispatch();
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的问题
     * @return string
     * @throws \think\Exception
     */
    public function question()
    {
        $type = $this->request->request('type', 'new');
        $type = in_array($type, ['new', 'hot', 'price', 'unsolved', 'unanswer']) ? $type : 'new';

        $conditions = [];
        if (!$this->user->me) {
            $conditions = ['isanonymous' => 0];
        }
        $questionList = Question::getIndexQuestionList($type, null, $this->user->id, null, null, $conditions);
        $this->view->assign('questionList', $questionList);
        $this->view->assign('questionType', $type);
        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的问题');
        if ($this->request->isAjax()) {
            return $this->view->fetch("ajax/get_question_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的回答
     * @return string
     * @throws \think\Exception
     */
    public function answer()
    {
        $me = $this->user->me;
        //答案列表
        $answerList = Answer::with('question')
            ->where('user_id', $this->user->id)
            ->where(function ($query) use ($me) {
                if (!$me) {
                    $query->where('price', 0);
                }
            })
            ->order('id desc')
            ->paginate();

        Collection::render($answerList, 'answer');
        Vote::render($answerList, 'answer');

        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的回答');
        $this->view->assign('answerList', $answerList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_answer_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的文章
     * @return string
     * @throws \think\Exception
     */
    public function article()
    {
        //答案列表
        $articleList = Article::
        where('user_id', $this->user->id)
            ->order('id desc')
            ->paginate();

        Collection::render($articleList, 'article');

        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的回答');
        $this->view->assign('articleList', $articleList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_article_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的粉丝
     * @return string
     * @throws \think\Exception
     */
    public function follower()
    {
        $followerList = Attention::with('user')
            ->where('type', 'user')
            ->where('source_id', $this->user->id)
            ->order('id desc')
            ->paginate();

        foreach ($followerList as $index => $item) {
            $item->user->followed = Attention::check('user', $item['user_id']) ? true : false;
        }

        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的粉丝');
        $this->view->assign('followerList', $followerList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_follower_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的关注
     * @return string
     * @throws \think\Exception
     */
    public function attention()
    {
        $type = $this->request->request('type', 'user');
        $type = in_array($type, ['user', 'question']) ? $type : 'user';

        //粉丝列表
        $attentionList = Attention::with($type == 'user' ? 'expert' : $type)
            ->where('type', $type)
            ->where('user_id', $this->user->id)
            ->order('id desc')
            ->paginate();
        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的关注');
        $this->view->assign('attentionType', $type);
        $this->view->assign('attentionList', $attentionList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_attention_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我(TA)的收藏
     * @return string
     * @throws \think\Exception
     */
    public function collection()
    {
        if ($this->auth->id != $this->user->id) {
            $this->error("无法进行越权操作");
        }
        $type = $this->request->request('type', 'question');
        $type = in_array($type, ['user', 'question', 'article', 'answer']) ? $type : 'question';

        //粉丝列表
        $collectionList = Collection::with($type == 'user' ? 'expert' : $type)
            ->where('type', $type)
            ->where('user_id', $this->user->id)
            ->order('id desc')
            ->paginate();
        if ($type == 'answer') {
            foreach ($collectionList as $index => $item) {
                $item->question = Question::get($item->answer->question_id);
            }
        }
        $this->view->assign('title', ($this->user->me ? '我' : 'TA') . '的收藏');
        $this->view->assign('collectionType', $type);
        $this->view->assign('collectionList', $collectionList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_collection_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我的通知
     */
    public function notification()
    {
        if ($this->auth->id != $this->user->id) {
            $this->error("无法进行越权操作");
        }
        $act = $this->request->request('act');
        if ($act) {
            if ($act == 'marktopall') {
                if ($this->user->notifications || $this->user->messages || $this->user->invites) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['notifications' => 0, 'messages' => 0, 'invites' => 0]);
                    $this->auth->getUser()->ask->notifications = 0;
                    $this->auth->getUser()->ask->messages = 0;
                    $this->auth->getUser()->ask->invites = 0;
                }
            } else if ($act == 'marktop') {
                if ($this->user->notifications) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['notifications' => 0]);
                    $this->auth->getUser()->ask->notifications = 0;
                }
            } else if ($act == 'markall') {
                Notification::where('to_user_id', $this->auth->id)->where('isread', 0)->update(['isread' => 1]);
                $this->user->data(['notifications' => 0])->save();
                $this->success('标记成功');
            } else {
                $notification_id = $this->request->request('notification_id');
                $notification = Notification::where('id', $notification_id)->where('to_user_id', $this->auth->id)->find();
                if (!$notification) {
                    $this->error('未找到指定信息');
                }
                if ($act == 'del') {
                    $notification->delete();
                    $this->user->setDec('notifications');
                    $this->success('删除成功');
                } else if ($act == 'mark') {
                    $notification->isread = 1;
                    $notification->save();
                    $this->user->setDec('notifications');
                    $this->success('标记成功');
                }
            }
        }
        $notificationList = Notification::with('from')
            ->where('to_user_id', $this->user->id)
            ->order('id desc')
            ->paginate();
        $this->view->assign('title', '我的通知');
        $this->view->assign('notificationList', $notificationList);

        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_notification_list");
        }
        return $this->view->fetch();
    }

    /**
     * 我的私信
     */
    public function message()
    {
        if ($this->auth->id != $this->user->id) {
            $this->error("无法进行越权操作");
        }
        $act = $this->request->request('act');
        if ($act) {
            if ($act == 'marktop') {
                if ($this->user->messages) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['messages' => 0]);
                    $this->auth->getUser()->ask->messages = 0;
                }
            } else if ($act == 'markall') {
                Message::where('to_user_id', $this->auth->id)->where('isread', 0)->update(['isread' => 1]);
                if ($this->user->messages) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['messages' => 0]);
                    $this->auth->getUser()->ask->messages = 0;
                }
                $this->success('标记成功');
            } else {
                $message_id = $this->request->request('message_id');
                $message = Message::where('id', $message_id)->where('to_user_id', $this->auth->id)->find();
                if (!$message) {
                    $this->error('未找到指定信息');
                }
                if ($act == 'del') {
                    $message->istodeleted = 1;
                    $message->save();
                    //如果双边都删除
                    if ($message->isfromdeleted && $message->istodeleted) {
                        $message->delete();
                    }
                    $this->user->setDec("messages");
                    $this->success('删除成功');
                } else if ($act == 'mark') {
                    $message->isread = 1;
                    $message->save();
                    $this->user->setDec("messages");
                    $this->success('标记成功');
                }
            }
        }
        $messageList = Message::with('from')
            ->where('to_user_id', $this->user->id)
            ->where('istodeleted', 0)
            ->order('id desc')
            ->paginate();
        $this->view->assign('title', '我的私信');
        $this->view->assign('messageList', $messageList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_message_list");
        }
        return $this->view->fetch();
    }

    /**
     * 邀请我回答的
     */
    public function invite()
    {
        if ($this->auth->id != $this->user->id) {
            $this->error("无法进行越权操作");
        }
        $act = $this->request->request('act');
        if ($act) {
            if ($act == 'marktop') {
                if ($this->user->invites) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['invites' => 0]);
                    $this->auth->getUser()->ask->invites = 0;
                }
            } else if ($act == 'markall') {
                if ($this->user->invites) {
                    \addons\ask\model\User::where('user_id', $this->user->id)->update(['invites' => 0]);
                    $this->auth->getUser()->ask->invites = 0;
                }
                $this->success('标记成功');
            } else {
                $invite_id = $this->request->request('invite_id');
                $invite = Invite::where('id', $invite_id)->where('invite_user_id', $this->auth->id)->find();
                if (!$invite) {
                    $this->error('未找到指定邀请信息');
                }
                if ($act == 'del') {
                    $invite->delete();
                    $this->user->setDec("invites");
                    $this->success('删除成功');
                } else if ($act == 'markone') {
                    $this->user->setDec("invites");
                    $this->success('标记成功');
                }
            }
        }
        $inviteList = Invite::with(['user', 'question'])
            ->where('invite_user_id', $this->user->id)
            ->order('id desc')
            ->paginate();
        $this->view->assign('title', '邀请我回答的');
        $this->view->assign('inviteList', $inviteList);
        if ($this->request->isAjax()) {
            return $this->view->fetch("user/ajax/get_invite_list");
        }
        return $this->view->fetch();
    }

}
