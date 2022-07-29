<?php

namespace addons\ask\controller;

use addons\ask\library\Converter;
use addons\ask\library\Service;
use addons\ask\model\Category;
use think\Config;
use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * Api接口控制器
 * Class Api
 * @package addons\ask\controller
 */
class Api extends Base
{

    protected $noNeedLogin = ['*'];

    /**
     *
     * @var Converter
     */
    protected $converter = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->request->filter('');
        Config::set('default_return_type', 'json');
        $apikey = $this->request->request('apikey');
        $config = get_addon_config('ask');
        if (!$config['apikey']) {
            $this->error('请先在后台配置API密钥');
        }
        if ($config['apikey'] != $apikey) {
            $this->error('密钥不正确');
        }
        $this->converter = new Converter();
    }

    /**
     * 问题
     */
    public function question()
    {
        $data = $this->request->request();
        if (isset($data['user']) && $data['user']) {
            $user = \app\common\model\User::where('nickname', $data['user'])->find();
            if ($user) {
                $data['user_id'] = $user->id;
            }
        }
        if (isset($data['category']) && $data['category']) {
            $category = Category::where('name', $data['category'])->where('type', 'question')->find();
            if ($category) {
                $data['category_id'] = $category->id;
            }
        }
        $data['content'] = !isset($data['content']) ? '' : $data['content'];
        $data['content'] = isset($data['html']) && $data['html'] ? $this->converter->parseString($data['content']) : $data['content'];
        $data['content_fmt'] = '';
        $data['user_id'] = !isset($data['user_id']) ? 1 : $data['user_id'];
        $data['createtime'] = !isset($data['createtime']) ? null : (is_numeric($data['createtime']) ? $data['createtime'] : strtotime($data['createtime']));
        $tags = isset($data['tags']) ? $data['tags'] : '';

        Db::startTrans();
        try {
            $question = \addons\ask\model\Question::create($data, true);
            if ($tags) {
                //刷新标签
                \addons\ask\model\Tag::refresh($tags, 'question', $question->id);
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功', null, ['questiondata' => $question->toArray()]);
        return;
    }

    /**
     * 文章
     */
    public function article()
    {
        $data = $this->request->request();
        if (isset($data['user']) && $data['user']) {
            $user = \app\common\model\User::where('nickname', $data['user'])->order('id', 'desc')->find();
            if ($user) {
                $data['user_id'] = $user->id;
            }
        }
        if (isset($data['category']) && $data['category']) {
            $category = Category::where('name', $data['category'])->where('type', 'article')->order('id', 'desc')->find();
            if ($category) {
                $data['category_id'] = $category->id;
            }
        }
        $data['content'] = isset($data['html']) && $data['html'] ? $this->converter->parseString($data['content']) : $data['content'];
        $data['content_fmt'] = '';
        $data['user_id'] = !isset($data['user_id']) ? 1 : $data['user_id'];
        $data['createtime'] = !isset($data['createtime']) ? null : (is_numeric($data['createtime']) ? $data['createtime'] : strtotime($data['createtime']));
        $tags = isset($data['tags']) ? $data['tags'] : '';

        Db::startTrans();
        try {
            $article = \addons\ask\model\Article::create($data, true);
            if ($tags) {
                //刷新标签
                \addons\ask\model\Tag::refresh($tags, 'article', $article->id);
            }
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功', null, ['articledata' => $article->toArray()]);
        return;
    }

    /**
     * 回答
     */
    public function answer()
    {
        $data = $this->request->request();
        if (isset($data['user']) && $data['user']) {
            $user = \app\common\model\User::where('nickname', $data['user'])->find();
            if ($user) {
                $data['user_id'] = $user->id;
            }
        }
        $question = null;
        $data['question_id'] = isset($data['question_id']) ? $data['question_id'] : 0;
        //如果有传标题，根据标签从数据库中查询出问题的ID
        if (!$data['question_id'] && isset($data['title'])) {
            $question = \addons\ask\model\Question::withTrashed()->where("title", $data['title'])->order('id', 'desc')->find();
            if ($question) {
                $data['question_id'] = $question->id;
            }
        } else {
            $question = \addons\ask\model\Question::withTrashed()->where("id", $data['question_id'])->find();
        }
        $data['content'] = isset($data['html']) && $data['html'] ? $this->converter->parseString($data['content']) : $data['content'];
        $data['content_fmt'] = '';
        $data['createtime'] = !isset($data['createtime']) ? null : (is_numeric($data['createtime']) ? $data['createtime'] : strtotime($data['createtime']));
        $data['user_id'] = !isset($data['user_id']) ? 1 : $data['user_id'];

        if (!$question) {
            $this->error("未找到匹配的问题");
        }

        Db::startTrans();
        try {
            $answer = \addons\ask\model\Answer::create($data, true);
            $question->setInc('answers', 1);
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功', null, ['questiondata' => $question->toArray(), 'answerdata' => $answer->toArray()]);
        return;
    }

    /**
     * 评论
     */
    public function comment()
    {
        $data = $this->request->request();
        if (isset($data['user']) && $data['user']) {
            $user = \app\common\model\User::where('nickname', $data['user'])->find();
            if ($user) {
                $data['user_id'] = $user->id;
            }
        }
        $data['type'] = isset($data['type']) ? $data['type'] : 'article';
        $data['source_id'] = isset($data['source_id']) ? $data['source_id'] : 0;
        //如果有传标题，根据标签从数据库中查询出问题的ID
        if (!$data['source_id'] && isset($data['title'])) {
            if ($data['type'] == 'article') {
                $article = \addons\ask\model\Article::withTrashed()->where("title", $data['title'])->order('id', 'desc')->find();
                if ($article) {
                    $data['source_id'] = $article->id;
                }
            } else if ($data['type'] == 'question') {
                $question = \addons\ask\model\Question::withTrashed()->where("title", $data['title'])->order('id', 'desc')->find();
                if ($question) {
                    $data['source_id'] = $question->id;
                }
            }
        }
        $data['content'] = isset($data['html']) && $data['html'] ? $this->converter->parseString($data['content']) : $data['content'];
        $data['content_fmt'] = '';
        $data['createtime'] = !isset($data['createtime']) ? null : (is_numeric($data['createtime']) ? $data['createtime'] : strtotime($data['createtime']));
        $data['user_id'] = !isset($data['user_id']) ? 1 : $data['user_id'];

        $model = Service::getModelByType($data['type'], $data['source_id']);
        if (!$model) {
            $this->error("未找到匹配的问题");
        }

        Db::startTrans();
        try {
            $comment = \addons\ask\model\Comment::create($data, true);
            $model->setInc("comments");
            Db::commit();
        } catch (PDOException $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        $this->success('新增成功', null, [$data['type'] . 'data' => $model->toArray(), 'commentdata' => $comment->toArray()]);
        return;
    }

}
