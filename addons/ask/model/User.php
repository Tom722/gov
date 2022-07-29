<?php

namespace addons\ask\model;

use addons\ask\library\Service;
use app\common\library\Auth;
use think\Model;

/**
 * 会员模型
 */
class User Extends Model
{

    protected $name = "ask_user";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'url',
        'fullurl',
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    /**
     * 批量设置数据
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/user/index', [':id' => $data['user_id'], ':action' => ''], false);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('ask/user/index', [':id' => $data['user_id'], ':action' => ''], false, true);
    }

    public function getAvatarAttr($value, $data)
    {
        return $data['avatar'] ? $data['avatar'] : (function_exists('letter_avatar') ? letter_avatar($data['nickname']) : '/assets/img/avatar.png');
    }

    public function getFollowedAttr($value, $data)
    {
        if (isset($this->data['followed'])) {
            return $this->data['followed'];
        }
        $this->data['followed'] = Attention::check('user', $data['user_id']) ? true : false;
        return $this->data['followed'];
    }

    public function getMeAttr()
    {
        $auth = Auth::instance();
        return $this->id && $auth->id == $this->id ? true : false;
    }

    public function getNoticesAttr($value, $data)
    {
        return $data['invites'] + $data['messages'] + $data['notifications'] + $data['unadopted'];
    }

    /**
     * 获取单页列表
     */
    public static function getUserList($params)
    {
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? true : (int)$params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [];
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['id', 'followers', 'questions', 'answers', 'comments', 'articles', 'collections', 'adoptions', 'views', 'createtime', 'updatetime', 'score']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::alias('t')
            ->join('user u', 'u.id = t.user_id')
            ->where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order)
            ->limit($limit)
            ->cache($cache)
            ->select();
        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['nickname'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['avatar'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['avatar'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 增加字段值
     * @param string $field   字段
     * @param int    $value   默认为1
     * @param null   $user_id 会员ID，默认为当前登录会员
     */
    public static function increase($field, $value = 1, $user_id = null)
    {
        if (!$value) {
            return;
        }
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        $user = self::get($user_id);
        if ($user) {
            $user->setInc($field, $value);
        }
    }

    /**
     * 减少字段值
     * @param string $field   字段
     * @param int    $value   默认为1
     * @param null   $user_id 会员ID，默认为当前登录会员
     */
    public static function decrease($field, $value = 1, $user_id = null)
    {
        if (!$value) {
            return;
        }
        $user_id = is_null($user_id) ? Auth::instance()->id : $user_id;
        $user = self::get($user_id);
        if ($user) {
            $user->setDec($field, $value);
        }
    }

    public function basic()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id');
    }

    public function certification()
    {
        return $this->belongsTo('Certification', 'user_id', 'user_id', [], 'LEFT');
    }

    public function combine()
    {
        $userinfo = $this->basic->toArray();
        $userinfo['prevtime'] = date('Y-m-d H:i:s',$userinfo['prevtime']);
        $userinfo['logintime'] = date('Y-m-d H:i:s',$userinfo['logintime']);     
        $userinfo['createtime'] = date('Y-m-d H:i:s',$userinfo['createtime']); 
        $this->data = array_merge($this->data, $userinfo);
        return $this->data;
    }

}
