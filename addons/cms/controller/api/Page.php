<?php

namespace addons\cms\controller\api;

/**
 * 单页面
 */
class Page extends Base
{
    protected $noNeedLogin = ['detail'];

    /**
     * 单页详情
     */
    public function detail()
    {
        $name = $this->request->request("diyname");
        $pageInfo = \addons\cms\model\Page::getByDiyname($name);
        if (!$pageInfo || $pageInfo['status'] != 'normal') {
            $this->error(__('单页未找到'));
        }
        unset($pageInfo['status'], $pageInfo['showtpl'], $pageInfo['parsetpl']);
        $view = new \think\View();
        $pageInfo['content'] = $view->fetch($pageInfo['content'], ["__PAGE__" => $pageInfo], [], [], true);

        $this->success('', ['pageInfo' => $pageInfo->toArray()]);
    }

}
