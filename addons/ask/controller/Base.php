<?php

namespace addons\ask\controller;

use addons\ask\library\Service;
use think\Config;
use think\Hook;
use think\Validate;

/**
 * 问答控制器基类
 */
class Base extends \think\addons\Controller
{

    // 初始化
    public function __construct()
    {
        parent::__construct();
        $config = get_addon_config('ask');
        // 加载自定义标签库
        //$this->view->engine->config('taglib_pre_load', 'addons\ask\taglib\Ask');
        // 定义问答首页的URL
        $config['indexurl'] = addon_url('ask/index/index', [], false);
        Config::set('ask', $config);

        Config::set('paginate.type', '\\addons\\ask\\library\\Bootstrap');

        if (Config::get('upload.uploadurl') == 'ajax/upload') {
            Config::set('upload.uploadurl', url('index/ajax/upload'));
        }

        //配置信息
        $upload = Config::get('upload');

        //如果非服务端中转模式需要修改为中转
        if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
            //临时修改上传模式为服务端中转
            set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

            $upload = \app\common\model\Config::upload();
            // 上传信息配置后
            Hook::listen("upload_config_init", $upload);

            $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
        }

        Config::set('paginate.query', $this->request->get());
        $url_domain_deploy = $config['domain'] && Config::get('url_domain_deploy');
        $askConfig = array_merge(['upload' => $upload, 'controllername' => $this->controller, 'actionname' => $this->action, 'url_domain_deploy' => $url_domain_deploy], Config::get("view_replace_str"));
        $askConfig = array_merge($askConfig, array_intersect_key($config, array_flip(['loadmode', 'pagesize', 'inviteprice', 'editormode', 'uploadparts', 'captchaparts', 'captchaparts'])));
        $askConfig['user'] = $this->auth->isLogin() ? array_intersect_key($this->auth->getUserinfo(), array_flip(['id', 'username', 'nickname', 'avatar', 'score', 'money'])) : null;
        $this->view->assign('askConfig', $askConfig);

        //顶部专区列表
        $topZoneList = \addons\ask\model\Zone::where('isnav', 1)->cache(true)->limit(10)->order('weigh DESC,id DESC')->select();
        $this->view->assign('topZoneList', $topZoneList);
    }

    public function _initialize()
    {
        parent::_initialize();
        //如果登录的情况下
        if ($this->auth->isLogin()) {
            $user = $this->auth->getUser();
            $user->ask = \addons\ask\model\User::get($this->auth->id);
            $this->view->assign('user', $user);
        }
        $this->view->assign('isAdmin', Service::isAdmin());
        $this->view->assign('isWechat', $this->isWechat());
        // 如果请求参数action的值为一个方法名,则直接调用
        $action = $this->request->post("action");
        if ($action && $this->request->isPost()) {
            return $this->$action();
        }
    }

    /**
     * 判断是否微信
     * @return bool
     */
    public function isWechat()
    {
        if (strpos($this->request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false) {
            return true;
        }
        return false;
    }

    protected function token()
    {
        $token = $this->request->param('__token__');

        //验证Token
        if (!Validate::make()->check(['__token__' => $token], ['__token__' => 'require|token'])) {
            $this->error("Token验证错误，请重试！", '', ['__token__' => $this->request->token()]);
        }

        //刷新Token
        $this->request->token();
    }

    protected function captcha($part)
    {
        $config = get_addon_config('ask');
        $partArr = array_filter(explode(',', $config['captchaparts'] ?? ''));
        if (in_array($part, $partArr)) {
            $captcha = $this->request->post("captcha");
            if (!captcha_check($captcha)) {
                $this->error("验证码不正确");
            }
        }
    }

}
