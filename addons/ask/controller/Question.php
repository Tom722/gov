<?php

namespace addons\ask\controller;

use addons\ask\library\Askdown;
use addons\ask\library\Service;
use addons\ask\model\Category;
use addons\ask\model\Feed;
use addons\ask\model\Notification;
use addons\ask\model\Score;
use addons\ask\model\Zone;
use app\common\model\User;
use think\Config;
use think\Db;
use think\Exception;

class Question extends Base
{
    protected $noNeedLogin = ["index", "show"];
    protected $layout = 'default';

    /**
     * 问题首页
     */
    public function index()
    {
        $type = $this->request->request('type', 'new');
        $categoryId = $this->request->request('category/d', null);
        $page = $this->request->request('page/d', 1);
        $category = null;
        if ($categoryId) {
            $category = Category::get($categoryId);
            if (!$category) {
                $this->error("分类未找到！");
            }
        }
        $categoryList = Category::getIndexCategoryList('question');
        $questionList = \addons\ask\model\Question::getIndexQuestionList($type, $categoryId, null, null, null, "flag <> 'top' or flag IS NULL");

        if ($page == 1) {
            $topQuestionList = \addons\ask\model\Question::getQuestionList(['flag' => 'top', 'orderby' => 'views', 'cache' => false]);
            foreach ($topQuestionList as $index => $item) {
                $questionList->unshift($item);
            }
        }
        $this->view->assign('title', ($category ? $category->name . ' - ' : '') . "问答中心");
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('questionList', $questionList);
        $this->view->assign('__pagelist__', $questionList);
        $this->view->assign('questionType', $type);

        if ($this->request->isAjax()) {
            return $this->view->fetch('ajax/get_question_list');
        }
        return $this->view->fetch();
    }

    /**
     * 问题详情
     */
    public function show()
    {
        $id = $this->request->param('id');
        $order = $this->request->param('order', 'default');
        $question = \addons\ask\model\Question::get($id, ['category']);
        if (!$question && Service::isAdmin()) {
            $question = \addons\ask\model\Question::withTrashed()->with(['category'])->find($id);
        }
        if (!$question) {
            $article = \addons\ask\model\Article::get($id);
            if ($article && $article->createtime < strtotime("2019-03-10")) {
                $this->redirect(addon_url("ask/article/show", [":id" => $article->id], true));
            }
            $this->error('问题未找到!');
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error('问题未找到!');
            }
        }
        $question->setInc('views');

        //付费阅读过期且没有解答时退款
        if ($question->is_reward_expired && !$question->answers && ($question->price > 0 || $question->score > 0)) {
            $question->refund();
        }

        //会员数据
        $user = \addons\ask\model\User::get($question->user_id);
        $user->combine();
        $question->user = $user;

        $tags = \addons\ask\model\Tag::getTags('question', $question->id);

        //专区检测
        if (!Zone::checkTags($tags, $zoneProductList, $zoneConditionList, $zoneList)) {
            //问题中不做任何处理，在答案中做相关隐藏处理
            $this->view->assign('zoneProductList', $zoneProductList);
            $this->view->assign('zoneConditionList', $zoneConditionList);
            $this->view->assign('zoneList', $zoneList);
        }

        //话题列表
        $question->setData(['tags' => $tags]);

        //相关问题
        $tagIds = array_map(function ($item) {
            return $item->id;
        }, $question->tags);
        $relatedQuestionList = \addons\ask\model\Question::where('id', 'in', function ($query) use ($tagIds) {
            $query->name('ask_taggable')->where('tag_id', 'in', $tagIds)->where('type', 'question')->field('source_id');
        })
            ->where('id', '<>', $question->id)
            ->where('status', '<>', 'hidden')
            ->limit(10)
            ->select();

        //回答列表
        $answerList = \addons\ask\model\Answer::getAnswerList($question->id, null, $order);

        //是否收藏问题
        \addons\ask\model\Collection::render($question, 'question');

        //是否关注问题
        \addons\ask\model\Attention::render($question, 'question');

        //最佳回答
        $bestAnswerList = [];
        if ($question['best_answer_id']) {
            $bestAnswer = \addons\ask\model\Answer::get($question['best_answer_id'], ['user']);
            if ($bestAnswer) {
                $bestAnswerList = [$bestAnswer];

                //是否投票
                \addons\ask\model\Vote::render($bestAnswerList, 'answer');

                //是否收藏问题
                \addons\ask\model\Collection::render($bestAnswerList, 'answer');
            }
        }

