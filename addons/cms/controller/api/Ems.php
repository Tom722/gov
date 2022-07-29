<?php

namespace addons\cms\controller\api;

use app\common\library\Ems as Emslib;
use app\common\model\User;

/**
 * 邮箱验证码接口
 */
class Ems extends Base
{
    protected $noNeedLogin = ['*'];

    public function _initialize()
    {
        parent::_initialize();
        if (!$this->request->isPost()) {
            $this->error('请求错误');
        }
        \think\Hook::add('ems_send', function ($params) {
            $obj = \app\common\library\Email::instance();
            $result = $obj
                ->to($params->email)
                ->subject('验证码')
                ->message("你的验证码是：" . $params->code)
                ->send();
            return $result;
        });
    }

    /**
     * 发送验证码
     *
     * @param string $email 邮箱
     * @param string $event 事件名称
     */
    public function send()
    {
        $email = $this->request->param("email");
        $event = $this->request->param("event");
        $event = $event ? $event : 'register';

        $last = Emslib::get($email, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }
        if ($event) {
            $userinfo = User::getByEmail($email);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changeemail']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Emslib::send($email, null, $event);
        if ($ret) {
            $this->success(__('发送成功'));
        } else {
            $this->error(__('发送失败'));
        }
    }

    /**
     * 检测验证码
     *
     * @param string $email   邮箱
     * @param string $event   事件名称
     * @param string $captcha 验证码
     */
    public function check()
    {
        $email = $this->request->param("email");
        $event = $this->request->param("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->param("captcha");

        if ($event) {
            $userinfo = User::getByEmail($email);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changeemail']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Emslib::check($email, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }
}
