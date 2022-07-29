<?php

namespace addons\cms\controller\wxapp;

use addons\cms\library\IntCode;
use app\common\controller\Api;
use app\common\library\Auth;
use think\Config;
use think\Lang;

class Base extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    //设置返回的会员字段
    protected $allowFields = ['id', 'username', 'nickname', 'mobile', 'avatar', 'score', 'vip', 'level', 'bio', 'balance', 'money'];

    public function _initialize()
    {
        parent::_initialize();

        Config::set('default_return_type', 'json');

        $config = get_addon_config('cms');
        Auth::instance()->setAllowFields($this->allowFields);

        //判断站点状态
        if (isset($config['openedsite']) && !in_array('wxapp', explode(',', $config['openedsite']))) {
            $this->error('站点已关闭');
        }

        //这里手动载入语言包
        Lang::load(ROOT_PATH . '/addons/cms/lang/zh-cn.php');
        Lang::load(APP_PATH . '/index/lang/zh-cn/user.php');
    }

    /**
     * 判断ID是否加密处理
     */
    protected function hashids($name = 'id')
    {
        $config = get_addon_config('cms');
        $getValue = $this->request->get($name);
        $postValue = $this->request->post($name);
        if ($config['archiveshashids'] && ($getValue || $postValue)) {
            if ($getValue) {
                $getValue = (int)IntCode::decode($getValue);
                $this->request->get([$name => $getValue]);
            }
            if ($postValue) {
                $postValue = (int)IntCode::decode($postValue);
                $this->request->post([$name => $postValue]);
            }
            $this->request->param('');
        }
    }
}
