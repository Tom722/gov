<?php

namespace app\admin\controller\cms;

use app\common\controller\Backend;

/**
 * 专题管理
 *
 * @icon fa fa-newspaper-o
 */
class Special extends Backend
{

    /**
     * Special模型对象
     * @var \app\admin\model\cms\Special
     */
    protected $model = null;

    protected $noNeedRight = ['check_element_available'];

    public function _initialize()
    {
        parent::_initialize();
        $config = get_addon_config('cms');
        if ($config['specialdatalimit'] != 'all') {
            $this->dataLimit = $config['specialdatalimit'];
        }

        $config = get_addon_config('cms');
        $this->assignconfig('spiderRecord', intval($config['spiderrecord']?? 0));

        $this->model = new \app\admin\model\cms\Special;
        $this->view->assign("flagList", $this->model->getFlagList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);


            \app\admin\model\cms\SpiderLog::render($list, 'special');

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $values = [];
        $fields = \addons\cms\library\Service::getCustomFields('special', 0, $values);

        $this->view->assign('fields', $fields);
        $this->view->assign('values', $values);
        return parent::add();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $values = \app\admin\model\cms\Special::get($ids);
        if (!$values) {
            $this->error(__('No Results were found'));
        }
        $values = $values->toArray();
        $fields = \addons\cms\library\Service::getCustomFields('special', 0, $values);

        $this->view->assign('fields', $fields);
        $this->view->assign('values', $values);
        return parent::edit($ids);
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
