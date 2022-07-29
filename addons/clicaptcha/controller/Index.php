<?php

namespace addons\clicaptcha\controller;

use addons\clicaptcha\library\Clicaptcha;
use think\addons\Controller;

class Index extends Controller
{

    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    /**
     * 初始化验证码
     */
    public function start()
    {
        $clicaptcha = new Clicaptcha();
        if ($this->request->post('do') == 'check') {
            echo $clicaptcha->check($this->request->post("info"), false) ? 1 : 0;
        } else {
            $config = get_addon_config('clicaptcha');
            $textArr = array_unique(array_filter(explode("\n", str_replace("\r\n", "\n", $config['customtext']))));
            $text = $textArr ? $textArr[array_rand($textArr)] : '';
            $clicaptcha->create($text);
        }
        return;
    }
}
