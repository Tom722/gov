<?php

namespace addons\ask\controller;

use addons\ask\library\Service;

/**
 * 举报控制器
 * Class Report
 * @package addons\ask\controller
 */
class Report extends Base
{
    protected $layout = 'default';

    /**
     * 举报
     */
    public function create()
    {
        if ($this->request->isPost()) {
            $source_id = $this->request->request('id');
            $type = $this->request->request('type');
            $content = $this->request->request('content');
            $reason = $this->request->request('reason');
            if (!$source_id || !$type || !$reason) {
                $this->error("参数不正确");
            }
            $ip = $this->request->ip(0, false);
            $lastReport = \addons\ask\model\Report::where([
                'type'      => $type,
                'source_id' => $source_id,
                'ip'        => $ip,
            ])->find();
            if ($lastReport) {
                $this->error("你已经提交到反馈，无需重复操作");
            }
            $data = [
                'user_id'   => $this->auth->id,
                'type'      => $type,
                'source_id' => $source_id,
                'reason'    => $reason,
                'ip'        => $ip,
                'useragent' => substr($this->request->server('HTTP_USER_AGENT'), 0, 255),
                'content'   => $content,
                'status'    => 'hidden',
            ];
            \addons\ask\model\Report::create($data);
            $model = Service::getModelByType($type, $source_id);
            if ($model) {
                $config = get_addon_config('ask');
                $model->setInc("reports");
                //超过阀值时隐藏
                if ($model->reports >= $config['reportlimit']) {
                    $hidden = true;
                    if (in_array($type, ['question', 'article', 'answer']) && $model->thanks > 0) {
                        $hidden = false;
                    } else if ($type == 'answer' && $model->voteup > $config['reportlimit']) {
                        $hidden = false;
                    }
                    if ($hidden) {
                        $model->save(['status' => 'hidden']);
                    }
                }
            }
            $this->success("消息发送成功");
        }
        return;
    }

}
