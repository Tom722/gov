<?php

namespace addons\ask\controller;

class Index extends Base
{

    protected $layout = 'default';

    /**
     * 首页
     */
    public function index()
    {
        $type = $this->request->request('type', 'new');
        $page = $this->request->request('page/d', 1);

        $questionList = \addons\ask\model\Question::getIndexQuestionList($type, null, null, null, null, "flag <> 'top' or flag IS NULL");

        if ($page == 1) {
            $topQuestionList = \addons\ask\model\Question::where("FIND_IN_SET(`flag`, 'top')")->order('views', 'desc')->select();
            foreach ($topQuestionList as $index => $item) {
                $questionList->unshift($item);
            }
        }
        $this->view->assign('questionList', $questionList);
        $this->view->assign('__pagelist__', $questionList);
        $this->view->assign('questionType', $type);
        if ($this->request->isAjax()) {
            return $this->view->fetch('ajax/get_question_list');
        }

        //签到
        $info = get_addon_info('signin');
        if ($info && $info['state']) {
            $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)->whereTime('createtime', 'today')->find();
            $this->view->assign('signin', $signin);
            $this->view->assign('signinconfig', get_addon_config('signin'));
        }

        $config = get_addon_config('ask');
        $this->view->assign('title', $config['title'] ?: '首页');
        $this->view->assign('keywords', $config['keywords']);
        $this->view->assign('description', $config['description']);
        return $this->view->fetch();
    }

}
