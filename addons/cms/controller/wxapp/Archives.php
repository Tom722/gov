<?php

namespace addons\cms\controller\wxapp;

use addons\cms\library\Order;
use addons\cms\library\OrderException;
use addons\cms\library\Service;
use addons\cms\model\Archives as ArchivesModel;
use addons\cms\model\Channel;
use addons\cms\model\Comment;
use addons\cms\model\Fields;
use addons\cms\model\Modelx;
use addons\epay\library\Collection;
use addons\third\model\Third;
use think\Exception;

/**
 * 文档
 */
class Archives extends Base
{
    protected $noNeedLogin = ['index', 'detail'];

    public function _initialize()
    {
        parent::_initialize();
        //检测ID是否加密
        $this->hashids();
    }

    /**
     * 读取文档列表
     */
    public function index()
    {
        $config = get_addon_config('cms');
        $params = [];
        $model = (int)$this->request->request('model');
        $channel = (int)$this->request->request('channel');
        $page = (int)$this->request->request('page');

        if ($model) {
            $params['model'] = $model;
        }
        if ($channel) {
            $params['channel'] = $channel;
        }
        $page = max(1, $page);
        $params['limit'] = ($page - 1) * 10 . ',10';
        $params['cache'] = 0;

        if ($channel) {
            $channelInfo = Channel::get($channel);
            if ($channelInfo) {
                $channelIds = Channel::where(function ($query) use ($channelInfo) {
                    if ($channelInfo['listtype'] <= 2) {
                        $query->whereOr("id", $channelInfo['id']);
                    }
                    if ($channelInfo['listtype'] == 1 || $channelInfo['listtype'] == 3) {
                        $query->whereOr('id', 'in', function ($query) use ($channelInfo) {
                            $query->name("cms_channel")->where('parent_id', $channelInfo['id'])->field("id");
                        });
                    }
                    if ($channelInfo['listtype'] == 0 || $channelInfo['listtype'] == 4) {
                        $childrenIds = \addons\cms\model\Channel::getChannelChildrenIds($channelInfo['id'], false);
                        if ($childrenIds) {
                            $query->whereOr('id', 'in', $childrenIds);
                        }
                    }
                })->column('id');
                $params['channel'] = $channelIds;
            }
        }
        $list = ArchivesModel::getArchivesList($params);

        foreach ($list as $index => $item) {
            if ($item->channel) {
                $item->channel->visible(explode(',', 'id,parent_id,name,image,diyname,items'));
            }
            $item->id = $config['archiveshashids'] ? $item->eid : $item->id;
        }
        $list = collection($list)->toArray();
        foreach ($list as $index => &$item) {
            $item['url'] = $item['fullurl'];
            //小程序只显示3张图
            $item['images_list'] = array_slice(array_filter(explode(',', $item['images'])), 0, 3);
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['taglist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img'], $item['admin_id']);
        }
        $this->success('', ['archivesList' => $list]);
    }

    /**
     * 文档详情
     */
    public function detail()
    {
        $action = $this->request->post("action");
        if ($action && $this->request->isPost()) {
            return $this->$action();
        }
        $diyname = $this->request->param('diyname');
        if ($diyname && !is_numeric($diyname)) {
            $archives = ArchivesModel::getByDiyname($diyname);
        } else {
            $id = $this->request->param('id', '');
            $archives = ArchivesModel::get($id);
        }
        if (!$archives || ($archives['status'] != 'normal' && (!$archives['user_id'] || $archives['user_id'] != $this->auth->id)) || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        if (!$this->auth->id && !$archives['isguest']) {
            $this->error(__('Please login first'));
        }
        $channel = Channel::get($archives['channel_id']);
        if (!$channel) {
            $this->error(__('No specified channel found'));
        }
        $model = Modelx::get($channel['model_id']);
        if (!$model) {
            $this->error(__('No specified model found'));
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
        $archives->id = $archives->eid;
        $archives->hidden(['admin_id', 'deletetime', 'status', 'memo', 'weigh', 'special_ids', 'channel']);

        $commentList = Comment::getCommentList(['aid' => $archives['id']]);
        $commentList = $commentList->getCollection();
        foreach ($commentList as $index => &$item) {
            if ($item->user) {
                $item->user->avatar = cdnurl($item->user->avatar, true);
                $item->user->visible(explode(',', 'id,nickname,avatar,bio'));
            }
            $item->hidden(['ip', 'useragent', 'deletetime', 'aid', 'subscribe', 'status', 'type', 'updatetime']);
        }
        $this->request->token();
        $channel = $channel->toArray();
        $channel['url'] = $channel['fullurl'];
        unset($channel['channeltpl'], $channel['listtpl'], $channel['showtpl'], $channel['status'], $channel['weigh'], $channel['parent_id']);
        $this->success('', ['archivesInfo' => $archives, 'channelInfo' => $channel, 'commentList' => $commentList]);
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
        if (!$archives || ($archives['user_id'] != $this->auth->id && $archives['status'] != 'normal') || $archives['deletetime']) {
            $this->error(__('No specified article found'));
        }
        $archives->where('id', $id)->setInc($type === 'like' ? 'likes' : 'dislikes', 1);
        $archives = ArchivesModel::get($id);
        $this->success(__('Operation completed'), ['likes' => $archives->likes, 'dislikes' => $archives->dislikes, 'likeratio' => $archives->likeratio]);
    }

    /**
     * 提交订单
     */
    public function order()
    {
        $id = $this->request->post('id/d');
        $third = Third::where('platform', 'wechat')->where('apptype', 'miniapp')->where('user_id', $this->auth->id)->find();
        if (!$third) {
            $this->error("未找到登录用户信息");
        }
        $openid = $third['openid'];
        $archives = \addons\cms\model\Archives::get($id);
        if (!$archives) {
            $this->error("文档未找到");
        }
        //优先使用余额的方式发起支付
        $paid = false;
        try {
            $response = Order::submit($id, 'balance');
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $paid = true;
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        if ($paid) {
            $this->success("余额支付成功", null);
        }

        //以微信小程序应用内支付的方式发起支付
        try {
            $response = Order::submit($id, 'wechat', 'miniapp', $openid);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        //如果是Collection则需要转换为数组
        $data = $response instanceof Collection ? $response->toArray() : (string)$response;

        $this->success("请求成功", $data);

        return;
    }
}
