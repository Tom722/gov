<?php

namespace addons\ask\controller;

use addons\ask\library\FulltextSearch;
use addons\ask\library\Service;
use think\Config;
use think\Exception;

class Ajax extends Base
{

    protected $layout = '';

    /**
     * 获取问题列表
     */
    public function get_question_list()
    {
        $category_id = $this->request->request('category_id', '');
        $user_id = $this->request->request('user_id', '');
        $tag_id = $this->request->request('tag_id', '');
        $type = $this->request->request('type', 'new');
        $keyword = $this->request->request('keyword', '');

        $questionList = \addons\ask\model\Question::getIndexQuestionList($type, $category_id, $user_id, $tag_id, $keyword);

        $this->view->assign('questionList', $questionList);

        return $this->view->fetch();
    }

    /**
     * 获取问题列表
     */
    public function get_article_list()
    {
        $category_id = $this->request->request('category_id');
        $user_id = $this->request->request('user_id', '');
        $tag_id = $this->request->request('tag_id', '');
        $type = $this->request->request('type', 'new');
        $keyword = $this->request->request('keyword', '');

        $articleList = \addons\ask\model\Article::getIndexArticleList($type, $category_id, $user_id, $tag_id, $keyword);

        $this->view->assign('articleList', $articleList);

        return $this->view->fetch();
    }

    /**
     * 获取回答列表
     */
    public function get_answer_list()
    {
        $question = null;
        $question_id = $this->request->param('id');
        $user_id = $this->request->param('user_id');
        $order = $this->request->param('order');
        if ($question_id) {
            $question = \addons\ask\model\Question::get($question_id);
            if (!$question || $question['status'] == 'hidden') {
                $this->error('问题未找到');
            }
        }

        $answerList = \addons\ask\model\Answer::getAnswerList($question_id, $user_id, $order);

        $this->view->assign('__question__', $question);
        $this->view->assign('answerList', $answerList);
        return $this->view->fetch();
    }

    /**
     * 获取评论列表
     */
    public function get_comment_list()
    {
        $source_id = (int)$this->request->request('id');
        $type = $this->request->request('type');
        $order = $this->request->request('order', 'default');
        $page = (int)$this->request->request('page', 1);

        $config = get_addon_config('ask');
        //评论列表
        $commentList = \addons\ask\model\Comment::with(['user', 'replyuser'])
            ->where('type', $type)
            ->where('source_id', $source_id)
            ->order($order == 'default' ? 'id asc' : 'id desc')
            ->paginate($config['pagesize']['comment']);

        \addons\ask\model\Vote::render($commentList, 'comment');

        $this->view->assign('page', $page);
        $this->view->assign('type', $type);
        $this->view->assign('source_id', $source_id);
        $this->view->assign('commentList', $commentList);
        return $this->view->fetch();
    }

    /**
     * 获取感谢列表
     */
    public function get_thanks_list()
    {
        $source_id = (int)$this->request->param('id');
        $type = $this->request->param('type');
        $model = Service::getModelByType($type, $source_id, 'user');
        if (!$model) {
            $this->error('未找到数据');
        }
        $thanksList = \addons\ask\model\Thanks::with('user')
            ->where('type', $type)
            ->where('source_id', $source_id)
            ->where('status', 'paid')
            ->order('id', 'asc')
            ->select();
        $this->view->assign('__model__', $model);
        $this->view->assign('type', $type);
        $this->view->assign('thanksList', $thanksList);
        return $this->view->fetch();
    }

    /**
     * 获取用户信息
     */
    public function get_user_info()
    {
        $id = (int)$this->request->request('id');

        $user = \addons\ask\model\User::get($id);
        if (!$user) {
            $this->error("未找到指定用户");
        }

        $user->combine();

        if (!Service::isAdmin()) {
            if ($user['status'] != 'normal') {
                $this->error("未找到指定用户");
            }
        }

        $this->view->assign('__user__', $user);
        return $this->view->fetch();
    }

    /**
     * 搜索栏搜索问题、文章和标签
     */
    public function get_search_autocomplete()
    {
        $q = $this->request->request('q', '', 'trim');
        $type = $this->request->request('type');
        $data = [];
        if ($q) {
            $tagList = [];
            if ($type != 'post') {
                //标签列表
                $tagList = \addons\ask\model\Tag::where(function ($query) use ($q) {
                    $arr = array_filter(explode(' ', $q));
                    foreach ($arr as $index => $item) {
                        $query->whereOr('name', 'like', '%' . $item . '%');
                    }
                })
                    ->field('id,name,icon')
                    ->where('status', 'normal')
                    ->limit(10)
                    ->select();
                $tagList = collection($tagList)->toArray();
                if ($tagList) {
                    $tagList = [$tagList];
                }
            }

            $config = get_addon_config('ask');
            if ($config['searchtype'] == 'xunsearch') {
                $result = FulltextSearch::search($q);
                $dataList = $result['list'];
                foreach ($dataList as $index => &$item) {
                    $item = array_intersect_key($item, array_flip(['id', 'title', 'type', 'user_id', 'createtime']));
                }
            } else {
                $prefix = Config::get('database.prefix');

                $keywordArr = array_filter(explode(',', str_replace(['，', '　', ' '], ',', $q)));
                $searchFields = implode(' AND ', array_fill(0, count($keywordArr), "title LIKE ?"));
                $bindArr = [];
                foreach ($keywordArr as $item) {
                    $bindArr[] = '%' . $item . '%';
                }
                //通过SQL查寻数据库ask_question表和ask_article表
                $dataList = \think\Db::query("SELECT id,user_id,title,type,price,nums,createtime FROM (
                  ( SELECT id, user_id, title, 'question' AS type, answers AS nums, price, createtime FROM {$prefix}ask_question WHERE status!='hidden' AND deletetime IS NULL AND {$searchFields} LIMIT 10 ) UNION ALL 
                  ( SELECT id, user_id, title, 'article' AS type, comments AS nums, price, createtime FROM {$prefix}ask_article WHERE status!='hidden' AND deletetime IS NULL AND {$searchFields} LIMIT 10 )
                  ) AS temp ORDER BY nums DESC LIMIT 20;", array_merge($bindArr, $bindArr));
            }
            foreach ($dataList as $index => &$item) {
                $item['url'] = addon_url("ask/{$item['type']}/show", [":id" => $item['id']]);
                $item['create_date'] = time() - $item['createtime'] > 7 * 86400 ? date("Y-m-d", $item['createtime']) : human_date($item['createtime']);
            }
            $this->auth->render($dataList);
            $data = array_merge($tagList, $dataList);
        }
        return json($data);
    }

