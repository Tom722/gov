<?php

namespace addons\cms\controller\api;

use addons\cms\library\Order;
use addons\cms\library\OrderException;
use addons\cms\model\Archives as ArchivesModel;
use addons\cms\model\Channel;
use addons\cms\model\Comment;
use addons\cms\model\Fields;
use addons\cms\model\Modelx;
use addons\cms\library\Service;
use addons\third\model\Third;
use fast\Tree;
use think\Db;
use think\Validate;
use app\common\model\User;
use addons\cms\library\Theme;

/**
 * 文档
 */
class Archives extends Base
{
    protected $noNeedLogin = ['index', 'detail', 'get_channel'];

    public function _initialize()
    {
        parent::_initialize();
        //检测ID是否加密
        $this->hashids();
    }

    public function index()
    {
        $config = get_addon_config('cms');

        $diyname = $this->request->param('diyname');
        $model_id = (int)$this->request->param('model');
        $channel_id = (int)$this->request->param('channel');
        $menu_index = (int)$this->request->param('menu_index/d', 0);
        //对于首页，应用初始化，无法给传参，默认传-1，取导航的设置参数
        if ($model_id == -1) {
            $model_id = Theme::getFirstParam('model', $menu_index);
        }
        if ($channel_id == -1) {
            $channel_id = Theme::getFirstParam('channel', $menu_index);
        }
        $channel = null;
        //取栏目
        if ($diyname && !is_numeric($diyname)) {
            $channel = Channel::getByDiyname($diyname);
        } else {
            $id = $diyname ? $diyname : ($channel_id ? $channel_id : $this->request->param('id', ''));
            $channel = Channel::get($id);
        }
        if ($model_id && !$channel) {
            $channel = Channel::where('type', 'channel')->where('parent_id', 0)->where('model_id', $model_id)->find();
        }
        if (!$channel) {
            $this->getArchivesIndex();
            return;
        }
        $channel['image'] = cdnurl($channel['image'], true);

        $filter = $this->request->get('filter/a', []);
        $orderby = $this->request->get('orderby', '');
        $orderway = $this->request->get('orderway', '', 'strtolower');
        $multiple = $this->request->get('multiple/d', 0);

        $params = [];
        $filter = $this->request->get();
        $filter = array_diff_key($filter, array_flip(['orderby', 'orderway', 'page', 'multiple']));
        if (isset($filter['filter'])) {
            $filter = array_merge($filter, $filter['filter']);
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
            // $this->redirect($channel['outlink']);
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

        //加载列表数据
        $pageList = ArchivesModel::with(['channel', 'user'])->alias('a')
            ->where('a.status', 'normal')
            ->whereNull('a.deletetime')
            ->where($filterWhere)
            ->bind($filterBind)
            ->join($model['table'] . ' n', 'a.id=n.id', 'LEFT')
            ->field('a.*')
            ->field('id,content', true, config('database.prefix') . $model['table'], 'n')
            ->where(function ($query) use ($channel) {
                if ($channel['status'] != 'normal') {
                    $query->where('channel_id', -1);
                } else {
                    $query->where(function ($query) use ($channel) {
                        if ($channel['listtype'] <= 2) {
                            $query->whereOr("channel_id", $channel['id']);
                        }
                        if ($channel['listtype'] == 1 || $channel['listtype'] == 3) {
                            $query->whereOr('channel_id', 'in', function ($query) use ($channel) {
                                $query->name("cms_channel")->where('parent_id', $channel['id'])->where('status', 'normal')->field("id");
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
                }
            })
            ->where('model_id', $channel->model_id)
            ->order($orderby, $orderway)
            ->paginate($channel['pagesize'], $config['pagemode'] == 'simple');

        Service::appendTextAndList('model', $model->id, $pageList, true);

        Service::appendTextAndList('channel', 0, $channel);

        $pageList->appends(array_filter($params));

        foreach ($pageList as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }

            if ($item->user) {
                $item->user->visible(explode(',', 'id,nickname,avatar'));
            }

            //小程序只显示9张图
            $item['images_list'] = array_slice(array_filter(explode(',', $item['images'])), 0, 9);

            //隐藏无关字段
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['tagslist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img'], $item['admin_id']);

            $item->id = $config['archiveshashids'] ? $item->eid : $item->id;
        }
        $this->success('获取成功', [
            'pageList'   => $pageList,
            'filterList' => $filterList,
            'orderList'  => $orderList,
            'channel'    => $channel
        ]);
    }


    /**
     * 读取文档列表
     */
    private function getArchivesIndex()
    {
        $config = get_addon_config('cms');
        $params = [];
        $model = (int)$this->request->param('model');
        $channel = (int)$this->request->param('channel');
        $page = (int)$this->request->param('page');
        $type = $this->request->param('type');
        $params['orderby'] = $this->request->param('orderby', 'weigh');
        $menu_index = (int)$this->request->param('menu_index/d', 0);
        //对于首页，应用初始化，无法给传参，默认传-1，取导航的设置参数
        if ($model == -1) {
            $model = Theme::getFirstParam('model', $menu_index);
        }
        if ($channel == -1) {
            $channel = Theme::getFirstParam('channel', $menu_index);
        }
        if ($model) {
            $params['model'] = $model;
        }
        if ($channel) {
            $params['channel'] = $channel;
        }
        if ($type) {
            $params['type'] = $type;
        }

        $page = max(1, $page);
        $params['page'] = $page;
        $params['cache'] = 0;

        if (isset($params['channel']) && $params['channel']) {

            $channelInfo = Channel::get($params['channel']);
            if ($channelInfo && $channelInfo['status'] == 'normal') {
                $channelIds = Channel::where(function ($query) use ($channelInfo) {
                    if ($channelInfo['listtype'] <= 2) {
                        $query->whereOr("id", $channelInfo['id']);
                    }
                    if ($channelInfo['listtype'] == 1 || $channelInfo['listtype'] == 3) {
                        $query->whereOr('id', 'in', function ($query) use ($channelInfo) {
                            $query->name("cms_channel")->where('parent_id', $channelInfo['id'])->where('status', 'normal')->field("id");
                        });
                    }
                    if ($channelInfo['listtype'] == 0 || $channelInfo['listtype'] == 4) {
                        $childrenIds = \addons\cms\model\Channel::getChannelChildrenIds($channelInfo['id'], false);
                        if ($childrenIds) {
                            $query->whereOr('id', 'in', $childrenIds);
                        }
                    }
                })->where('status', 'normal')->column('id');

            } else {
                $channelIds = [-1];
            }
            $params['channel'] = implode(',', $channelIds);
        }
        $params['paginate'] = 10;
        $list = ArchivesModel::getArchivesList($params);
        foreach ($list as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }

            if ($item->user) {
                $item->user->visible(explode(',', 'id,nickname,avatar'));
            }

            //小程序只显示9张图
            $item['images_list'] = array_slice(array_filter(explode(',', $item['images'])), 0, 9);

            //隐藏无关字段
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['tagslist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img'], $item['admin_id']);

            $item->id = $config['archiveshashids'] ? $item->eid : $item->id;
        }
        $this->success('', [
            'pageList'   => $list,
            'filterList' => [],
            'orderList'  => [],
            'channel'    => []
        ]);
    }


    /**
     * 文档详情
     */
    public function detail()
    {
        $diyname = $this->request->post('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $archives = ArchivesModel::with([
                'user' => function ($query) {
                    $query->field('id,nickname,avatar,bio');
                }
            ])->getByDiyname($diyname);
        } else {
            $id = $this->request->param('id', '');
            $archives = ArchivesModel::get($id, [
                'user' => function ($query) {
                    $query->field('id,nickname,avatar,bio');
                }
            ]);
        }
        if (!$archives || ($archives['status'] != 'normal' && (!$archives['user_id'] || $archives['user_id'] != $this->auth->id)) || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        if (!$this->auth->id && !$archives['isguest']) {
            $this->error(__('Please login first'));
        }
        $channel = Channel::get($archives['channel_id']);
        if (!$channel || $channel['status'] != 'normal') {
            $this->error(__('No specified channel found'));
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
        }
        if ($archives['user']) {
            $archives['user']['avatar'] = cdnurl($archives['user']['avatar'], true);
        }
        $archives->setInc("views", 1);
        $addon = db($model['table'])->where('id', $archives['id'])->find();
        if ($addon) {
            if ($model->fields) {
                Service::appendTextAndList('model', $model->id, $addon);
            }
            $archives->setData($addon);
        } else {
            $this->error(__('No specified article addon found'));
        }

        //小程序付费阅读将不可见
        $content = $archives->content;
        if ($archives->is_paid_part_of_content || $archives->ispaid) {
            $value = $archives->getData('content');
            $pattern = '/<paid>(.*?)<\/paid>/is';
            if (preg_match($pattern, $value) && !$archives->ispaid) {
                $value = preg_replace($pattern, "<div class='alert alert-warning' style='background:#fcf8e3;border:1px solid #faf3cd;color:#c09853;padding:8px;'>付费内容已经隐藏，请付费后查看</div>", $value);
            }
            $content = $value;
        } else {
            if (!$archives->ispaid) {
                if (isset($channel['vip']) && $channel['vip'] > $this->auth->vip) {
                    $paytips = "此文章为付费文章，需要VIP {$channel['vip']}" . ($archives->price > 0 ? "或支付￥{$archives->price}元" : "") . "才能查看";
                } else {
                    $paytips = "此文章为付费文章，需要支付￥{$archives->price}元才能查看";
                }
                $content = "<div class='alert alert-warning alert-paid' style='background:#fcf8e3;border:1px solid #faf3cd;color:#c09853;padding:8px;'>{$paytips}</div>";
            }
        }

        if (isset($archives['downloadurl'])) {
            //$archives['downloadurl'] = is_array($archives['downloadurl']) ? $archives['downloadurl'] : (array)json_decode($archives['downloadurl'], true);
            $archives['downloadurl'] = $archives['downloadurl_list'];
            unset($archives['downloadurl_text']);
        }
        if (!$archives->ispaid && isset($archives['downloadurl'])) {
            $archives['downloadurl'] = [];
        }

        //小程序不支持内容页分页
        $content = str_replace("##pagebreak##", "<br>", $content);
        $archives->content = $content;

        $commentList = Comment::getCommentList(['aid' => $archives['id']]);
        $commentList = $commentList->getCollection();

        foreach ($commentList as $index => &$item) {
            if ($item['user']) {
                $item['user']['avatar'] = cdnurl($item['user']['avatar'], true);
            }
            $item->hidden(['ip', 'useragent', 'deletetime', 'aid', 'subscribe', 'status', 'type', 'updatetime']);
        }

        $channel = $channel->toArray();
        $channel['url'] = $channel['fullurl'];

        foreach ($model->fields_list as $diy) {
            if ($diy['type'] == 'image' || $diy['type'] == 'file') {
                $archives[$diy['name']] = cdnurl($archives[$diy['name']], true);
            }
            if ($diy['type'] == 'images' || $diy['type'] == 'files') {
                if (isset($archives[$diy['name']])) {
                    $images = explode(',', $archives[$diy['name']]);
                    foreach ($images as &$img) {
                        $img = cdnurl($img, true);
                    }
                    unset($img);
                    $archives[$diy['name']] = $images;
                }
            }
        }
        unset($channel['channeltpl'], $channel['listtpl'], $channel['showtpl'], $channel['status'], $channel['weigh'], $channel['parent_id']);
        if ($archives->channel) {
            $archives->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
        }
        $archives['id'] = $archives['eid'];
        $this->success('', ['archivesInfo' => $archives, 'commentList' => $commentList, '__token__' => $this->request->token()]);
    }

    /**
     * 赞与踩
     */
    public function vote()
    {
        $id = (int)$this->request->post("id");
        $type = trim($this->request->post("type", ""));
        if (!$id || !$type) {
            $this->error(__('Operation failed'));
        }
        $archives = ArchivesModel::get($id);
        if (!$archives || ($archives['status'] != 'normal' && (!$archives['user_id'] || $archives['user_id'] != $this->auth->id)) || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $archives->where('id', $id)->setInc($type === 'like' ? 'likes' : 'dislikes', 1);
        $archives = ArchivesModel::get($id);
        $this->success(__('点赞成功！'), ['likes' => $archives->likes, 'dislikes' => $archives->dislikes, 'likeratio' => $archives->likeratio]);
    }

    /**
     * 提交订单
     */
    public function order()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误');
        }
        $id = $this->request->post('id/d');
        $paytype = $this->request->param('paytype');
        $method = $this->request->param('method');
        $appid = $this->request->param('appid'); //为APP的应用id
        $returnurl = $this->request->param('returnurl', '', 'trim');
        $openid = '';
        $archives = \addons\cms\model\Archives::get($id);
        if (!$archives || ($archives['status'] != 'normal' && (!$archives['user_id'] || $archives['user_id'] != $this->auth->id)) || $archives['deletetime']) {
            $this->error("文档未找到");
        }

        //非H5 支付,非余额
        if (in_array($method, ['miniapp', 'mp']) && $paytype == 'wechat') {
            $third = Third::where('platform', 'wechat')
                ->where('apptype', $method)
                ->where('user_id', $this->auth->id)
                ->find();

            if (!$third) {
                $this->error("未找到登录用户信息", 'bind');
            }
            $openid = $third['openid'];
        }

        try {
            $response = Order::submit($id, $paytype, $method, $openid, '', $returnurl);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage(), null);
            } else {
                $this->error($e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success("请求成功", $response);
    }

    /**
     * 获取栏目
     */
    public function get_channel()
    {
        $archives_id = $this->request->param('archives_id');
        $archives = $archives_id ? \app\admin\model\cms\Archives::get($archives_id) : null;
        $where = [];
        $channel_id = 0;
        $model = null;
        if ($archives) {
            $channel = Channel::get($archives['channel_id']);
            if (!$channel) {
                $this->error(__('未找到指定栏目'));
            }
            $model = \addons\cms\model\Modelx::get($channel['model_id']);
            if (!$model) {
                $this->error(__('未找到指定模型'));
            }
            if ($archives['user_id'] != $this->auth->id) {
                $this->error("无法进行越权操作！");
            }
            $where['model_id'] = $channel['model_id'];
            $channel_id = $archives['channel_id'];
        }

        list($channelList, $disabledIds) = Channel::getContributeInfo($archives, $model);

        $tree = Tree::instance()->init($channelList, 'parent_id');
        $data = $tree->getTreeList($tree->getTreeArray(0, false));
        foreach ($data as &$item) {
            $item['disabled'] = in_array($item['id'], $disabledIds);
        }
        $this->success('', [
            'channel'    => $data,
            'channel_id' => $channel_id
        ]);
    }

    /**
     * 获取栏目字段列表
     * @internal
     */
    public function get_channel_fields()
    {
        $channel_id = $this->request->param('channel_id');
        $archives_id = $this->request->param('archives_id');
        $channel = Channel::get($channel_id, 'model');

        if ($channel && $channel['type'] != 'link') {
            if ($channel['status'] != 'normal') {
                $this->error(__('No specified channel found'));
            }
            $values = [];
            //编辑获取数据
            $archives = $archives_id ? \app\admin\model\cms\Archives::get($archives_id) : null;
            if ($archives) {
                $archives->hidden(['admin_id', 'status', 'views']);
                $values = db($channel['model']['table'])->where('id', $archives_id)->find(); //编辑附表数据
            }
            $model = Modelx::get($channel['model_id']);
            //字段
            $fields = Service::getCustomFields('model', $channel['model_id'], $values, ['iscontribute' => 1]);
            $setting = $channel->getRelation('model')->setting ?? [];

            //副栏目数据
            list($channelList, $disabledIds) = Channel::getContributeInfo($archives, $model);
            $tree = Tree::instance()->init($channelList, 'parent_id');
            $data = $tree->getTreeList($tree->getTreeArray(0, false));

            $secondList = [];
            foreach ($data as &$item) {
                $item['disabled'] = in_array($item['id'], $disabledIds);
                if ($item['model_id'] == $channel['model_id']) {
                    $secondList[] = $item;
                }
            }

            $this->success('', [
                'contributefields' => $setting && isset($setting['contributefields']) ? $setting['contributefields'] : [],
                'fields'           => $fields,
                'values'           => $values,
                'archives'         => $archives,
                'secondList'       => $secondList
            ]);
        } else {
            $this->error(__('请选择栏目'));
        }
        $this->error(__('参数不能为空', 'ids'));
    }

    /**
     * 提交数据
     */
    public function archives_post()
    {
        // 如果来源于提交
        $config = get_addon_config('cms');
        if ($this->auth->score < $config['limitscore']['postarchives']) {
            $this->error("积分必须大于{$config['limitscore']['postarchives']}才可以发布文章");
        }
        $row = $this->request->post('', '', 'trim,xss_clean');

        $rule = [
            'title|标题'      => 'require|length:3,100',
            'channel_id|栏目' => 'require|integer',
        ];

        $msg = [
            'title.require'   => '标题不能为空',
            'title.length'    => '标题长度限制在3~100个字符',
            'channel_id'      => '栏目不能为空',
            'content.require' => '内容不能为空',
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($row);
        if (!$result) {
            $this->error($validate->getError());
        }

        //如果有传栏目名称
        if (isset($row['channel']) && $row['channel']) {
            $channel = Channel::where('name', $row['channel'])->find();
            if (!$channel || $channel['status'] != 'normal') {
                $this->error('栏目未找到');
            }
            $row['channel_id'] = $channel->id;
            unset($row['channel']);
        } else {
            $channel_id = (isset($row['channel_id']) && !empty($row['channel_id'])) ? $row['channel_id'] : $this->request->request('channel_id');
            $channel = Channel::get($channel_id);
            if (!$channel || $channel['status'] != 'normal') {
                $this->error('栏目未找到');
            }
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error('模型未找到');
        }

        $channelIds = isset($row['channel_ids']) ? (is_array($row['channel_ids']) ? $row['channel_ids'] : explode(',', $row['channel_ids'])) : [];
        $channelIds = array_merge([$channel['id']], $channelIds);
        $channelIds = array_filter($channelIds);
        $count = Channel::where('id', 'in', $channelIds)->where('iscontribute', 0)->count();
        if ($count > 0) {
            $this->error("栏目不允许投稿");
        }

        //审核状态
        $status = 'normal';
        if ($config['isarchivesaudit'] == 1) {
            $status = 'hidden';
        } elseif ($config['isarchivesaudit'] == 0) {
            $status = 'normal';
        } else {
            $textArr = array_map(function ($item) {
                return is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item;
            }, $row);
            if (!Service::isContentLegal(implode(' ', $textArr))) {
                $status = 'hidden';
            }
        }
        $row['user_id'] = $this->auth->id;
        $row['status'] = $status;
        $row['publishtime'] = time();

        //编辑的时候
        $archives = null;
        if (isset($row['id']) && !empty((int)$row['id'])) {
            $archives = \app\admin\model\cms\Archives::get($row['id']);
            if ($archives['user_id'] != $this->auth->id) {
                $this->error("无法进行越权操作！");
            }
        } else {
            unset($row['id']);
        }
        Db::startTrans();
        try {
            if ($archives) {
                $archives->allowField(true)->save($row);
            } else {
                (new \app\admin\model\cms\Archives)->allowField(true)->save($row);
            }
            //增加积分
            $status == 'normal' && User::score($config['score']['postarchives'], $this->auth->id, '发布文章');
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error("发生错误:" . $e->getMessage());
        }
        if ($status === 'hidden') {
            //发送通知
            $status === 'hidden' && Service::notice(config('cms.sitename') . '收到一篇新的文章审核');
            $this->success("提交成功！请等待审核!");
        } else {
            $this->success("发布成功！");
        }
    }

    /**
     * 我的发布
     */
    public function my()
    {
        $archives = new \addons\cms\model\Archives;
        $model = null;
        $model_id = (int)$this->request->request('model_id');
        $channel_id = (int)$this->request->request('channel_id');
        // 如果有model_id则调用指定模型
        if ($model_id) {
            $model = Modelx::get($model_id);
            if ($model) {
                $archives->where('model_id', $model_id);
            }
        }
        $config = ['query' => []];
        if ($model) {
            $config['query']['model_id'] = $model_id;
        }
        if ($channel_id) {
            $config['query']['channel_id'] = $channel_id;
        }
        $user_id = $this->auth->id;
        $archivesList = $archives->where('user_id', $user_id)
            ->where($config['query'])
            ->field('id,user_id,title,channel_id,dislikes,likes,tags,createtime,image,images,style,comments,views,diyname,status')
            ->order('id', 'desc')
            ->paginate(10, null, $config);

        $channelList = Channel::where('id', 'in', function ($query) use ($user_id) {
            $query->name('cms_archives')->where('user_id', $user_id)->field('channel_id');
        })->where('status', 'normal')->select();

        foreach ($archivesList as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }
            //小程序只显示9张图
            $item->images_list = array_slice(array_filter(explode(',', $item['images'])), 0, 9);
            unset($item['weigh'], $item['deletetime'], $item['memo']);
            $item->id = $item->eid;
        }

        $this->success('', [
            'archivesList' => $archivesList,
            'channelList'  => $channelList
        ]);
    }

    /**
     * 删除文档
     */
    public function delete()
    {
        if (!$this->request->isPost()) {
            $this->error('请求错误');
        }
        $id = (int)$this->request->post('id/d');
        if (!$id) {
            $this->error("参数不正确");
        }
        $archives = \addons\cms\model\Archives::where('id', $id)->where('user_id', $this->auth->id)->find();
        if (!$archives) {
            $this->error("未找到指定的文档");
        }
        Db::startTrans();
        try {
            $archives->delete();
            if ($archives->channel->items > 0) {
                $archives->channel->setDec("items");
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error("删除文档失败");
        }
        $this->success("删除文档成功");
    }
}
