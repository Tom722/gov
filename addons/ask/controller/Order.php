<?php

namespace addons\ask\controller;

use addons\ask\library\OrderException;
use addons\ask\library\Service;
use think\Exception;

/**
 * 订单控制器
 * Class Order
 * @package addons\ask\controller
 */
class Order extends Base
{

    protected $layout = 'default';

    /**
     * 创建订单并发起支付请求
     * @throws \think\exception\DbException
     */
    public function submit()
    {
        $config = get_addon_config("ask");
        //是否需要登录后才可以支付
        if ($config['ispaylogin'] && !$this->auth->isLogin()) {
            $this->error("请登录后再进行操作!");
        }
        $id = $this->request->request('id');
        $type = $this->request->request('type');
        $paytype = $this->request->request('paytype', 'wechat');
        $currency = $this->request->request('currency', 'money');
        $model = Service::getModelByType($type, $id);
        if (!$model || $model['status'] == 'hidden') {
            $this->error("未到找对应的文档");
        }
        $amount = $currency == 'score' ? $model['score'] : $model['price'];
        if ($amount <= 0) {
            $this->error("提交金额不正确");
        }
        $title = '';
        if ($type == 'answer') {
            $title = '付费查看答案';
        } elseif ($type == 'article') {
            $title = '付费查看文章';
        }
        try {
            $response = \addons\ask\library\Order::submit($type, $id, $amount, $paytype, $title, $currency);
        } catch (OrderException $e) {
            if ($e->getCode() == 1) {
                $this->success($e->getMessage(), $this->request->server('http_referer'));
            } else if ($e->getCode() == 2) {
                $this->redirect($e->getData());
            } else {
                $this->error($e->getMessage());
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        return $response;
    }

    /**
     * 企业支付通知和回调
     * @throws \think\exception\DbException
     */
    public function epay()
    {
        $type = $this->request->param('type');
        $paytype = $this->request->param('paytype');
        if ($type == 'notify') {
            $pay = \addons\epay\library\Service::checkNotify($paytype);
            if (!$pay) {
                echo '签名错误';
                return;
            }
            $data = $pay->verify();
            try {
                $payamount = $paytype == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;
                \addons\ask\library\Order::settle($data['out_trade_no'], $payamount);
            } catch (Exception $e) {

            }
            echo $pay->success();
        } else {
            $pay = \addons\epay\library\Service::checkReturn($paytype);
            if (!$pay) {
                $this->error('签名错误');
            }
            //微信支付没有返回链接
            if ($pay === true) {
                $this->success("请返回网站查看支付状态!", "");
            }
            $data = $pay->verify();

            $order = \addons\ask\model\Order::get($data['out_trade_no']);
            if (!$order) {
                $this->error('订单未找到');
            }
            $this->success("恭喜你！支付成功!", $order->url);
        }
        return;
    }

    public function wechat()
    {
        $id = $this->request->request('id');
        $order = \addons\ask\model\Order::get($id);
        if (!$order) {
            $this->error('订单未找到');
        }
        if ($this->request->isAjax()) {
            if ($order['status'] == 'paid') {
                $model = Service::getModelByType($order['type'], $order['source_id']);
                if (!$model) {
                    $this->error("未找到文档");
                }
                $this->success("支付成功", $model->url);
            } else {
                $this->error("暂未支付");
            }
        }
        if ($order['status'] == 'paid') {
            $this->error('订单已支付，请勿重复支付');
        }

        $code_url = $this->request->request('code_url');

        $this->view->assign('title', "微信支付");
        $this->view->assign('__order__', $order);
        $this->view->assign('code_url', $code_url);
        return $this->view->fetch();
    }

}
