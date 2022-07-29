<?php

namespace addons\ask\controller;

use addons\ask\library\Askdown;
use addons\ask\library\OrderException;
use addons\ask\library\Service;
use addons\ask\model\Category;
use addons\ask\model\Feed;
use addons\ask\model\Zone;
use think\Config;
use think\Db;
use think\Exception;

class Article extends Base
{
    protected $noNeedLogin = ["index", "show"];
    protected $layout = 'default';

    /**
     * 文章首页
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

        $categoryList = Category::getIndexCategoryList('article');
        $articleList = \addons\ask\model\Article::getIndexArticleList($type, $categoryId, null, null, null, "flag <> 'top' or flag IS NULL");

        if ($page == 1) {
            $topArticleList = \addons\ask\model\Article::getArticleList(['flag' => 'top', 'orderby' => 'views', 'cache' => false]);
            foreach ($topArticleList as $index => $item) {
                $articleList->unshift($item);
            }
        }
        $this->view->assign('title', ($category ? $category->name . ' - ' : '') . "文章中心");
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('articleType', $type);
        $this->view->assign('articleList', $articleList);
        $this->view->assign('__pagelist__', $articleList);
        if ($this->request->isAjax()) {
            return $this->view->fetch('ajax/get_article_list');
        }
        return $this->view->fetch();
    }

    /**
     * 文章详情
     */
    public function show()
    {
        $config = get_addon_config('ask');

        $id = $this->request->param('id');
        $order = $this->request->request('order', 'default');
        $article = \addons\ask\model\Article::get($id, ['category']);

        if (!$article && Service::isAdmin()) {
            $article = \addons\ask\model\Article::withTrashed()->with(['category'])->find($id);
        }

        if (!$article) {
            $this->error('文章未找到!');
        }

        if (!Service::isAdmin()) {
            if ($article['status'] == 'hidden') {
                $this->error('文章未找到!');
            }
        }
        $article->setInc('views');

        //会员数据
        $user = \addons\ask\model\User::get($article->user_id);
        $user->combine();
        $article->user = $user;

        $tags = \addons\ask\model\Tag::getTags('article', $article->id);

        //专区检测
        if (!Zone::checkTags($tags, $zoneProductList, $zoneConditionList, $zoneList)) {
            $this->view->assign('zoneProductList', $zoneProductList);
            $this->view->assign('zoneConditionList', $zoneConditionList);
            $this->view->assign('zoneList', $zoneList);
            return $this->view->fetch("article/tips");
        }

        //话题列表
        $article->setData(['tags' => $tags]);

        //相关问题
        $tagIds = array_map(function ($item) {
            return $item->id;
        }, $article->tags);
        $relatedArticleList = \addons\ask\model\Article::where('id', 'in', function ($query) use ($tagIds) {
            $query->name('ask_taggable')->where('tag_id', 'in', $tagIds)->where('type', 'question')->field('source_id');
        })
            ->where('id', '<>', $article->id)
            ->where('status', '<>', 'hidden')
            ->limit(10)
            ->select();

        //是否收藏文章
        \addons\ask\model\Collection::render($article, 'article');

        //是否点赞
        \addons\ask\model\Vote::render($article, 'article');

        //评论列表
        $commentList = \addons\ask\model\Comment::with('user')
            ->where('type', 'article')
            ->where('source_id', $article->id)
            ->order($order == 'default' ? 'id asc' : 'id desc')
            ->paginate($config['pagesize']['comment']);

        \addons\ask\model\Vote::render($commentList, 'comment');

        $this->request->get(['type' => 'article', 'id' => $article->id]);
        $this->view->assign('page', 1);
        $this->view->assign('type', 'article');
        $this->view->assign('source_id', $article->id);
        $this->view->assign('commentList', $commentList);

        $this->view->assign('__article__', $article);
        $this->view->assign('relatedArticleList', $relatedArticleList);
        $this->view->assign('title', $article->title);
        return $this->view->fetch();
    }

