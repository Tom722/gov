<?php

namespace addons\cms\controller\api;

use addons\cms\library\Service;
use addons\cms\model\Diydata;
use addons\cms\model\Diyform as DiyformModel;

/**
 * 自定义表单控制器
 */
class Diyform extends Base
{
    protected $noNeedLogin = ['*'];

    protected $diyform = null;

    public function _initialize()
    {
        parent::_initialize();
        $diyname = $this->request->param('diyname');
        if (!$diyname) {
            $diyname = $this->request->get('id/d', '');
        }
        //都没有
        if (!$diyname) {
            //如果表单为空则取第一个表单
            $diyform = DiyformModel::where('status', 'normal')->order('id', 'asc')->find();
        } else {
            if ($diyname && !is_numeric($diyname)) {
                $diyform = DiyformModel::getByDiyname($diyname);
            } else {
                $diyform = DiyformModel::get($diyname);
            }
        }
        if (!$diyform || $diyform['status'] != 'normal') {
            $this->error(__('表单未找到'));
        }
        $diyform->hidden(['admin_id', 'table', 'posttpl', 'listtpl', 'showtpl']);
        $this->diyform = $diyform;
    }

    /**
     * 获取字段
     */
    public function index()
    {
        $form_id = $this->request->request("form_id/d"); //编辑的表单id
        $diydata = new Diydata([], $this->diyform);

        if (!$this->diyform['isguest'] && !$this->auth->isLogin()) {
            $this->error("请登录后再操作", null, 401);
        }

        if ($form_id) {
            if (!$this->auth->isLogin()) {
                $this->error("请登录后再操作", null, 401);
            }
            $diydata = $diydata->find($form_id);
            if (!$diydata) {
                $this->error("未找到指定数据");
            }
            if ($diydata['user_id'] != $this->auth->id) {
                $this->error("无法进行越权操作");
            }
        }

        $fields = DiyformModel::getDiyformFields($this->diyform['id'], $diydata->toArray());

        foreach ($fields as $item) {
            if ($item['type'] == 'array') {
                $item->value = html_entity_decode($item->value);
            }
        }

        $this->success('', [
            'diyform' => $this->diyform,
            'fields'  => $fields
        ]);
    }

    /**
     * 提交或修改表单数据
     */
    public function postForm()
    {
        $diyform = $this->diyform;
        if ($diyform['needlogin'] && !$this->auth->isLogin()) {
            $this->error("请登录后再操作", null, 401);
        }
        $form_id = $this->request->post("form_id/d"); //编辑的表单id
        $diydata = new Diydata([], $diyform);
        if ($form_id) {
            if (!$this->auth->isLogin()) {
                $this->error("请登录后再操作", null, 401);
            }
            $diydata = $diydata->find($form_id);
            if (!$diydata) {
                $this->error("未找到指定数据");
            }
            if ($diydata['user_id'] != $this->auth->id) {
                $this->error("无法进行越权操作");
            }
            if (!$diyform['isedit']) {
                $this->error("表单不允许编辑！");
            }

        }
        //开启验证码
        $captcha = $this->request->param('captcha');
        if ($diyform->iscaptcha && !captcha_check($captcha, $diyform->id)) {
            $this->error('验证码不正确');
        }
        $config = get_addon_config('cms');

        $row = $this->request->post('', '', 'trim,xss_clean');
        unset($row['id']);

        $fields = DiyformModel::getDiyformFields($diyform['id']);
        foreach ($fields as $index => $field) {
            if ($field['isrequire'] && (!isset($row[$field['name']]) || $row[$field['name']] == '')) {
                $this->error("{$field['title']}不能为空！");
            }
        }

        $row['user_id'] = $this->auth->id;
        foreach ($row as $index => &$value) {
            if (is_array($value) && isset($value['field'])) {
                $value = json_encode(\app\common\model\Config::getArrayData($value), JSON_UNESCAPED_UNICODE);
            } else {
                $value = is_array($value) ? implode(',', $value) : $value;
            }
        }
        $diydata['status'] = 'hidden';

        try {
            $diydata->allowField(true)->save($row);
        } catch (\Exception $e) {
            $this->error("发生错误:" . $e->getMessage());
        }
        //发送通知
        Service::notice(config('cms.sitename') . '收到新的' . $diyform['name']);

        $this->success($diyform['successtips'] ? $diyform['successtips'] : '提交成功！');
    }

