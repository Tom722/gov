<?php

namespace addons\cms\library;

use fast\Http;
use fast\Random;
use think\Cache;

class Jssdk
{
    private $appId;
    private $appSecret;

    public function __construct()
    {
        $config = get_addon_config('third');

        $this->appId = $config['wechat']['app_id'];
        $this->appSecret = $config['wechat']['app_secret'];
    }

    public function getSignedPackage($url)
    {
        $jsapiTicket = $this->getJsApiTicket();
        $timestamp = time();
        $nonceStr = Random::alnum(16);
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$jsapiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
            "jsticket" => $jsapiTicket,
        );
        return $signPackage;
    }

    private function getJsApiTicket()
    {
        $ticket = Cache::get("wechat_jsapi_ticket");
        if (!$ticket) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token={$accessToken}";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token={$accessToken}";
            $ret = Http::get($url);
            $json = (array)json_decode($ret, true);
            $ticket = isset($json['ticket']) ? $json['ticket'] : '';
            if ($ticket) {
                Cache::set('wechat_jsapi_ticket', $ticket, 7200);
            }
        }
        return $ticket;
    }

    private function getAccessToken()
    {
        $token = Cache::get("wechat_access_token");
        if (!$token) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={$this->appId}&corpsecret={$this->appSecret}";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
            $ret = Http::get($url);
            $json = (array)json_decode($ret, true);
            $token = isset($json['access_token']) ? $json['access_token'] : '';
            if ($token) {
                Cache::set('wechat_access_token', $token, 7200);
            }
        }
        return $token;
    }
}