    /**
     * 发布文章
     */
    public function post()
    {
        if ($this->request->isPost()) {
            $config = get_addon_config('ask');
            $title = $this->request->post('title');
            $image = $this->request->post('image');
            $tags = $this->request->post('tags');
            $price = $this->request->post('price/f');
            $score = $this->request->post('score/d');
            $isanonymous = $this->request->post('isanonymous', 0);
            $summary = $this->request->post('summary', '');
            $content = $this->request->post('content', '', 'trim');
            $category_id = $this->request->post('category_id/d');
            $zone_id = $this->request->post('zone_id/d');

            $this->token();

            $this->captcha('postarticle');

            if ($isanonymous) {
                $this->error('目前暂未开放匿名文章功能');
            }
            if (!$this->auth->email) {
                $this->error('暂未绑定邮箱无法发表文章');
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法发表文章');
            }
            if (!$title) {
                $this->error('文章标题不能为空');
            }
            if (!Service::isAdmin() && in_array(mb_substr($title, -1), ['?', '？'])) {
                $this->error('文章标题不能以问号(?)结尾');
            }
            if (mb_strlen($title) < 10) {
                $this->error('文章标题字数不能少于10个汉字');
            }
            if (!$tags) {
                $this->error('标签不能为空');
            }
            if ($tags == $title) {
                $this->error('请给文章做好标签归类，标题和标签不能相同');
            }
            if (!$content) {
                $this->error('内容不能为空');
            }
            if ($content == $title) {
                $this->error('请完善文章的内容，标题和内容不能相同');
            }
            if ($this->auth->score < $config['limitscore']['postarticle']) {
                $this->error('你的积分小于' . $config['limitscore']['postarticle'] . '，无法发布文章');
            }
            if ($price) {
                if ($price < 0) {
                    $this->error('金额设置不正确');
                }
                $user = $this->auth->getUser();
                if (!$user->ask->isexpert) {
                    $this->error('只有认证的专家可以发布付费阅读');
                }
            }
            if ($score) {
                if ($score < 0) {
                    $this->error('金额设置不正确');
                }
                $user = $this->auth->getUser();
                if (!$user->ask->isexpert) {
                    $this->error('只有认证的专家可以发布付费阅读');
                }
            }

            if (!Service::isContentLegal($title . $tags . $summary . $content)) {
                $this->error("标题、标签、摘要或内容含有非法关键字");
            }
            Db::startTrans();
            try {
                $data = [
                    'category_id' => $category_id,
                    'user_id'     => $this->auth->id,
                    'zone_id'     => $zone_id,
                    'title'       => $title,
                    'image'       => $image,
                    'price'       => $price,
                    'score'       => $score,
                    'summary'     => $summary,
                    'content'     => $content,
                    'isanonymous' => $isanonymous,
                    'status'      => 'normal'
                ];

                $article = \addons\ask\model\Article::create($data, true);
                $article_id = $article->id;
                \addons\ask\model\Tag::refresh($tags, 'article', $article_id);
                //更新动态
                Feed::record($title, '', 'post_article', 'article', $article_id, $this->auth->id);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("发布文章失败");
            }
            $this->success('发布成功', $article->url);
        }
        $tag_id = $this->request->request('tag_id');
        $tag = $tag_id ? \addons\ask\model\Tag::get($tag_id) : null;

        $user = null;

        $zone_id = $this->request->request('zone_id');
        $zone = $zone_id ? \addons\ask\model\Zone::get($zone_id) : null;

        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->field('id,name,diyname')
            ->order('weigh DESC,id DESC')
            ->select();
        $categoryId = $this->request->request('category');
        $categoryList = Category::getIndexCategoryList('article');
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('__tag__', $tag);
        $this->view->assign('__user__', $user);
        $this->view->assign('__zone__', $zone);
        $this->view->assign('__article__', null);
        $this->view->assign('zoneList', $zoneList);
        $this->view->assign('title', '发布文章');
        return $this->view->fetch();
    }