        $this->view->assign('__question__', $question);
        $this->view->assign('answerList', $answerList);
        $this->view->assign('bestAnswerList', $bestAnswerList);
        $this->view->assign('relatedQuestionList', $relatedQuestionList);
        $this->view->assign('title', $question->title);
        return $this->view->fetch();
    }

    /**
     * 发布问题
     */
    public function post()
    {
        if ($this->request->isPost()) {
            $config = get_addon_config('ask');
            $title = $this->request->post('title');
            $tags = $this->request->post('tags');
            $price = $this->request->post('price/f');
            $score = $this->request->post('score/d');
            $isanonymous = $this->request->post('isanonymous/d', 0);
            $isprivate = $this->request->post('isprivate/d', 0);
            $content = $this->request->post('content', '', null);
            $category_id = $this->request->post('category_id/d');
            $zone_id = $this->request->post('zone_id/d');
            $to_user_id = $this->request->post('to_user_id/d');
            $inviteprice = $this->request->post('inviteprice/f', 0);

            $price = max(0, $price);
            $score = max(0, $score);
            $inviteprice = max(0, $inviteprice);

            $this->token();

            $this->captcha('postquestion');

            if (!$this->auth->email) {
                $this->error('暂未绑定或激活邮箱无法进行提问', url('index/user/profile'));
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法进行提问', url('index/user/profile'));
            }
            if ($this->auth->score < $config['limitscore']['postquestion']) {
                $this->error('你的积分小于' . $config['limitscore']['postquestion'] . '，无法发布问题');
            }
            if (!$category_id) {
                $this->error('分类必须选择');
            }
            if (!$title) {
                $this->error('问题标题不能为空');
            }
            if (!in_array(mb_substr($title, -1), ['?', '？'])) {
                //$this->error('问题标题必须以问号(?)结尾');
            }
            if (mb_strlen($title) < 10) {
                $this->error('问题标题字数不能少于10个汉字');
            }
            if (!$tags) {
                $this->error('标签不能为空，请确保有按空格或回车确认');
            }
            if ($tags == $title) {
                $this->error('请给问题做好标签分类，标题和标签不能相同');
            }
            if (!$content) {
                $this->error('内容不能为空');
            }
            if ($content == $title) {
                $this->error('请完善问题的内容，标题和内容不能相同');
            }
            if ($price > $this->auth->money) {
                $this->error('当前余额不足，无法发布为悬赏问题', url('index/recharge/recharge'));
            }
            if ($score > $this->auth->score) {
                $this->error('当前积分不足，无法发布为悬赏问题');
            }
            if ($price && $price < $config['minprice']) {
                $this->error('最低悬赏金额不能小于￥' . $config['minprice'] . '元');
            }
            if ($price && $price > $config['maxprice']) {
                $this->error('最高悬赏金额不能大于￥' . $config['maxprice'] . '元');
            }
            if ($score && $score < $config['minscore']) {
                $this->error('最低悬赏积分不能小于' . $config['minscore'] . '积分');
            }
            if ($score && $score > $config['maxscore']) {
                $this->error('最高悬赏积分不能大于' . $config['maxscore'] . '积分');
            }

            if ($inviteprice > 0 && $to_user_id && $to_user_id != $this->auth->id) {
                list($minInvitePrice, $maxInvitePrice) = explode('-', $config['inviteprice']);
                if ($inviteprice < $minInvitePrice || $inviteprice > $maxInvitePrice) {
                    $this->error('邀请赏金必须在￥' . $config['inviteprice'] . '元之间');
                }
                if ($inviteprice > $this->auth->money || $inviteprice + $price > $this->auth->money) {
                    $this->error('当前余额不足，无法设定付费邀请赏金', url('index/recharge/recharge'));
                }
            }

            //免费邀请上限
            if (!$price && !$score && !$inviteprice) {
                $count = \addons\ask\model\Invite::where('user_id', $this->auth->id)
                    ->where('price', '0')->whereTime('createtime', 'today')->count();
                if ($count > $config['maxinvitelimit']) {
                    $this->error("每日邀请超过上限，无法向用户发起提问");
                }
            }

            if (!Service::isContentLegal($title . $tags . $content)) {
                $this->error("标题、标签或内容含有非法关键字");
            }

            //发贴速度限制
            $nums = \addons\ask\model\Question::withTrashed()->where('user_id', $this->auth->id)->whereTime('createtime', '-1 minute')->count();
            if ($nums >= 1) {
                //$this->error("发贴速度过快，请喝杯咖啡休息一下");
            }

            Db::startTrans();
            try {
                $data = [
                    'category_id' => $category_id,
                    'user_id'     => $this->auth->id,
                    'zone_id'     => $zone_id,
                    'title'       => $title,
                    'price'       => $price,
                    'score'       => $score,
                    'content'     => $content,
                    'isanonymous' => $isanonymous,
                    'isprivate'   => $isprivate,
                    'rewardtime'  => $price > 0 || $score > 0 ? time() : null,
                    'status'      => 'normal'
                ];

                //写入问题
                $question = \addons\ask\model\Question::create($data, true);
                $question_id = $question->id;
                //刷新标签
                \addons\ask\model\Tag::refresh($tags, 'question', $question_id);
                //匿名则不发动态
                if (!$isanonymous) {
                    //更新动态
                    Feed::record($title, '', 'post_question', 'question', $question_id, $this->auth->id);
                }
                //悬赏扣除
                if ($price > 0) {
                    User::money(-$price, $this->auth->id, "发布悬赏问题");
                }
                //悬赏扣除
                if ($score > 0) {
                    User::score(-$score, $this->auth->id, "发布悬赏问题");
                }
                //发送邀请
                if ($to_user_id && $to_user_id != $this->auth->id) {
                    //付费邀请赏金扣除
                    if ($inviteprice > 0) {
                        User::money(-$inviteprice, $this->auth->id, "付费邀请");
                    }
                    $data = [
                        'user_id'        => $isanonymous ? 0 : $this->auth->id,
                        'invite_user_id' => $to_user_id,
                        'question_id'    => $question_id,
                        'price'          => $inviteprice,
                    ];
                    \addons\ask\model\Invite::create($data);
                }
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("发布问题失败:" . $e->getMessage());
            }
            $this->success('添加成功', $question->url);
        }
        $tag_id = $this->request->request('tag_id');
        $tag = $tag_id ? \addons\ask\model\Tag::get($tag_id) : null;

        $user_id = $this->request->request('user_id');
        $user = $user_id ? User::get($user_id) : null;

        $zone_id = $this->request->request('zone_id');
        $zone = $zone_id ? \addons\ask\model\Zone::get($zone_id) : null;

        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->field('id,name,diyname')
            ->order('weigh DESC,id DESC')
            ->select();

        $categoryId = $this->request->request('category');
        $categoryList = Category::getIndexCategoryList('question');
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('__tag__', $tag);
        $this->view->assign('__user__', $user);
        $this->view->assign('__zone__', $zone);
        $this->view->assign('__question__', null);
        $this->view->assign('zoneList', $zoneList);
        $this->view->assign('title', '发布问题');
        return $this->view->fetch();
    }

    /**
     * 完善问题
     */
    public function update()
    {
        $id = $this->request->param('id');
        $question = \addons\ask\model\Question::get($id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("问题未找到!");
            }
            if ($question['status'] == 'closed') {
                $this->error("问题已经关闭!");
            }
            if ($question['status'] == 'solved') {
                $this->error("问题已经解决!");
            }
            if ($question->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }
        if ($this->request->isPost()) {
            $config = get_addon_config('ask');
            $title = $this->request->post('title');
            $tags = $this->request->post('tags');
            $isanonymous = $this->request->post('isanonymous/d', 0);
            $isprivate = $this->request->post('isprivate/d', 0);
            $content = $this->request->post('content', '', 'trim');
            $category_id = $this->request->post('category_id');
            $zone_id = $this->request->post('zone_id/d');

            if (!$this->auth->email) {
                $this->error('暂未绑定或激活邮箱无法进行操作');
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法进行操作');
            }
            if ($this->auth->score < $config['limitscore']['postquestion']) {
                $this->error('你的积分小于' . $config['limitscore']['postquestion'] . '，无法发布问题');
            }
            if (!$category_id) {
                $this->error('分类必须选择');
            }
            if (!$title) {
                $this->error('问题标题不能为空');
            }
            if (!in_array(mb_substr($title, -1), ['?', '？'])) {
                //$this->error('问题标题必须以问号(?)结尾');
            }
            if (mb_strlen($title) < 10) {
                $this->error('问题标题字数不能少于10个汉字');
            }
            if (!$tags) {
                $this->error('标签不能为空');
            }
            if (!$content) {
                $this->error('内容不能为空');
            }
            if (!Service::isAdmin()) {
                if ($question->best_answer_id) {
                    $this->error('已采纳最佳答案,无法再修改');
                }
            }

            if (!Service::isContentLegal($title . $tags . $content)) {
                $this->error("标题、标签或内容含有非法关键字");
            }

            Db::startTrans();
            try {
                $data = [
                    'category_id' => $category_id,
                    'zone_id'     => $zone_id,
                    'title'       => $title,
                    'content'     => $content,
                    'isanonymous' => $isanonymous,
                    'isprivate'   => $isprivate,
                ];

                //更新问题
                $question->allowField(true)->save($data);
                $question_id = $question->id;
                //刷新标签
                \addons\ask\model\Tag::refresh($tags, 'question', $question_id);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("完善问题失败:" . $e->getMessage());
            }
            $this->success('操作成功', $question->url);
        }
        $tag = null;
        $user = null;
        $zone = $question->zone_id ? Zone::get($question->zone_id) : null;

        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->field('id,name,diyname')
            ->order('weigh DESC,id DESC')
            ->select();
        $categoryId = $question->category_id;
        $categoryList = Category::getIndexCategoryList('question');
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('__tag__', $tag);
        $this->view->assign('__user__', $user);
        $this->view->assign('__zone__', $zone);
        $this->view->assign('__question__', $question);
        $this->view->assign('zoneList', $zoneList);
        $this->view->assign('title', '完善问题');
        return $this->view->fetch('question/post');
    }

    /**
     * 采纳回答
     */
    public function adopt()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $question_id = $this->request->request("question_id");
        $best_answer_id = $this->request->request("best_answer_id");
        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("问题未找到!");
            }
            if ($question['status'] == 'closed') {
                $this->error("问题已经关闭!");
            }
            if ($question['status'] == 'solved') {
                $this->error("问题已经解决!");
            }
            if ($question->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }
        if ($question->best_answer_id) {
            $this->error("问题已经采纳");
        }
        $answer = \addons\ask\model\Answer::get($best_answer_id);
        if (!$answer || $answer->question_id != $question->id) {
            $this->error("回答未找到");
        }
        if (!Service::isAdmin()) {
            if ($question->user_id == $answer->user_id) {
                $this->error("你不能采纳自己的答案");
            }
            //相同登录的IP禁止采纳
            if ($question->user->loginip && $answer->user->loginip && $question->user->loginip == $answer->user->loginip) {
                $this->error("对不起，当前操作行为被禁止");
            }
        }
        Db::startTrans();
        try {
            $question->status = 'solved';
            $question->best_answer_id = $answer->id;
            $question->save();
            $answer->adopttime = time();
            $answer->save();
            $config = get_addon_config('ask');
            if ($question->price > 0 || $question->score > 0) {
                list($systemRatio, $userRatio) = explode(':', $config['bestanswerratio']);
                $amount = $question->price > 0 ? $question->price : $question->score;
                $method = $question->price > 0 ? "money" : "score";
                $method = "\\app\\common\\model\\User::{$method}";
                //最佳答案分成
                $systemRatio > 0 && call_user_func_array($method, [$systemRatio * $amount, $config['system_user_id'], '最佳答案分成']);
                $userRatio > 0 && call_user_func_array($method, [$userRatio * $amount, $answer->user_id, '最佳答案分成']);
            }

            //只有当回答者与提问者不同时才增加积分和消息
            if ($answer->user_id != $question->user_id) {
                //增加积分
                Score::increase('bestanswer', $answer->user_id);
                //更新动态
                if (!$question->isanonymous) {
                    Feed::record($question->title, '', 'adopt', 'question', $question_id, $question->user_id);
                    //发送通知
                    Notification::record($question->title, '', 'adopt', 'question', $question_id, $answer->user_id);
                }
            }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("采纳失败，请稍后重试");
        }
        //采纳答案，邮件通知
        Service::sendEmail($answer->user_id, "你回答的问题被提问者采纳为最佳答案了", ['content' => "你在《" . config('site.name') . "问答社区》回答的问题被小伙伴采纳为最佳答案了，快来看看吧！", 'url' => $question->full_url], 'adoptanswer');
        $this->success("采纳成功");
    }

    /**
     * 关闭问题
     */
    public function close()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $id = $this->request->param("id");
        $question = \addons\ask\model\Question::get($id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            $this->error("无法进行越权访问");
        }
        if ($question->status == 'closed') {
            $this->error("无需重复关闭");
        }
        Db::startTrans();
        try {
            $question->status = 'closed';
            $question->save();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("关闭失败，请稍后重试");
        }
        $this->success("关闭成功");
    }

    /**
     * 开启问题
     */
    public function open()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $id = $this->request->param("id");
        $question = \addons\ask\model\Question::get($id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            $this->error("无法进行越权访问");
        }
        if ($question->status == 'normal') {
            $this->error("无需重复开启");
        }
        Db::startTrans();
        try {
            $question->status = 'normal';
            $question->save();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("开启失败，请稍后重试");
        }
        $this->success("开启成功");
    }

    /**
     * 追加悬赏
     */
    public function reward()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $config = get_addon_config('ask');
        $question_id = $this->request->request("id");
        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("问题未找到!");
            }
            if ($question['status'] == 'closed') {
                $this->error("问题已经关闭!");
            }
            if ($question['status'] == 'solved') {
                $this->error("问题已经解决!");
            }
            if ($question->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }
        if ($question->best_answer_id) {
            $this->error("问题已经采纳");
        }
        $money = (float)$this->request->post("money");
        if ($money <= 0) {
            $this->error("追加的金额不能小于0");
        }
        if ($question->currency == 'score') {
            if ($this->auth->score < $money) {
                $this->error("当前积分不足");
            }
            if ($money + $question->score > $config['maxscore']) {
                $this->error('最高悬赏积分不能大于' . $config['maxscore'] . '积分');
            }
        } else {
            if ($this->auth->money < $money) {
                $this->error("当前余额不足");
            }
            if ($money + $question->price > $config['maxprice']) {
                $this->error('最高悬赏金额不能大于' . $config['maxprice'] . '元');
            }
        }
        Db::startTrans();
        try {
            if ($question->currency == 'score') {
                User::score(-$money, $this->auth->id, "追加悬赏");
                $question->setInc('score', $money);
            } else {
                User::money(-$money, $this->auth->id, "追加悬赏");
                $question->setInc('price', $money);
            }
            $question->rewardtime = time();
            $question->save();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("追加悬赏失败");
        }
        $this->success("追加悬赏成功");
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $question_id = $this->request->param('id');

        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("问题未找到!");
            }
            if ($question['status'] == 'closed') {
                $this->error("问题已经关闭!");
            }
            if ($question['status'] == 'solved') {
                $this->error("问题已经解决!");
            }
            if (($question->price > 0 || $question->score > 0) && $question->answers > 0) {
                $this->error("悬赏提问暂无法删除");
            }
            if ($question->answers > 0) {
                $this->error("问题已经有回答，无法进行删除");
            }
            if ($question->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }

        Db::startTrans();
        try {
            $question->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("删除失败");
        }
        $this->success("删除成功", addon_url("ask/question/index"));
    }

    /**
     * 配置
     */
    public function config()
    {
        $this->view->engine->layout(false);
        $question_id = $this->request->param('id');

        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error("无法进行越权访问!");
            }
        }
        if ($this->request->isPost()) {
            $color = $this->request->post("color/a", []);
            $style = $this->request->post("style/a", []);
            $flag = $this->request->post("flag/a", []);
            $color = array_unique(array_filter($color));
            $style[] = implode(',', $color);
            $style = array_unique(array_filter($style));
            $flag = array_unique(array_filter($flag));
            Db::startTrans();
            try {
                $question->style = implode('|', $style);
                $question->flag = implode(',', $flag);
                $question->save();
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("修改失败");
            }
            $this->success("修改成功");
        }
        $this->view->assign('__question__', $question);
        $this->view->assign('__item__', $question);
        $this->success("", "", $this->view->fetch('common/config'));
    }

    public function get_answer_list()
    {
        $this->view->engine->layout(false);
        $question_id = $this->request->param('id');
        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error('问题未找到！');
        }
        if (!Service::isAdmin()) {
            if ($question['status'] == 'hidden') {
                $this->error('问题未找到！');
            }
        }

        //答案列表
        $answerList = \addons\ask\model\Answer::with('user')
            ->where('question_id', $question['id'])
            ->order('id desc')
            ->paginate(10);

        $this->view->assign('__question__', $question);
        $this->view->assign('answerList', $answerList);
        return $this->view->fetch();
    }

    /**
     * 转移
     */
    public function transfer()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $question_id = $this->request->param('id');

        $question = \addons\ask\model\Question::get($question_id);
        if (!$question) {
            $this->error("问题未找到!");
        }
        if (!Service::isAdmin()) {
            $this->error("无法进行越权访问");
        }

        $prefix = Config::get('database.prefix');

        Db::startTrans();
        try {
            $data = [
                'id'          => $question->id,
                'title'       => $question->title,
                'content'     => $question->content,
                'content_fmt' => Askdown::instance()->format($question->content),
                'category_id' => 7,
                'user_id'     => $question->user_id,
                'views'       => $question->views,
                'thanks'      => $question->thanks,
                'createtime'  => $question->createtime,
                'updatetime'  => $question->updatetime,
                'deletetime'  => $question->deletetime,
                'price'       => 0,
                'summary'     => '',
                'status'      => $question->status
            ];
            $article_id = \think\Db::table("{$prefix}ask_article")->insert($data, false, true);
            $article = \addons\ask\model\Article::get($article_id);

            //转移回答
            $answerList = \addons\ask\model\Answer::withTrashed()->where('question_id', $question->id)->select();
            foreach ($answerList as $index => $item) {
                $data = [
                    'type'        => 'article',
                    'source_id'   => $article->id,
                    'user_id'     => $item['user_id'],
                    'content'     => $item['content'],
                    'content_fmt' => Askdown::instance()->format($item['content']),
                    'voteup'      => $item['voteup'],
                    'createtime'  => $item['createtime'],
                    'updatetime'  => $item['updatetime'],
                    'deletetime'  => $item['deletetime'],
                    'status'      => $item['status'],
                ];
                $comment_id = \think\Db::table("{$prefix}ask_comment")->insert($data, false, true);
                //评论
                $comment = \addons\ask\model\Comment::get($comment_id);
                //投票
                \addons\ask\model\Vote::where('type', 'answer')->where('source_id', $item->id)->update(['type' => 'comment', 'source_id' => $comment_id]);
            }

            $source = function ($query) use ($question, $prefix) {
                $query->name("ask_answer")->where("question_id", $question->id)->field("id");
            };
            //转移回答的评论
            \addons\ask\model\Comment::withTrashed()->where('type', 'answer')->where('source_id', 'in', $source)->update(['type' => 'article', 'source_id' => $article->id]);

            //转移问题的评论
            \addons\ask\model\Comment::withTrashed()->where('type', 'question')->where('source_id', $question->id)->update(["type" => 'article', 'source_id' => $article->id]);

            //转移问题的收藏
            \addons\ask\model\Collection::where('type', 'question')->where('source_id', $question->id)->update(["type" => 'article', 'source_id' => $article->id]);

            //删除收藏
            \think\Db::execute("DELETE FROM {$prefix}ask_collection WHERE type='answer' AND source_id IN (SELECT id FROM {$prefix}ask_answer WHERE question_id='{$question->id}')");
            //删除回答
            \think\Db::execute("DELETE FROM {$prefix}ask_answer WHERE question_id = '{$question->id}'");


            //收藏的话题进行转移
            \addons\ask\model\Collection::where('type', 'question')->where('source_id', $question->id)->update(['type' => 'article', 'source_id' => $article->id]);
            //关注的话题进行转移
            \addons\ask\model\Attention::where('type', 'question')->where('source_id', $question->id)->update(['type' => 'article', 'source_id' => $article->id]);
            //感谢的话题进行转移
            \addons\ask\model\Thanks::where('type', 'question')->where('source_id', $question->id)->update(['type' => 'article', 'source_id' => $article->id]);
            //问题标签进行转移
            \addons\ask\model\Taggable::where('type', 'question')->where('source_id', $question->id)->update(['type' => 'article', 'source_id' => $article->id]);
            //标题统计，增减问题和文章标签数量
            $list = \addons\ask\model\Tag::withTrashed()->where('id', 'in', function ($query) use ($article, $prefix) {
                $query->name("ask_taggable")->where('type', 'article')->where('source_id', $article->id)->field("tag_id");
            })->select();
            foreach ($list as $index => $item) {
                $item->setDec("questions");
                $item->setInc("articles");
            }

            //如果已经采纳最佳答案
            if (!$question->best_answer_id) {
                \addons\ask\model\User::where('user_id', $question->user_id)->setDec('unadopted');
            }

            $article->comments = \addons\ask\model\Comment::where('type', 'article')->where('source_id', $article->id)->count();
            $article->save();

            \addons\ask\model\User::where('user_id', $question->user_id)->setInc('articles');
            \addons\ask\model\User::where('user_id', $question->user_id)->setDec('questions');

            //删除问题
            \think\Db::execute("DELETE FROM {$prefix}ask_question WHERE id='{$question->id}'");
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("转移失败，失败原因：" . $e->getMessage());
        }
        $this->success("转移成功", $article->url);
    }

}
