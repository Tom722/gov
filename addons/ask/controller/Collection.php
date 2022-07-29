<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use addons\ask\model\Feed;
use addons\ask\model\Notification;
use think\Db;
use think\Exception;

class Collection extends Base
{
    protected $noNeedLogin = [];
    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
    }

    /**
     * 添加收藏
     */
    public function create()
    {
        $source_id = $this->request->request('id');
        $type = $this->request->request('type');

        Db::startTrans();
        try {
            $data = [
                'user_id'   => $this->auth->id,
                'type'      => $type,
                'source_id' => $source_id,
            ];
            Db::name("ask_{$type}")->where($type == 'user' ? 'user_id' : 'id', $source_id)->setInc('collections');
            (new \addons\ask\model\Collection())->allowField(true)->save($data);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error($e->getCode() == 10501 ? "已收藏,请勿重复收藏" : "收藏失败");
        }
        try {
            $model = Service::getModelByType($type, $source_id);
            if ($model) {
                $title = isset($model['title']) ? $model['title'] : '';
                $title = $title ? $title : ($type == 'answer' ? $model->question->title : "");
                //更新动态
                Feed::record($title, '', 'collection', $type, $source_id, $this->auth->id);
                $to_user_id = $type == 'user' ? $source_id : (isset($model['user_id']) ? $model['user_id'] : 0);
                //发送通知
                //$to_user_id && Notification::record($title, '', 'collection', $type, $source_id, $to_user_id);
            }
        } catch (Exception $e) {

        }
        $this->success('收藏成功');
    }

    /**
     * 取消收藏
     */
    public function delete()
    {
        $source_id = $this->request->request('id');
        $type = $this->request->request('type');
        $attention = \addons\ask\model\Collection::where(['type' => $type, 'source_id' => $source_id, 'user_id' => $this->auth->id])->find();
        Db::startTrans();
        try {
            Db::name("ask_{$type}")->where($type == 'user' ? 'user_id' : 'id', $source_id)->setDec('collections');
            $attention->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("取消收藏失败");
        }
        $this->success('取消收藏成功');
    }
}
