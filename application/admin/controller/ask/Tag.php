<?php

namespace app\admin\controller\ask;

use app\common\controller\Backend;

/**
 * 问答话题管理
 *
 * @icon fa fa-circle-o
 */
class Tag extends Backend
{

    /**
     * Tag模型对象
     * @var \app\admin\model\ask\Tag
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ask\Tag;
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 还原
     */
    public function restore($ids = "")
    {
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        $list = $this->model->onlyTrashed()->select();
        foreach ($list as $index => $item) {
            $item->deletetime = null;
            $item->save();
            $count++;
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

}
