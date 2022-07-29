<?php

namespace addons\ask\library;

use fast\Http;
use think\Cache;
use think\Log;
use addons\ask\model\WxUser;

/**
 * 微信接口
 *
 */
class Wechat
{
    private $app_id = '';
    private $app_secret = '';

    public function __construct()
    {
        $config = get_addon_config('ask');
        if (isset($config['app_id'])) {
            $this->app_id = $config['app_id'];
        }
        if (isset($config['app_secret'])) {
            $this->app_secret = $config['app_secret'];
        }
    }

    /**
     * 根据前台传过来的code 去取openid和session_key
     * @param string $code
     * @return array
     */
    public function codeToSessionkey($code)
    {
        $result = [
            'status' => false,
            'data'   => '',
            'msg'    => '获取成功'
        ];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->app_id . '&secret=' . $this->app_secret . '&js_code=' . $code . '&grant_type=authorization_code';
        $Http = new Http();
        $re = $Http->get($url);
        $re = json_decode($re, true);
        if (!isset($re['errcode'])) {
            $result['data'] = $re;
            $result['status'] = true;
        } else {
            $result['msg'] = $re['errcode'] . ":" . $re['errmsg'];
        }
        return $result;
    }

    /**
     * 小程序数据解密
     * @param string $edata
     * @param string $iv
     * @param string $sessionKey
     * @return array
     */
    public function decrypt($edata, $iv, $sessionKey)
    {
        $result = [
            'status' => false,
            'data'   => '',
            'msg'    => ''
        ];
        if (strlen($sessionKey) != 24) {
            $result['msg'] = 'sessionKey不正确';
            return $result;
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            $result['msg'] = 'iv不正确';
            return $result;
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($edata);
        $re = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $data = json_decode($re, true);
        if ($data) {
            $result['status'] = true;
            $result['data'] = $data;
            return $result;
        } else {
            $result['msg'] = '解密错误';
            return $result;
        }
    }


    /**
     * 获取access_token
     * @return void
     */
    public function getAccessToken()
    {
        //todo::$appid和$secret从配置文件获取
        //查询是否有缓存的access_token todo::改成mysql数据库存储
        $key = $this->app_id . '_' . $this->app_secret;
        $val = Cache::get($key);
        if (!$val) {
            //没有缓存的，去微信接口获取access_token
            $Http = new Http();
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->app_id . '&secret=' . $this->app_secret;
            $res = $Http->get($url);
            $res = json_decode($res, true);
            $val = $res['access_token'];
            Cache::set($key, $val, 3600);
        }
        //返回access_token
        return $val;
    }

    /**
     * 公众号授权
     * @param string  $code
     * @param integer $scope
     * @param int     $user_id
     * @param integer $pid
     * @return bool
     */
    public function codeToInfo($code, $scope = 1, $user_id, $pid = 0)
    {
        $config = get_addon_config('epay');

        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $config['wechat']['app_id'] . "&secret=" . $config['wechat']['app_secret'] . "&code=" . $code . "&grant_type=authorization_code";
        $http = new Http();
        $re = $http->get($url);
        $data = json_decode($re, true);

        if (isset($data['errcode'])) {
            return false;
        }
        $wx = new WxUser();
        //如果是静默登陆的话，到这里就OK了。
        if ($scope == 2) {
            $wx_user = $wx->where('type', 2)->where('openid', $data['openid'])->where('user_id', $user_id)->find();
            if ($wx_user) {
                return $wx_user->id;
            }
            $wx->save([
                'user_id' => $user_id,
                'type'    => 2,
                'openid'  => $data['openid']
            ]);
            return $wx->id;
        }
        //根据access_token拉取用户信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $data['access_token'] . "&openid=" . $data['openid'] . "&lang=zh_CN";
        $re = $http->get($url);
        $data = json_decode($re, true);
        if (isset($data['errcode'])) {
            return false;
        }
        //到这里就取到用户的信息了
        return $wx->addUserWx($data, $user_id, $pid);
    }
}