    /**
     * 编辑文章
     */
    public function update()
    {
        $id = $this->request->param('id');
        $article = \addons\ask\model\Article::get($id);
        if (!$article) {
            $this->error("文章未找到");
        }
        if (!Service::isAdmin()) {
            if ($article['status'] == 'hidden') {
                $this->error("文章未找到");
            }

            if ($article->user_id != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }
        if ($this->request->isPost()) {
            $title = $this->request->post('title');
            $tags = $this->request->post('tags');
            $price = $this->request->post('price/f', 0);
            $score = $this->request->post('score/d', 0);
            $isanonymous = $this->request->post('isanonymous', 0);
            $summary = $this->request->post('summary', '');
            $content = $this->request->post('content', '', 'trim');
            $category_id = $this->request->post('category_id');
            $zone_id = $this->request->post('zone_id/d');
            $price = max(0, $price);
            $score = max(0, $score);
            $score = $price ? 0 : $score;
            if (!$this->auth->email) {
                $this->error('暂未绑定或激活邮箱无法进行操作');
            }
            if (!$this->auth->mobile) {
                $this->error('暂未绑定手机号无法进行操作');
            }
            if (!$title) {
                $this->error('标题不能为空');
            }
            if (!Service::isAdmin() && in_array(mb_substr($title, -1), ['?', '？'])) {
                $this->error('文章标题不能以问号(?)结尾');
            }
            if (mb_strlen($title) < 10) {
                $this->error('文章标题字数不能少于10个汉字');
            }
            if (!$tags) {
                $this->error('标签不能为空，请确保有按空格或回车确认');
            }
            if (!$content) {
                $this->error('内容不能为空');
            }

            Db::startTrans();
            try {
                $data = [
                    'category_id' => $category_id,
                    'zone_id'     => $zone_id,
                    'title'       => $title,
                    'price'       => $price,
                    'score'       => $score,
                    'summary'     => $summary,
                    'content'     => $content,
                    'isanonymous' => $isanonymous,
                ];

                //更新问题
                $article->allowField(true)->save($data);
                $article_id = $article->id;
                //刷新标签
                \addons\ask\model\Tag::refresh($tags, 'article', $article_id);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("更新文章失败:" . $e->getMessage());
            }
            $this->success('操作成功', $article->url);
        }
        $tag = null;
        $user = null;
        $zone = $article->zone_id ? \addons\ask\model\Zone::get($article->zone_id) : null;

        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->field('id,name,diyname')
            ->order('weigh DESC,id DESC')
            ->select();
        $categoryId = $article->category_id;
        $categoryList = Category::getIndexCategoryList('article');
        $this->view->assign('categoryId', $categoryId);
        $this->view->assign('categoryList', $categoryList);
        $this->view->assign('zoneList', $zoneList);
        $this->view->assign('__tag__', $tag);
        $this->view->assign('__user__', $user);
        $this->view->assign('__zone__', $zone);
        $this->view->assign('__article__', $article);
        $this->view->assign('title', '更新文章');
        return $this->view->fetch('article/post');
    }

    /**
     * 删除
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
        $article_id = $this->request->param('id');

        $article = \addons\ask\model\Article::get($article_id);
        if (!$article) {
            $this->error("文章未找到");
        }
        if (!Service::isAdmin()) {
            if ($article['status'] == 'hidden') {
                $this->error("文章未找到");
            }
            if ($article['user_id'] != $this->auth->id) {
                $this->error("无法进行越权访问");
            }
        }

        Db::startTrans();
        try {
            $article->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("删除失败");
        }
        $this->success("删除成功", addon_url("ask/article/index"));
    }

    /**
     * 配置
     */
    public function config()
    {
        $this->view->engine->layout(false);
        $question_id = $this->request->param('id');

        $article = \addons\ask\model\Article::get($question_id);
        if (!$article) {
            $this->error("文章未找到!");
        }
        if (!Service::isAdmin()) {
            if ($article['status'] == 'hidden') {
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
                $article->style = implode('|', $style);
                $article->flag = implode(',', $flag);
                $article->save();
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("修改失败");
            }
            $this->success("修改成功");
        }
        $this->view->assign('__article__', $article);
        $this->view->assign('__item__', $article);
        $this->success("", "", $this->view->fetch('common/config'));
    }

    /**
     * 立即支付
     */
    public function paynow()
    {
        $id = $this->request->param('id');

        $article = \addons\ask\model\article::get($id);
        if (!$article || $article['status'] == 'hidden') {
            $this->error('文章未找到！');
        }
        if ($article->price <= 0 && $article->score <= 0) {
            $this->error('无需要付费！');
        }
        $title = '付费查看文章';
        $amount = $article->price ? $article->price : $article->score;
        $currency = $article->price ? 'money' : 'score';
        try {
            \addons\ask\library\Order::submit('answer', $article->id, $amount, 'balance', $title, $currency);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success('', '', $article->content_fmt);
            } else {
                $this->error($e->getMessage(), '', [
                    'id'    => $id,
                    'type'  => 'article',
                    'title' => $title,
                    'price' => $article->price,
                ]);
            }
        }
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

        $article = \addons\ask\model\Article::get($question_id);
        if (!$article) {
            $this->error("文章未找到!");
        }
        if (!Service::isAdmin()) {
            $this->error("无法进行越权访问");
        }

        $prefix = Config::get('database.prefix');

        Db::startTrans();
        try {
            $data = [
                'id'          => $article->id,
                'title'       => $article->title,
                'content'     => $article->content,
                'content_fmt' => Askdown::instance()->format($article->content),
                'category_id' => 2,
                'user_id'     => $article->user_id,
                'views'       => $article->views,
                'createtime'  => $article->createtime,
                'updatetime'  => $article->updatetime,
                'deletetime'  => $article->deletetime,
                'price'       => 0,
                'status'      => $article->status
            ];

            $question_id = \think\Db::table("{$prefix}ask_question")->insert($data, false, true);
            $question = \addons\ask\model\Question::withTrashed()->where('id', $question_id)->find();

            $source = function ($query) use ($article, $prefix) {
                $query->name("ask_comment")->where('type', 'article')->where("source_id", $article->id)->field("id");
            };

            //评论
            $commentList = \addons\ask\model\Comment::withTrashed()->where($source)->select();
            foreach ($commentList as $index => $item) {
                $data = [
                    'user_id'     => $item['user_id'],
                    'question_id' => $question->id,
                    'content'     => $item['content'],
                    'content_fmt' => Askdown::instance()->format($item['content']),
                    'voteup'      => $item['voteup'],
                    'createtime'  => $item['createtime'],
                    'updatetime'  => $item['updatetime'],
                    'status'      => $item['status'],
                ];
                $answer_id = \think\Db::table("{$prefix}ask_answer")->insert($data, false, true);
                //答案
                $answer = \addons\ask\model\Answer::withTrashed()->where('id', $answer_id)->find();
                //投票
                \addons\ask\model\Vote::where('type', 'comment')->where('source_id', $item->id)->update(['type' => 'answer', 'source_id' => $answer_id]);
            }

            //收藏
            \addons\ask\model\Collection::where('type', 'article')->where('source_id', $article->id)->update(['type' => 'question', 'source_id' => $question->id]);
            //感谢
            \addons\ask\model\Thanks::where('type', 'article')->where('source_id', $article->id)->update(['type' => 'question', 'source_id' => $question->id]);

            //标签
            \addons\ask\model\Taggable::where('type', 'article')->where('source_id', $article->id)->update(['type' => 'question', 'source_id' => $question->id]);

            //标题统计，增减问题和文章标签数量
            $list = \addons\ask\model\Tag::withTrashed()->where('id', 'in', function ($query) use ($question, $prefix) {
                $query->name("ask_taggable")->where('type', 'question')->where('source_id', $question->id)->field("tag_id");
            })->select();
            foreach ($list as $index => $item) {
                $item->setDec("articles");
                $item->setInc("questions");
            }

            //采纳数
            \addons\ask\model\User::where('user_id', $article->user_id)->setInc('unadopted');

            $question->answers = \addons\ask\model\Answer::where('question_id', $question->id)->count();
            $question->save();

            \addons\ask\model\User::where('user_id', $article->user_id)->setInc('questions');
            \addons\ask\model\User::where('user_id', $article->user_id)->setDec('articles');

            //删除文章
            \think\Db::execute("DELETE FROM {$prefix}ask_article WHERE id='{$article->id}'");
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("转移失败，失败原因：" . $e->getMessage());
        }
        $this->success("转移成功", $question->url);
    }

}
