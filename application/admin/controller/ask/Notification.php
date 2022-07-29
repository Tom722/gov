<?php

namespace app\admin\controller\ask;

use app\common\controller\Backend;

/**
 * 问答通知管理
 *
 * @icon fa fa-circle-o
 */
class Notification extends Backend
{

    /**
     * Notification模型对象
     * @var \app\admin\model\ask\Notification
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ask\Notification;

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
            $total = $this->model->with(['fromuser', 'touser'])
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model->with(['fromuser', 'touser'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $index => $item) {
                $item->fromuser->visible(['id', 'nickname']);
                $item->touser->visible(['id', 'nickname']);
            }

            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }
}
