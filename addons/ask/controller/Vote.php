<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use think\Db;
use think\Exception;

class Vote extends Base
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
     * 添加
     */
    public function create()
    {
        $source_id = $this->request->request('id');
        $type = $this->request->request('type');
        $value = $this->request->request('value');

        $model = Service::getModelByType($type, $source_id);
        if (!$model) {
            $this->error("关联文档未找到!");
        }
        if ($model->user_id == $this->auth->id) {
            $this->error("你无法给自己投票");
        }
        $vote = \addons\ask\model\Vote::where(['user_id' => $this->auth->id, 'type' => $type, 'source_id' => $source_id])->find();
        Db::startTrans();
        try {
            $data = [
                'user_id'   => $this->auth->id,
                'type'      => $type,
                'source_id' => $source_id,
                'value'     => $value,
            ];
            if ($vote) {
                $vote->value = $value;
                $vote->save();
            } else {
                (new \addons\ask\model\Vote())->allowField(true)->save($data);
            }
            if ($vote) {
                Db::name("ask_{$type}")->where('id', $source_id)->setDec("vote" . ($value == 'up' ? 'down' : 'up'));
            }
            Db::name("ask_{$type}")->where('id', $source_id)->setInc("vote{$value}");

            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("失败" . $e->getMessage());
        }
        $this->success('成功', '', ['voteup' => Db::name("ask_{$type}")->where('id', $source_id)->value('voteup')]);
    }

    /**
     * 取消
     */
    public function delete()
    {
        $source_id = $this->request->request('id');
        $type = $this->request->request('type');
        $value = $this->request->request('value');
        $vote = \addons\ask\model\Vote::where(['user_id' => $this->auth->id, 'type' => $type, 'source_id' => $source_id])->find();
        Db::startTrans();
        try {
            Db::name("ask_{$type}")->where('id', $source_id)->setDec("vote{$value}");
            $vote->delete();
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error("取消失败");
        }
        $this->success('取消成功', '', ['voteup' => Db::name("ask_{$type}")->where('id', $source_id)->value('voteup')]);
    }
}
