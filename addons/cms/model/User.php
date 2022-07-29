<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Db;

/**
 * 会员模型
 */
class User Extends \app\common\model\User
{

    protected static $config = [];

    protected static $tagCount = 0;

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function getUrlAttr($value, $data)
    {
        return $this->buildUrl($value, $data);
    }

    public function getFullurlAttr($value, $data)
    {
        return $this->buildUrl($value, $data, true);
    }

    private function buildUrl($value, $data, $domain = false)
    {
        $vars = [
            ':id' => $data['id'],
        ];
        //$suffix = static::$config['moduleurlsuffix']['user'] ?? static::$config['urlsuffix'];
        return addon_url('cms/user/index', $vars, false, $domain);
    }

    /**
     * 获取会员列表
     */
    public static function getUserList($params)
    {
        $config = get_addon_config('cms');
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'createtime' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('arclist', $params);

        self::$tagCount++;

        $where = [];
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");

        $userModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('upage' . self::$tagCount, $params);
            $list = $userModel->paginate($listRows, $simple, $config);
        } else {
            $list = $userModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }
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

}
