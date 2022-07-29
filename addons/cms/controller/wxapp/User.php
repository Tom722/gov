<?php

namespace addons\cms\controller\wxapp;

use addons\third\library\Service;
use addons\third\model\Third;
use app\common\library\Auth;
use fast\Http;
use think\Config;
use think\Validate;

/**
 * 会员
 */
class User extends Base
{
    protected $noNeedLogin = ['index', 'login'];

    protected $token = '';

    public function _initialize()
    {
        $this->token = $this->request->post('token');
        if ($this->request->action() == 'login' && $this->token) {
            $this->request->post(['token' => '']);
        }
        parent::_initialize();

        if (!Config::get('fastadmin.usercenter')) {
            $this->error(__('User center already closed'));
        }
    }


    /**
     * 登录
     */
    public function login()
    {
        if ($this->auth->isLogin()) {
            $this->success("登录成功", ['userInfo' => $this->getUserInfo()]);
        }
        $config = get_addon_config('cms');
        $code = $this->request->post("code");
        $rawData = $this->request->post("rawData", '', 'trim');
        if (!$code || !$rawData) {
            $this->error("参数不正确");
        }
        $third = get_addon_info('third');
        if (!$third || !$third['state']) {
            $this->error("请在后台插件管理安装并配置第三方登录插件");
        }
        $userInfo = (array)json_decode($rawData, true);

        $params = [
            'appid'      => $config['wxappid'],
            'secret'     => $config['wxappsecret'],
            'js_code'    => $code,
            'grant_type' => 'authorization_code'
        ];
        $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
        if ($result['ret']) {
            $json = (array)json_decode($result['msg'], true);
            if (isset($json['openid'])) {
                //如果有传Token
                if ($this->token) {
                    $this->auth->init($this->token);
                    //检测是否登录
                    if ($this->auth->isLogin()) {
                        $third = Third::where(['openid' => $json['openid'], 'platform' => 'wxapp'])->find();
                        if ($third && $third['user_id'] == $this->auth->id) {
                            $this->success("登录成功", ['userInfo' => $this->getUserInfo()]);
                        }
                    }
                }

                $platform = 'wechat';
                $result = [
                    'openid'        => $json['openid'],
                    'unionid'       => $json['unionid'],
                    'userinfo'      => [
                        'nickname' => $userInfo['nickName'],
                    ],
                    'access_token'  => $json['session_key'],
                    'refresh_token' => '',
                    'expires_in'    => isset($json['expires_in']) ? $json['expires_in'] : 0,
                    'apptype'       => 'miniapp'
                ];
                $extend = ['gender' => $userInfo['gender'], 'nickname' => $userInfo['nickName'], 'avatar' => $userInfo['avatarUrl']];
                $ret = Service::connect($platform, $result, $extend);
                if ($ret) {
                    $this->success("登录成功", ['userInfo' => $this->getUserInfo()]);
                } else {
                    $this->error("连接失败");
                }
            } else {
                $this->error("登录失败");
            }
        }

        return;
    }

    /**
     * 绑定账号
     */
    public function bind()
    {
        $account = $this->request->post("account");
        $password = $this->request->post("password");
        if (!$account || !$password) {
            $this->error("参数不正确");
        }

        $account = $this->request->post('account');
        $password = $this->request->post('password');
        $rule = [
            'account'  => 'require|length:3,50',
            'password' => 'require|length:6,30',
        ];

        $msg = [
            'account.require'  => 'Account can not be empty',
            'account.length'   => 'Account must be 3 to 50 characters',
            'password.require' => 'Password can not be empty',
            'password.length'  => 'Password must be 6 to 30 characters',
        ];
        $data = [
            'account'  => $account,
            'password' => $password,
        ];
        $validate = new Validate($rule, $msg);
        $result = $validate->check($data);
        if (!$result) {
            $this->error(__($validate->getError()));
            return false;
        }
        $field = Validate::is($account, 'email') ? 'email' : (Validate::regex($account, '/^1\d{10}$/') ? 'mobile' : 'username');
        $user = \app\common\model\User::get([$field => $account]);
        if (!$user) {
            $this->error('账号未找到');
        }
        $third = Third::where(['user_id' => $user->id, 'platform' => 'wxapp'])->find();
        if ($third) {
            $this->error('账号已经绑定其他小程序账号');
        }

        $third = Third::where(['user_id' => $this->auth->id, 'platform' => 'wechat', 'apptype' => 'miniapp'])->find();
        if (!$third) {
            $this->error('未找到登录信息');
        }

        if ($this->auth->login($account, $password)) {
            $third->user_id = $this->auth->id;
            $third->save();
            $this->success("绑定成功", ['userInfo' => $this->getUserInfo()]);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 个人资料
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio');
        $avatar = $this->request->post('avatar');
        if (!$username || !$nickname) {
            $this->error("用户名和昵称不能为空");
        }
        $exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
        if ($exists) {
            $this->error(__('Username already exists'));
        }
        $avatar = str_replace(cdnurl('', true), '', $avatar);
        $user->username = $username;
        $user->nickname = $nickname;
        $user->bio = $bio;
        $user->avatar = $avatar;
        $user->save();
        $this->success('', ['userInfo' => $this->getUserInfo()]);
    }

    /**
     * 保存头像
     */
    public function avatar()
    {
        $user = $this->auth->getUser();
        $avatar = $this->request->post('avatar');
        if (!$avatar) {
            $this->error("头像不能为空");
        }
        $avatar = str_replace(cdnurl('', true), '', $avatar);
        $user->avatar = $avatar;
        $user->save();
        $this->success('', ['userInfo' => $this->getUserInfo()]);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        $this->auth->logout();
        $this->success();
    }

    /**
     * 获取用户信息
     * @return array
     */
    protected function getUserInfo()
    {
        $userinfo = $this->auth->getUserInfo();
        $userinfo['avatar'] = cdnurl($userinfo['avatar'], true);
        $vip = get_addon_info('vip');
        $userinfo['is_install_vip'] = ($vip && $vip['state']);
        if (!$userinfo['is_install_vip']) {//禁用
            $userinfo['vip'] = 0;
            $userinfo['vipInfo'] = null;
        } else {
            $userinfo['vipInfo'] = \addons\vip\library\Service::getVipInfo($userinfo['id']) ?? null;
            if (empty($userinfo['vipInfo'])) {
                $userinfo['vip'] = 0;
            }
        }
        return $userinfo;
    }
}
