<?php

namespace addons\ask\controller;

use addons\ask\library\OrderException;
use addons\ask\library\Service;
use think\Db;
use think\Exception;

class Thanks extends Base
{

    protected $layout = 'default';

    public function _initialize()
    {
        parent::_initialize();

        if (!$this->request->isPost()) {
            $this->error("请求错误");
        }
    }

    /**
     * 感谢
     */
    public function create()
    {
        $source_id = $this->request->request('id');
        $type = $this->request->request('type');
        $money = $this->request->request('money/f');
        $paytype = $this->request->request('paytype');
        $content = $this->request->request('content');

        if ($content && !Service::isContentLegal($content)) {
            $this->error("内容含有非法关键字");
        }
        $model = Service::getModelByType($type, $source_id);
        if ($model && $model->user_id == $this->auth->id) {
            $this->error("你不能感谢你自己");
        }
        if ($money <= 0) {
            $this->error("感谢金额不正确");
        }
        $thanks = \addons\ask\model\Thanks::where('user_id', $this->auth->id)
            ->where('type', $type)
            ->where('source_id', $source_id)
            ->where('money', $money)
            ->where('status', 'created')
            ->whereTime('createtime', '-30 minutes')
            ->find();
        if (!$thanks) {
            Db::startTrans();
            try {
                $data = [
                    'user_id'   => $this->auth->id,
                    'type'      => $type,
                    'source_id' => $source_id,
                    'money'     => $money,
                    'paytype'   => $paytype,
                    'content'   => $content,
                    'status'    => 'created',
                ];
                $thanks = \addons\ask\model\Thanks::create($data, true);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error("操作失败");
            }
        }
        try {
            $response = \addons\ask\library\Order::submit('thanks', $thanks->id, $money, $paytype ? $paytype : 'wechat', '感谢费用');
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage());
            } else {
                $this->error($e->getMessage());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        return $response;
    }

}
