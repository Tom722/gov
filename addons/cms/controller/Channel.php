<?php

namespace addons\cms\controller;

use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Channel as ChannelModel;
use addons\cms\model\Fields;
use addons\cms\model\Modelx;
use addons\cms\model\SpiderLog;
use think\Cache;
use think\Config;

/**
 * 栏目控制器
 * Class Channel
 * @package addons\cms\controller
 */
class Channel extends Base
{
    public function index()
    {
        $config = get_addon_config('cms');

        $diyname = $this->request->param('diyname');

        if ($diyname && !is_numeric($diyname)) {
            $channel = ChannelModel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : $this->request->param('id', '');
            $channel = ChannelModel::get($id);
        }
        if (!$channel || $channel['status'] != 'normal') {
            $this->error(__('No specified channel found'));
        }

        $filter = $this->request->get('filter/a', []);
        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '');
        $multiple = $this->request->get('multiple/d', 0);

        $orderway = $orderway && in_array(strtolower($orderway), ['asc', 'desc']) ? $orderway : 'desc';

        $params = [];
        $filter = $this->request->get();
        $filter = array_diff_key($filter, array_flip(['orderby', 'orderway', 'page', 'multiple']));
        if (isset($filter['filter'])) {
            $filter = array_merge($filter, $filter['filter']);
            unset($filter['filter']);
        }
        if ($filter) {
            $filter = array_filter($filter, 'strlen');
            $params['filter'] = $filter;
            $params = $filter;
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
        if ($channel['type'] === 'link') {
            $this->redirect($channel['outlink']);
        }

        //加载模型数据
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }

        //默认排序字段
        $orders = [
            ['name' => 'default', 'field' => 'weigh DESC,publishtime DESC', 'title' => __('Default')],
        ];

        //合并主表筛选字段
        $orders = array_merge($orders, $model->getOrderFields());

        //获取过滤列表
        list($filterList, $filter, $params, $fields, $multiValueFields, $fieldsList) = Service::getFilterList('model', $model['id'], $filter, $params, $multiple);

        //获取排序列表
        list($orderList, $orderby, $orderway) = Service::getOrderList($orderby, $orderway, $orders, $params, $fieldsList);

        //获取过滤的条件和绑定参数
        list($filterWhere, $filterBind) = Service::getFilterWhereBind($filter, $multiValueFields, $multiple);

        $filterChannel = function ($query) use ($channel) {
            $query->where(function ($query) use ($channel) {
                if ($channel['listtype'] <= 2) {
                    $query->whereOr("channel_id", $channel['id']);
                }
                if ($channel['listtype'] == 1 || $channel['listtype'] == 3) {
                    $query->whereOr('channel_id', 'in', function ($query) use ($channel) {
                        $query->name("cms_channel")->where('parent_id', $channel['id'])->field("id");
                    });
                }
                if ($channel['listtype'] == 0 || $channel['listtype'] == 4) {
                    $childrenIds = \addons\cms\model\Channel::getChannelChildrenIds($channel['id'], false);
                    if ($childrenIds) {
                        $query->whereOr('channel_id', 'in', $childrenIds);
                    }
                }
            })
                ->whereOr("(`channel_ids`!='' AND FIND_IN_SET('{$channel['id']}', `channel_ids`))");
        };

        //模板名称
        $template = ($this->request->isAjax() ? '/ajax/' : '/') . ($channel["{$channel['type']}tpl"] ?? '');
        $template = preg_replace('/\.html$/', '', $template);

        $pagelistParams = Service::getPagelistParams($template);
        //分页大小
        $pagesize = $pagelistParams['pagesize'] ?? $channel['pagesize'];
        //过滤条件
        $filterPagelist = function ($query) use ($pagelistParams) {
            if (isset($pagelistParams['condition'])) {
                $query->where($pagelistParams['condition']);
            }
        };

        //分页模式
        $simple = $config['loadmode'] == 'paging' && $config['pagemode'] == 'full' ? false : true;

        //缓存列表总数
        if (!$simple && ($config['cachelistcount'] ?? false)) {
            $simple = Archives::with(['channel', 'user'])->alias('a')
                ->where('a.status', 'normal')
                ->whereNull('a.deletetime')
                ->where($filterWhere)
                ->bind($filterBind)
                ->where($filterPagelist)
                ->where($filterChannel)
                ->where('model_id', $channel->model_id)
                ->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
                ->cache("cms-channel-list-" . $channel['id'] . '-' . md5(serialize($filter)), 3600) //总数缓存1小时
                ->count();
        }

        //加载列表数据
        $pageList = Archives::with(['channel', 'user'])->alias('a')
            ->where('a.status', 'normal')
            ->whereNull('a.deletetime')
            ->where($filterWhere)
            ->bind($filterBind)
            ->where($filterPagelist)
            ->where($filterChannel)
            ->where('model_id', $channel->model_id)
            ->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
            ->field('a.*')
            ->field('id,content', true, config('database.prefix') . $model['table'], 'n')
            ->order($orderby, $orderway)
            ->paginate($pagesize, $simple);

        Service::appendTextAndList('model', $model->id, $pageList, true);

        Service::appendTextAndList('channel', 0, $channel);

        $pageList->appends(array_filter($params));
        $this->view->assign("__FILTERLIST__", $filterList);
        $this->view->assign("__ORDERLIST__", $orderList);
        $this->view->assign("__PAGELIST__", $pageList);
        $this->view->assign("__CHANNEL__", $channel);

        SpiderLog::record('channel', $channel['id']);

        //设置TKD
        Config::set('cms.title', isset($channel['seotitle']) && $channel['seotitle'] ? $channel['seotitle'] : $channel['name']);
        Config::set('cms.keywords', $channel['keywords']);
        Config::set('cms.description', $channel['description']);

        //读取模板
        if (!$template) {
            $this->error('请检查栏目是否配置相应的模板');
        }

        if ($this->request->isAjax()) {
            $this->success("", "", $this->view->fetch($template));
        }

        return $this->view->fetch($template);
    }
}
