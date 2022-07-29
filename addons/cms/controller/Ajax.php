<?php

namespace addons\cms\controller;

use addons\cms\model\Collection;
use addons\cms\model\Fields;
use fast\Tree;
use think\Config;
use think\Db;

/**
 * Ajax控制器
 * Class Ajax
 * @package addons\cms\controller
 */
class Ajax extends Base
{
    protected $noNeedLogin = ["share", "selectpage"];

    public function selectpage()
    {
        $id = $this->request->get("id/d", 0);
        $fieldInfo = Fields::get($id);
        if (!$fieldInfo) {
            $this->error("未找到指定字段");
        }
        $setting = $fieldInfo['setting'];
        if (!$setting || !isset($setting['table'])) {
            $this->error("字段配置不正确");
        }
        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("pageNumber");
        //分页大小
        $pagesize = $this->request->request("pageSize");
        //搜索条件
        $andor = $this->request->request("andOr", "and", "strtoupper");
        //排序方式
        $orderby = (array)$this->request->request("orderBy/a");
        //显示的字段
        //$field = $this->request->request("showField");
        $field = $setting['field'];
        //主键
        //$primarykey = $this->request->request("keyField");
        $primarykey = $setting['primarykey'];
        //主键值
        $primaryvalue = $this->request->request("keyValue");
        //搜索字段
        $searchfield = (array)$this->request->request("searchField/a");
        $searchfield = [$field, $primarykey];
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        $custom = isset($setting['conditions']) ? (array)json_decode($setting['conditions'], true) : [];
        //$custom = array_filter($custom);

        $admin_id = session('admin.id') ?: 0;
        $user_id = $this->auth->id ?: 0;
        //如果是管理员需要移除user_id筛选,否则会导致管理员无法筛选列表信息
        $admin = $this->request->request("admin/d");
        if ($admin_id && $admin) {
            unset($custom['user_id']);
        } else {
            //如果不是管理员则需要判断是否开放相应的投稿字段
            if (!in_array($fieldInfo['source'], ['model', 'diyform'])) {
                $this->error("未开放栏目信息");
            }
            if (!$fieldInfo['iscontribute']) {
                $this->error("未开放字段信息");
            }
        }

        //是否返回树形结构
        $istree = $this->request->request("isTree", 0);
        $ishtml = $this->request->request("isHtml", 0);
        if ($istree) {
            $word = [];
            $pagesize = 99999;
        }
        $order = [];
        foreach ($orderby as $k => $v) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ($primaryvalue !== null) {
            $where = [$primarykey => ['in', $primaryvalue]];
            $where = function ($query) use ($primaryvalue, $custom, $admin_id, $user_id) {
                $query->where('id', 'in', $primaryvalue);
                if ($custom && is_array($custom)) {
                    //替换暂位符
                    $search = ["{admin_id}", "{user_id}"];
                    $replace = [$admin_id, $user_id];
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), str_replace($search, $replace, $v[1]));
                        } else {
                            $query->where($k, '=', str_replace($search, $replace, $v));
                        }
                    }
                }
            };
            $pagesize = 99999;
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom, $admin_id, $user_id) {
                $logic = $andor == 'AND' ? '&' : '|';
                $searchfield = is_array($searchfield) ? implode($logic, $searchfield) : $searchfield;
                $word = array_filter($word);
                if ($word) {
                    foreach ($word as $k => $v) {
                        $query->where(str_replace(',', $logic, $searchfield), "like", "%{$v}%");
                    }
                }
                if ($custom && is_array($custom)) {
                    //替换暂位符
                    $search = ["{admin_id}", "{user_id}"];
                    $replace = [$admin_id, $user_id];
                    foreach ($custom as $k => $v) {
                        if (is_array($v) && 2 == count($v)) {
                            $query->where($k, trim($v[0]), str_replace($search, $replace, $v[1]));
                        } else {
                            $query->where($k, '=', str_replace($search, $replace, $v));
                        }
                    }
                }
            };
        }
        $list = [];
        $total = Db::table($setting['table'])->where($where)->count();
        if ($total > 0) {
            $datalist = Db::table($setting['table'])->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field($primarykey . "," . $field . ($istree ? ",pid" : ""))
                ->select();
            foreach ($datalist as $index => &$item) {
                unset($item['password'], $item['salt']);
                $list[] = [
                    $primarykey => isset($item[$primarykey]) ? $item[$primarykey] : '',
                    $field      => isset($item[$field]) ? $item[$field] : '',
                    'pid'       => isset($item['pid']) ? $item['pid'] : 0
                ];
            }
            if ($istree && !$primaryvalue) {
                $tree = Tree::instance();
                $tree->init($list, 'pid');
                $list = $tree->getTreeList($tree->getTreeArray(0), $field);
                if (!$ishtml) {
                    foreach ($list as &$item) {
                        $item = str_replace('&nbsp;', ' ', $item);
                    }
                    unset($item);
                }
            }
        }
        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        return json(['list' => $list, 'total' => $total]);
    }

    /**
     * 添加收藏
     */
    public function collection()
    {
        if (!$this->auth->isLogin()) {
            $this->error("请登录后操作");
        }
        $type = $this->request->post("type");
        $aid = $this->request->post("aid/d");
        if (!in_array($type, ['archives', 'page', 'special', 'diyform'])) {
            $this->error("参数不正确");
        }
        $model = call_user_func_array(['\\addons\\cms\\model\\' . ucfirst($type), "get"], [$aid]);
        if (!$model) {
            $this->error("未找到指定数据");
        }
        Db::startTrans();
        try {
            $collection = Collection::lock(true)->where(['type' => $type, 'aid' => $aid, 'user_id' => $this->auth->id])->find();
            if ($collection) {
                throw new \think\Exception("请勿重复收藏");
            }
            $title = $model->title;
            $url = $model->fullurl;
            $image = $model->image;
            $data = [
                'user_id' => $this->auth->id,
                'type'    => $type,
                'aid'     => $aid,
                'title'   => $title,
                'url'     => $url,
                'image'   => $image
            ];
            Collection::create($data);
            Db::commit();
        } catch (\think\Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        } catch (\Exception $e) {
            Db::rollback();
            $this->error("收藏失败");
        }
        $this->success("收藏成功");
    }

    /**
     * 微信公众号内分享
     */
    public function share()
    {
        $url = $this->request->request("url", "", "trim");
        $config = get_addon_config('third');
        if (!$config) {
            $this->error("请在后台插件管理安装配置《第三方登录》插件");
        }
        $js_sdk = new \addons\cms\library\Jssdk();
        $data = $js_sdk->getSignedPackage($url);
        $this->success('', '', $data);
    }

}
