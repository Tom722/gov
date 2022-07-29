<?php

namespace addons\ask\library;

use addons\ask\model\Answer;
use addons\ask\model\Article;
use addons\ask\model\Thanks;
use app\common\library\Auth;
use app\common\model\User;
use fast\Http;
use fast\Random;
use think\Db;
use think\Exception;
use think\Hook;
use think\Request;
use think\View;

class Order
{

    /**
     * 获取查询条件
     * @return \Closure
     */
    protected static function getQueryCondition()
    {
        $condition = function ($query) {

            $auth = Auth::instance();
            $user_id = $auth->isLogin() ? $auth->id : 0;
            $ip = Request::instance()->ip(0, false);

            $config = get_addon_config('ask');
            //如果开启支付需要登录，则只判断user_id
            if ($config['ispaylogin']) {
                $query->where('user_id', $user_id);
            } else {
                if ($user_id) {
                    $query->whereOr('user_id', $user_id)->whereOr('ip', $ip);
                } else {
                    $query->where('user_id', 0)->where('ip', $ip);
                }
            }
        };
        return $condition;
    }

    /**
     * 检查订单
     * @param string $type
     * @param mixed  $source_id
     * @return bool
     * @throws Exception
     */
    public static function check($type, $source_id)
    {
        $model = Service::getModelByType($type, $source_id);
        if (!$model) {
            return false;
        }
        $where = [
            'type'      => $type,
            'source_id' => $source_id,
            'status'    => 'paid',
        ];

        //匹配已支付订单
        $order = \addons\ask\model\Order::where($where)->where(self::getQueryCondition())->order('id', 'desc')->find();
        return $order ? true : false;
    }

    /**
     * 发起订单支付
     *
     * @param string $type      类型
     * @param int    $source_id 来源ID
     * @param float  $amount    金额
     * @param string $paytype   支付类型
     * @param string $title     订单标题
     * @param string $currency  货币 money=金额,score=积分
     * @param string $method    支付方法
     * @param string $openid    Openid
     * @param string $notifyurl 通知地址
     * @param string $returnurl 返回地址
     * @return \addons\epay\library\Collection|\addons\epay\library\RedirectResponse|\addons\epay\library\Response|null
     * @throws OrderException
     */
    public static function submit($type, $source_id, $amount, $paytype = 'wechat', $title = '', $currency = 'money', $method = 'web', $openid = '', $notifyurl = '', $returnurl = '')
    {
        $order = \addons\ask\model\Order::where('type', $type)
            ->where('source_id', $source_id)
            ->where(self::getQueryCondition())
            ->order('id', 'desc')
            ->find();
        if ($order && $order['status'] == 'paid') {
            throw new OrderException('订单已支付');
        }
        $currency = $currency ? $currency : 'money';
        $auth = Auth::instance();
        $request = Request::instance();
        $user_id = $auth->id ? $auth->id : 0;
        $title = $title ? $title : '支付';

        $orderid = date("YmdHis") . sprintf("%06d", $user_id) . mt_rand(1000, 9999);
        if (!$order) {
            $data = [
                'user_id'   => $user_id,
                'type'      => $type,
                'orderid'   => $orderid,
                'source_id' => $source_id,
                'title'     => $title,
                'amount'    => $amount,
                'payamount' => 0,
                'currency'  => $currency,
                'paytype'   => $paytype,
                'method'    => $method,
                'ip'        => $request->ip(0, false),
                'useragent' => substr($request->server('HTTP_USER_AGENT', ''), 0, 255),
                'status'    => 'created'
            ];
            $order = \addons\ask\model\Order::create($data);
        } else {
            //支付方式变更
            if (($order['method'] && $order['paytype'] == $paytype && $order['method'] != $method)) {
                $orderid = date("Ymdhis") . sprintf("%06d", $user_id) . mt_rand(1000, 9999);
                $order->save(['orderid' => $orderid]);
            }

            //更新支付类型和方法
            $order->save(['paytype' => $paytype, 'method' => $method]);

            if ($order->amount != $amount) {
                $order->amount = $amount;
                $order->save();
            }
        }
        $paytype = $currency == 'score' ? 'balance' : $paytype;

        //使用余额支付
        if ($paytype == 'balance') {
            if (!$auth->id) {
                throw new OrderException('需要登录后才能够支付');
            }
            if ($currency == 'money' && $auth->money < $amount) {
                throw new OrderException('余额不足，无法进行支付');
            }
            if ($currency == 'score' && $auth->score < $amount) {
                throw new OrderException('积分不足，无法进行支付');
            }
            Db::startTrans();
            try {
                if ($currency == 'money') {
                    User::money(-$amount, $auth->id, $title);
                }
                if ($currency == 'score') {
                    User::score(-$amount, $auth->id, $title);
                }
                self::settle($order->orderid);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                throw new OrderException($e->getMessage());
            }
            throw new OrderException('支付成功', 1);
        }

        $response = null;
        $epay = get_addon_info('epay');

        if ($epay && $epay['state']) {
            $notifyurl = $notifyurl ? $notifyurl : addon_url('ask/order/epay', [], false, true) . '/type/notify/paytype/' . $paytype;
            $returnurl = $returnurl ? $returnurl : addon_url('ask/order/epay', [], false, true) . '/type/return/paytype/' . $paytype;

            $params = [
                'amount'    => $order['amount'],
                'orderid'   => $order['orderid'],
                'type'      => $paytype,
                'title'     => $title,
                'notifyurl' => $notifyurl,
                'returnurl' => $returnurl,
                'method'    => $method,
                'openid'    => $openid
            ];

            $response = \addons\epay\library\Service::submitOrder($params);
        } else {
            $result = Hook::listen('ask_order_submit', $order);
            if (!$result) {
                throw new OrderException("请先在后台安装配置微信支付宝整合插件");
            }
        }
        return $response;
    }

    /**
     * 订单结算
     * @param mixed  $orderid   订单号
     * @param mixed  $payamount 金额
     * @param string $memo      备注
     * @return bool
     */
    public static function settle($orderid, $payamount, $memo = '')
    {
        $order = \addons\ask\model\Order::getByOrderid($orderid);
        if (!$order) {
            return false;
        }
        if ($order['status'] != 'paid') {
            if ($payamount != $order->amount) {
                \think\Log::write("[ask][pay][{$orderid}][订单支付金额不一致]");
                return false;
            }
            $order->payamount = $payamount;
            $order->paytime = time();
            $order->status = 'paid';
            $order->memo = $memo;
            $order->save();

            if ($order->payamount == $order->amount) {
                if ($order['type'] == 'thanks') {
                    Thanks::notify($order);
                } elseif ($order['type'] == 'answer') {
                    Answer::notify($order);
                } elseif ($order['type'] == 'article') {
                    Article::notify($order);
                }
            }
        }
        return true;
    }
}