    public function formList()
    {
        $config = get_addon_config('cms');
        $diyform = $this->diyform;

        if (!$diyform['isguest'] && !$this->auth->isLogin()) {
            $this->error("请登录后再操作", null, 401);
        }
        $filter = $this->request->get('filter/a', []);
        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $multiple = $this->request->get('multiple/d', 0);
        $keyword = $this->request->get('keyword');

        $params = [];
        $filter = $this->request->get();
        $filter = array_diff_key($filter, array_flip(['orderby', 'orderway', 'page', 'multiple']));
        if (isset($filter['filter'])) {
            $filter = array_merge($filter, $filter['filter']);
        }
        if ($filter) {
            $params['filter'] = $filter;
        }
        if ($orderby) {
            $params['orderby'] = $orderby;
        }
        if ($orderway) {
            $params['orderway'] = $orderway;
        }
        if ($multiple) {
            $params['multiple'] = $multiple;
        }

        //默认排序字段
        $orders = [
            ['name' => 'default', 'field' => 'createtime DESC,id DESC', 'title' => __('Default')],
        ];

        //合并特殊筛选字段
        $orders = array_merge($orders, $diyform->getOrderFields());

        //获取过滤列表
        list($filterList, $filter, $params, $fields, $multiValueFields, $fieldsList) = Service::getFilterList('diyform', $diyform['id'], $filter, $params, $multiple);

        //获取排序列表
        list($orderList, $orderby, $orderway) = Service::getOrderList($orderby, $orderway, $orders, $params, $fieldsList);

        //获取过滤的条件和绑定参数
        list($filterWhere, $filterBind) = Service::getFilterWhereBind($filter, $multiValueFields, $multiple);

        $auth = $this->auth;
        $model = new Diydata([], $diyform);

        $pageList = $model
            ->where($filterWhere)
            ->bind($filterBind)
            ->where(function ($query) use ($diyform, $auth) {
                //用户过滤模式
                //如果是仅用户自己消息可见
                if ($diyform['usermode'] == 'user') {
                    $query->where('user_id', $auth->id);
                }
            })
            ->where(function ($query) use ($diyform, $auth) {
                //状态过滤模式
                if ($diyform['statusmode'] === 'normal') {
                    if ($auth->id) {
                        $query->whereRaw("user_id='" . intval($auth->id) . "' OR status='normal'");
                    } else {
                        $query->where('status', 'normal');
                    }
                }
            })
            ->where(function ($query) use ($diyform, $keyword) {
                $field = '';
                $fieldArr = explode(',', $diyform->fields);
                if (in_array('name', $fieldArr)) {
                    $field = 'name';
                } elseif (in_array('title', $fieldArr)) {
                    $field = 'title';
                } elseif (in_array('content', $fieldArr)) {
                    $field = 'content';
                }
                if ($keyword && $field) {
                    $query->where($field, 'like', '%' . $keyword . '%');
                }
            })
            ->order($orderby, $orderway)
            ->paginate(15);

        foreach ($pageList as $item) {

            if (isset($item['images']) && !empty($item['images'])) {
                $images = explode(',', $item->images);
                foreach ($images as &$res) {
                    $res = cdnurl($res, true);
                }
                unset($res);
                $item->images = $images;
            }

            if (isset($item['image']) && !empty($item['image'])) {
                $image = explode(',', $item->image);
                foreach ($image as &$res) {
                    $res = cdnurl($res, true);
                }
                unset($res);
                $item->image = $image;
            }
            $item->content = mb_substr(strip_tags($item->content), 0, 80);
        }

        $this->success('', [
            'pageList'   => $pageList,
            'orderList'  => $orderList,
            'filterList' => $filterList
        ]);
    }

    /**
     * 查看详情
     */
    public function show()
    {
        $diyform = $this->diyform;
        if (!$diyform['isguest'] && !$this->auth->isLogin()) {
            $this->error("请登录后再操作", null, 401);
        }
        $id = $this->request->param('form_id/d');
        $auth = $this->auth;
        $model = new Diydata([], $diyform);

        $diydata = $model
            ->where('id', $id)
            ->where(function ($query) use ($diyform, $auth) {
                //用户过滤模式
                //如果是仅用户自己消息可见
                if ($diyform['usermode'] == 'user') {
                    $query->where('user_id', $auth->id);
                }
            })
            ->where(function ($query) use ($diyform, $auth) {
                //状态过滤模式
                if ($diyform['statusmode'] === 'normal') {
                    if ($auth->id) {
                        $query->whereRaw("user_id='" . intval($auth->id) . "' OR status='normal'");
                    } else {
                        $query->where('status', 'normal');
                    }
                }
            })
            ->find();

        if (!$diydata) {
            $this->error("数据未找到或正在审核");
        }

        $fields = DiyformModel::getDiyformFields($this->diyform['id'], $diydata->toArray());

        foreach ($fields as $item) {
            $setting = $item->setting;
            unset($setting['table']);
            $item->setting = $setting;
            if (in_array($item['type'], ['image', 'file'])) {
                $item->value = cdnurl($item->value, true);
            }
            if (in_array($item['type'], ['images', 'files'])) {
                $ifs = explode(',', $item->value);
                foreach ($ifs as &$res) {
                    $res = cdnurl($res, true);
                }
                $item->value = $ifs;
            }
            if ($item['type'] == 'array') {
                $item->value = json_decode(html_entity_decode($item->value), true);
            }
        }
        $diyform['isedit'] = $this->auth->id && $diydata['user_id'] == $this->auth->id ? true : false;

        $this->success('', [
            'fieldsList' => $fields,
            'diydata'    => $diydata,
            'diyform'    => $diyform
        ]);
    }
}
