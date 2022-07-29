<?php

namespace app\admin\controller\ask;

use app\common\controller\Backend;

/**
 * 问答区块管理
 *
 * @icon fa fa-circle-o
 */
class Feed extends Backend
{

    /**
     * Feed模型对象
     * @var \app\admin\model\ask\Feed
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ask\Feed;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    public function index()
    {
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model->with(['user'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $index => $item) {
                $item->user->visible(['id', 'nickname']);
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

}
