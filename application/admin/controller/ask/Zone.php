<?php

namespace app\admin\controller\ask;

use app\common\controller\Backend;

/**
 * 问答专区管理
 *
 * @icon fa fa-circle-o
 */
class Zone extends Backend
{

    /**
     * Zone模型对象
     * @var \app\admin\model\ask\Zone
     */
    protected $model = null;
    protected $multiFields = 'status,isnav';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\ask\Zone;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 添加
     */
    public function add()
    {
        $this->view->assign("condition", [
            'level'    => 0,
            'score'    => 0,
            'isexpert' => 0,
            'joindays' => 0,
        ]);
        return parent::add();
    }

    /**
     * 检测元素是否可用
     * @internal
     */
    public function check_element_available()
    {
        $id = $this->request->request('id');
        $name = $this->request->request('name');
        $value = $this->request->request('value');
        $name = substr($name, 4, -1);
        if (!$name) {
            $this->error(__('Parameter %s can not be empty', 'name'));
        }
        if ($id) {
            $this->model->where('id', '<>', $id);
        }
        $exist = $this->model->where($name, $value)->find();
        if ($exist) {
            $this->error(__('The data already exist'));
        } else {
            $this->success();
        }
    }
}
