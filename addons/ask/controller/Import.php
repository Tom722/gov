<?php

namespace addons\ask\controller;

use addons\ask\library\Askdown;
use addons\ask\library\Service;
use addons\ask\library\VicDict;
use think\Config;
use think\Db;

/**
 * 导入
 * Class Import
 * @package addons\ask\controller
 */
class Import extends Base
{
    protected $noNeedLogin = ["*"];
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->request->isCli()) {
            $this->error('只允许在终端进行操作!');
        }
    }

    /**
     * 导入词典
     */
    public function dict()
    {
        define('_VIC_WORD_DICT_PATH_', ADDON_PATH . 'ask/data/dict.json');
        $dict = new VicDict('json');

        //添加词语词库 add(词语,词性) 可以是除保留字符（/，\ ， \x  ，\i），以外的utf-8编码的任何字符
        $lines = file(ADDON_PATH . 'ask/data/dict.txt', FILE_IGNORE_NEW_LINES);
        foreach ($lines as $index => $line) {
            $lineArr = explode(' ', $line);
            $dict->add($lineArr[0], 'n');
        }

        //保存词库
        $dict->save();
        echo "done";
    }

    public function question()
    {
        \think\Db::execute("UPDATE fa_ask_user SET unadopted=0");
        $list = \addons\ask\model\Question::where('best_answer_id', 0)->field("COUNT(*) AS nums,user_id")->group("user_id")->select();
        foreach ($list as $index => $item) {
            (new \addons\ask\model\User())->where('user_id', $item['user_id'])->update(['unadopted' => $item['nums']]);
        }

        $list = \addons\ask\model\Article::withTrashed()->select();
        foreach ($list as $index => $item) {
            $item->comments = \addons\ask\model\Comment::where('type', 'article')->where('source_id', $item['id'])->count();
            $item->save();
        }

        //重新统计评论、提问、文章
        $list = \addons\ask\model\Comment::field('COUNT(*) AS nums,user_id')->group("user_id")->having("nums>0")->select();
        foreach ($list as $index => $item) {
            (new \addons\ask\model\User())->where('user_id', $item['user_id'])->update(['comments' => $item['nums']]);
        }
        $list = \addons\ask\model\Article::field('COUNT(*) AS nums,user_id')->group("user_id")->having("nums>0")->select();
        foreach ($list as $index => $item) {
            (new \addons\ask\model\User())->where('user_id', $item['user_id'])->update(['articles' => $item['nums']]);
        }
        $list = \addons\ask\model\Question::field('COUNT(*) AS nums,user_id')->group("user_id")->having("nums>0")->select();
        foreach ($list as $index => $item) {
            (new \addons\ask\model\User())->where('user_id', $item['user_id'])->update(['questions' => $item['nums']]);
        }
        $list = \addons\ask\model\Answer::field('COUNT(*) AS nums,user_id')->group("user_id")->having("nums>0")->select();
        foreach ($list as $index => $item) {
            (new \addons\ask\model\User())->where('user_id', $item['user_id'])->update(['answers' => $item['nums']]);
        }
        echo "done";
    }

    public function format()
    {
        $askdown = Askdown::instance();
        echo "question";
        $questionList = \addons\ask\model\Question::withTrashed()->select();
        foreach ($questionList as $index => $item) {
            $item->content = str_replace(["&lt;", "&gt;"], ["<", ">"], $item['content']);
            $item->save();
        }
        echo "article";
        $questionList = \addons\ask\model\Article::withTrashed()->select();
        foreach ($questionList as $index => $item) {
            $item->content = str_replace(["&lt;", "&gt;"], ["<", ">"], $item['content']);
            $item->content_fmt = $askdown->format($item);
            $item->save();
        }

        echo "answer";
        $questionList = \addons\ask\model\Answer::withTrashed()->select();
        foreach ($questionList as $index => $item) {
            $item->content = str_replace(["&lt;", "&gt;"], ["<", ">"], $item['content']);
            $item->content_fmt = $askdown->format($item);
            $item->save();
        }

        echo "comment";
        $questionList = \addons\ask\model\Comment::withTrashed()->select();
        foreach ($questionList as $index => $item) {
            $item->content = str_replace(["&lt;", "&gt;"], ["<", ">"], $item['content']);
            $item->content_fmt = $askdown->format($item);
            $item->save();
        }

        echo "done";
        return;
    }
}