    /**
     * 编辑器@搜索
     */
    public function get_user_autocomplete()
    {
        if (!$this->auth->isLogin()) {
            return json([]);
        }
        $q = $this->request->request('q');
        $source_id = $this->request->request('id/d');
        $type = $this->request->request('type');
        $user = new \app\common\model\User;
        $model = null;
        $user_id = $this->auth->id;
        if (!$q && $source_id && $type) {
            try {
                $model = Service::getModelByType($type, $source_id);
                $user->where(['id' => $model->user_id, 'status' => 'normal']);

                if (in_array($type, ['question', 'answer'])) {
                    $source_id = $type == 'question' ? $source_id : $model->question_id;
                    $user->whereOr('id', 'in', function ($query) use ($type, $source_id, $user_id) {
                        $query->name('ask_answer')->where('user_id', '<>', $user_id)->where('question_id', $source_id)->field('user_id');
                    });
                } else {
                    $user->whereOr('id', 'in', function ($query) use ($type, $source_id, $user_id) {
                        $query->name('ask_comment')->where('user_id', '<>', $user_id)->where('type', $type)->where('source_id', $source_id)->field('user_id');
                    });
                }
            } catch (Exception $e) {

            }
        } else {
            $user->where('nickname|username', 'like', '%' . $q . '%');
            $user->where('id', '<>', $user_id);
        }
        $userList = $user
            ->field('id,avatar,nickname,username')
            ->order('score', 'desc')
            ->limit(8)
            ->select();
        $data = collection($userList)->toArray();
        foreach ($data as $index => &$datum) {
            $datum['nickname'] .= ($model && $model->user_id == $datum['id'] ? ' <label class="label label-primary">楼主</label>' : '');
        }
        $data = array_values($data);
        return json($data);
    }

    /**
     * 编辑器#搜索
     */
    public function get_question_autocomplete()
    {
        $q = $this->request->request('q', '', 'trim');
        $q = preg_replace("/^(\d+)\[(.*?)\]?(\([A|Q]?\))?$/i", "$2", $q);

        $dataList = [];
        if ($q) {
            $config = get_addon_config('ask');
            if ($config['searchtype'] == 'xunsearch') {
                $result = FulltextSearch::search($q);
                $dataList = $result['list'];
                foreach ($dataList as $index => &$item) {
                    $item = array_intersect_key($item, array_flip(['id', 'title', 'type', 'user_id', 'createtime']));
                }
            } else {
                $prefix = Config::get('database.prefix');

                $keywordArr = array_filter(explode(',', str_replace(['，', '　', ' '], ',', $q)));
                $searchFields = implode(' AND ', array_fill(0, count($keywordArr), "title LIKE ?"));
                $bindArr = [];
                foreach ($keywordArr as $item) {
                    $bindArr[] = '%' . $item . '%';
                }
                //通过SQL查寻数据库ask_question表和ask_article表
                $dataList = \think\Db::query("SELECT id,user_id,title,type,price,nums,createtime FROM (
                  ( SELECT id, user_id, title, 'question' AS type, answers AS nums, price, createtime FROM {$prefix}ask_question WHERE status!='hidden' AND deletetime IS NULL AND {$searchFields} LIMIT 10 ) UNION ALL 
                  ( SELECT id, user_id, title, 'article' AS type, comments AS nums, price, createtime FROM {$prefix}ask_article WHERE status!='hidden' AND deletetime IS NULL AND {$searchFields} LIMIT 10 )
                  ) AS temp ORDER BY nums DESC LIMIT 20;", array_merge($bindArr, $bindArr));
            }
            foreach ($dataList as $index => &$item) {
                $item['url'] = addon_url("ask/{$item['type']}/show", [":id" => $item['id']]);
                $item['create_date'] = time() - $item['createtime'] > 7 * 86400 ? date("Y-m-d", $item['createtime']) : human_date($item['createtime']);
            }
        }
        return json($dataList);
    }

    /**
     * 标签搜索
     */
    public function get_tags_autocomplete()
    {
        $q = $this->request->request('q');
        $tagList = [];
        if ($q) {
            $tagList = \addons\ask\model\Tag::where('name', 'like', '%' . $q . '%')
                ->where('status', 'normal')
                ->limit(8)
                ->column('name');
        }
        return json($tagList);
    }

    /**
     * 提取标题标签
     */
    public function get_title_tags()
    {
        $title = $this->request->request("title");
        $result = Service::getContentTags($title);
        $this->success('', '', $result);
    }

}
