<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Db;
use think\Model;
use think\View;
use traits\model\SoftDelete;

/**
 * 单页模型
 */
class Page extends Model
{
    use SoftDelete;
    protected $name = "cms_page";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];
    protected static $config = [];

    protected static $tagCount = 0;

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function getAttr($name)
    {
        //获取自定义字段关联表数据
        if (!isset($this->data[$name]) && preg_match("/(.*)_value\$/i", $name, $matches)) {
            $key = $this->data[$matches[1]] ?? '';
            if (!$key) {
                return '';
            }
            return Service::getRelationFieldValue('page', 0, $matches[1], $key);
        }
        return parent::getAttr($name);
    }

    public function getIscommentAttr($value, $data)
    {
        //优先判断全局评论开关
        $iscomment = self::$config['iscomment'] ?? 1;
        if ($iscomment) {
            $iscomment = $value ? $value : 0;
        }
        return $iscomment;
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_page_img'];
        return cdnurl($value);
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
        $diyname = isset($data['diyname']) && $data['diyname'] ? $data['diyname'] : $data['id'];
        $time = $data['createtime'] ?? time();

        $vars = [
            ':id'      => $data['id'],
            ':diyname' => $diyname,
            ':year'    => date("Y", $time),
            ':month'   => date("m", $time),
            ':day'     => date("d", $time)
        ];
        $suffix = static::$config['moduleurlsuffix']['page'] ?? static::$config['urlsuffix'];
        return addon_url('cms/page/index', $vars, $suffix, $domain);
    }

    public function getContentAttr($value, $data)
    {
        if (isset($data['parsetpl']) && $data['parsetpl']) {
            $view = View::instance();
            $view->engine->layout(false);
            return $view->display($data['content']);
        }
        return $data['content'];
    }

    public function getHasimageAttr($value, $data)
    {
        return $this->getData("image") ? true : false;
    }

    public function getLikeratioAttr($value, $data)
    {
        return ($data['dislikes'] > 0 ? min(1, $data['likes'] / ($data['dislikes'] + $data['likes'])) : ($data['likes'] ? 1 : 0.5)) * 100;
    }

    /**
     * 获取单页列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPageList($params)
    {
        $type = empty($params['type']) ? '' : $params['type'];
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

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('pagelist', $params);

        self::$tagCount++;

        $where = ['status' => 'normal'];
        if ($type !== '') {
            $where['type'] = $type;
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");

        $pageModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('ppage' . self::$tagCount, $params);
            $list = $pageModel->paginate($listRows, $simple, $config);
        } else {
            $list = $pageModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        Service::appendTextAndList('page', 0, $list, true);

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function getPageInfo($params)
    {
        $config = get_addon_config('cms');
        $sid = empty($params['sid']) ? '' : $params['sid'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'weigh' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $where = [];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('pageinfo', $params);

        if ($sid !== '') {
            $where['id'] = $sid;
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $data = self::where($where)
            ->where($condition)
            ->field($field)
            ->order($order)
            ->limit($limit)
            ->cache($cacheKey, $cacheExpire)
            ->find();
        if ($data) {
            $list = [$data];
            self::render($list, $imgwidth, $imgheight);
            return reset($list);
        } else {
            return false;
        }
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }
}
