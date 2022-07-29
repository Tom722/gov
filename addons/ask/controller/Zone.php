<?php

namespace addons\ask\controller;

class Zone extends Base
{

    protected $layout = 'default';
    protected $noNeedLogin = ['*'];

    public function index()
    {
        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->order('weigh DESC,id DESC')
            ->paginate(100);

        $this->view->assign('title', "专区");
        $this->view->assign("zoneList", $zoneList);

        return $this->view->fetch();
    }

    /**
     * 专区详情
     */
    public function show()
    {
        $type = $this->request->request('type', 'question');
        $id = $this->request->param('id', 0);
        $diyname = $this->request->param('diyname', '');

        $zone = $id ? \addons\ask\model\Zone::get($id) : \addons\ask\model\Zone::getByDiyname($diyname);
        if (!$zone) {
            $this->error("未找到指定专区");
        }
        if (!in_array($type, ['question', 'article'])) {
            $this->error("未找到指定类型");
        }

        //专区权限检测
        if (!\addons\ask\model\Zone::check($zone->id, $zoneProductList, $zoneConditionList, $zoneList)) {
            $this->view->assign('zoneProductList', $zoneProductList);
            $this->view->assign('zoneConditionList', $zoneConditionList);
            $this->view->assign('zoneList', $zoneList);
            return $this->view->fetch("zone/tips", ['__zone__' => $zone]);
        }
        $questionList = $articleList = null;

        if ($type == 'question') {
            $questionList = \addons\ask\model\Question::getIndexQuestionList('new', null, null, $zone->id, null, "flag <> 'top' or flag IS NULL");
            $this->view->assign('questionList', $questionList);
        } else {
            $articleList = \addons\ask\model\Article::getIndexArticleList('new', null, null, $zone->id, null, "flag <> 'top' or flag IS NULL");
            $this->view->assign('articleList', $articleList);
        }

        $this->view->assign('zoneType', $type);
        $this->view->assign('__zone__', $zone);
        $this->view->assign('__pagelist__', $type == 'question' ? $questionList : $articleList);
        $this->view->assign('title', $zone->name);

        if ($this->request->isAjax()) {
            return $this->view->fetch('ajax/get_' . $type . '_list');
        }
        $zone->setInc('views');
        return $this->view->fetch();
    }

    public function selectpage()
    {
        $zoneList = \addons\ask\model\Zone::where('1=1')
            ->field('id,name,diyname')
            ->order('weigh DESC,id DESC')
            ->select(100);
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => collection($zoneList)->toArray()]);
    }
}
