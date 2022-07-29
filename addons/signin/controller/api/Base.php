<?php

namespace addons\signin\controller\api;

use app\common\controller\Api;
use think\Lang;

/**
 * api基类
 */
class Base extends Api
{
    protected $noNeedLogin = []; // 无需登录即可访问的方法，同时也无需鉴权了
    protected $noNeedRight = ['*']; // 无需鉴权即可访问的方法

    protected $config;

    public function _initialize()
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header('Access-Control-Expose-Headers: __token__');//跨域让客户端获取到
        }
        //跨域检测
        check_cors_request();

        parent::_initialize();
        $this->config = get_addon_config('signin');
        Lang::load(APP_PATH . '../addons/signin/lang/zh-cn.php');
    }
}
