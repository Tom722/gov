<?php

namespace addons\cms\controller\api;

use addons\cms\model\MoneyLog;
use addons\cms\model\ScoreLog;

/**
 * 会员日志
 */
class TheLogs extends Base
{
    protected $noNeedLogin = [];

    /**
     * 余额日志
     */
    public function money()
    {
        $list = MoneyLog::where(['user_id' => $this->auth->id])
            ->order('id desc')
            ->paginate(10);

        $this->success('', $list);
    }

    /**
     * 积分日志
     */
    public function score()
    {
        $list = ScoreLog::where(['user_id' => $this->auth->id])
            ->order('id desc')
            ->paginate(10);

        $this->success('', $list);
    }
}
