<?php

namespace app\admin\controller\ask;

use app\common\controller\Backend;

/**
 * 问答统计管理
 *
 * @icon fa fa-bar-chart
 * @remark 可以查看订单相关统计信息
 */
class Statistics extends Backend
{

    /**
     * 模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 查询统计
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $date = $this->request->post('date', '');
        $data = $this->getOrderStatisticsData($date);
        $statistics = ['columns' => array_keys($data), 'data' => array_values($data), 'amount' => sprintf("%.2f", array_sum(array_values($data)))];

        //订单金额数
        $statistics['todayincome'] = floatval(\app\admin\model\ask\Order::whereTime('paytime', 'today')->sum('payamount'));
        $statistics['totalincome'] = floatval(\app\admin\model\ask\Order::where('paytime', '>', 0)->sum('payamount'));
        //订单数
        $statistics['todayorders'] = intval(\app\admin\model\ask\Order::whereTime('paytime', 'today')->count());
        $statistics['totalorders'] = intval(\app\admin\model\ask\Order::where('paytime', '>', 0)->count());
        //问题数
        $statistics['todayquestions'] = intval(\app\admin\model\ask\Question::whereTime('createtime', 'today')->count());
        $statistics['totalquestions'] = intval(\app\admin\model\ask\Question::where('createtime', '>', 0)->count());
        //文章数
        $statistics['todayarticles'] = intval(\app\admin\model\ask\Article::whereTime('createtime', 'today')->count());
        $statistics['totalarticles'] = intval(\app\admin\model\ask\Article::where('createtime', '>', 0)->count());

        //未认证数
        $statistics['totaluncertifications'] = intval(\app\admin\model\ask\Certification::where('status', '=', 'hidden')->count());;
        //未采纳数
        $statistics['totalunadopted'] = intval(\app\admin\model\ask\Question::where('best_answer_id', '=', 0)->count());;
        //未处理举报数
        $statistics['totalunreports'] = intval(\app\admin\model\ask\Report::where('status', '=', 'hidden')->count());
        //总文章数
        $statistics['totalanswers'] = intval(\app\admin\model\ask\Answer::where('createtime', '>', 0)->count());

        if ($this->request->isPost()) {
            $this->success('', '', $statistics);
        }
        $this->view->assign('statistics', $statistics);
        $this->assignconfig('statistics', $statistics);
        return $this->view->fetch();
    }

    /**
     * 获取订单统计数据
     * @param string $date
     * @return array
     */
    protected function getOrderStatisticsData($date = '')
    {
        if ($date) {
            list($start, $end) = explode(' - ', $date);

            $starttime = strtotime($start);
            $endtime = strtotime($end);
        } else {
            $starttime = \fast\Date::unixtime('day', 0, 'begin');
            $endtime = \fast\Date::unixtime('day', 0, 'end');
        }
        $totalseconds = $endtime - $starttime;

        $format = '%Y-%m-%d';
        if ($totalseconds > 86400 * 30 * 2) {
            $format = '%Y-%m';
        } else if ($totalseconds > 86400) {
            $format = '%Y-%m-%d';
        } else {
            $format = '%H:00';
        }
        $orderlist = \app\admin\model\ask\Order::where('paytime', 'between time', [$starttime, $endtime])
            ->field('paytime, status, COUNT(*) AS nums, SUM(payamount) AS amount, MIN(paytime) AS min_paytime, MAX(paytime) AS max_paytime, 
            DATE_FORMAT(FROM_UNIXTIME(paytime), "' . $format . '") AS order_date')
            ->group('order_date')
            ->select();

        if ($totalseconds > 84600 * 30 * 2) {
            $starttime = strtotime('last month', $starttime);
            while (($starttime = strtotime('next month', $starttime)) <= $endtime) {
                $column[] = date('Y-m', $starttime);
            }
        } else if ($totalseconds > 86400) {
            for ($time = $starttime; $time <= $endtime;) {
                $column[] = date("Y-m-d", $time);
                $time += 86400;
            }
        } else {
            for ($time = $starttime; $time <= $endtime;) {
                $column[] = date("H:00", $time);
                $time += 3600;
            }
        }
        $list = array_fill_keys($column, 0);
        $orderlist = collection($orderlist)->toArray();
        foreach ($orderlist as $k => $v) {
            $list[$v['order_date']] = $v['amount'];
        }
        return $list;

    }


}
