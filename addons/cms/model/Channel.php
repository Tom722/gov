<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Cache;
use think\Db;
use think\Model;
use think\View;

/**
 * 栏目模型
 */
class Channel extends Model
{
    protected $name = "cms_channel";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
        'url',
        'fullurl'
    ];
    protected static $config = [];

    protected static $tagCount = 0;

    protected static $parentIds = null;

    protected static $outlinkParentIds = null;

    protected static $navParentIds = null;

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
            return Service::getRelationFieldValue('channel', 0, $matches[1], $key);
        }
        return parent::getAttr($name);
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
        $cateid = $data['id'] ?? 0;
        $catename = isset($data['diyname']) && $data['diyname'] ? $data['diyname'] : 'all';
        $time = $data['createtime'] ?? time();

        $vars = [
            ':id'       => $data['id'],
            ':diyname'  => $diyname,
            ':channel'  => $cateid,
            ':catename' => $catename,
            ':cateid'   => $cateid,
            ':year'     => date("Y", $time),
            ':month'    => date("m", $time),
            ':day'      => date("d", $time)
        ];
        if (isset($data['type']) && isset($data['outlink']) && $data['type'] == 'link') {
            return $this->getAttr('outlink');
        }
        $suffix = static::$config['moduleurlsuffix']['channel'] ?? static::$config['urlsuffix'];
        return addon_url('cms/channel/index', $vars, $suffix, $domain);
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_channel_img'];
        return cdnurl($value);
    }

    public function getOutlinkAttr($value, $data)
    {
        $indexUrl = $view_replace_str = config('view_replace_str.__PUBLIC__');
        $indexUrl = rtrim($indexUrl, '/');
        return str_replace('__INDEX__', $indexUrl, $value);
    }

    public function getTagcolorAttr($value, $data)
    {
        $color = ['primary', 'default', 'success', 'warning', 'danger'];
        $index = $data['id'] % count($color);
        return isset($color[$index]) ? $color[$index] : $color[0];
    }

    public function getHasimageAttr($value, $data)
    {
        return $this->getData("image") ? true : false;
    }

    /**
     * 判断是否拥有子列表
     * @param $value
     * @param $data
     * @return bool|mixed
     */
    public function getHasChildAttr($value, $data)
    {
        static $checked = [];
        if (isset($checked[$data['id']])) {
            return $checked[$data['id']];
        }
        if (is_null(self::$parentIds)) {
            self::$parentIds = self::where('parent_id', '>', 0)->cache(true)->where('status', 'normal')->column('parent_id');
        }
        if (self::$parentIds && in_array($data['id'], self::$parentIds)) {
            return true;
        }
        return false;
    }

    /**
     * 判断导航是否拥有子列表
     * @param $value
     * @param $data
     * @return bool|mixed
     */
    public function getHasNavChildAttr($value, $data)
    {
        static $checked = [];
        if (isset($checked[$data['id']])) {
            return $checked[$data['id']];
        }
        if (is_null(self::$navParentIds)) {
            self::$navParentIds = self::where('parent_id', '>', 0)->cache(true)->where('status', 'normal')->where('isnav', 1)->column('parent_id');
        }
        if (self::$navParentIds && in_array($data['id'], self::$navParentIds)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否当前页面
     * @param $value
     * @param $data
     * @return bool
     */
    public function getIsActiveAttr($value, $data)
    {
        $url = request()->url();
        $channel = View::instance()->__CHANNEL__;
        if (($channel && ($channel['id'] == $this->id || $channel['parent_id'] == $this->id)) || $this->url == $url) {
            return true;
        } else {
            if ($this->has_child) {
                if (is_null(self::$outlinkParentIds)) {
                    self::$outlinkParentIds = self::where('type', 'link')->where('status', 'normal')->column('outlink,parent_id');
                }
                if (self::$outlinkParentIds && isset(self::$outlinkParentIds[$url]) && self::$outlinkParentIds[$url] == $this->id) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function getContributeInfo($archives, $model = null)
    {
        // 读取可发布的栏目列表
        $channel = new Channel();
        $disabledIds = [];
        $channelList = collection(
            $channel->where('status', 'normal')
                ->order("weigh desc,id desc")
                ->cache(true)
                ->select()
        )->toArray();
        $channelParents = [];
        foreach ($channelList as $index => $item) {
            if ($item['parent_id'] && $item['iscontribute']) {
                $channelParents[] = $item['parent_id'];
            }
        }
        $channelList = collection(
            $channel->where('status', 'normal')
                ->where(function ($query) use ($channelParents, $archives) {
                    $query->where("iscontribute", 1)->whereOr('id', 'in', $channelParents);
                    if ($archives) {
                        $query->whereOr('id', $archives->channel_id);
                    }
                })
                ->order("weigh desc,id desc")
                ->select()
        )->toArray();
        foreach ($channelList as $index => $item) {
            if (!$item['iscontribute'] && !in_array($item['id'], $channelParents) && (!$archives || $archives->channel_id != $item['id'])) {
                unset($channelList[$index]);
            }
        }
        foreach ($channelList as $k => $v) {
            if ($v['type'] == 'link' || (($archives || input('model_id')) && $model && $model['id'] != $v['model_id']) || (!$v['iscontribute'])) {
                $disabledIds[] = $v['id'];
            }
            //if ($v['type'] == 'channel' && !in_array($v['id'], $channelParents)) {
            //    unset($channelList[$k]);
            //}
        }
        return [$channelList, $disabledIds];
    }

    /**
     * 获取栏目所有子级的ID
     * @param mixed $ids      栏目ID或集合ID
     * @param bool  $withself 是否包含自身
     * @return array
     */
    public static function getChannelChildrenIds($ids, $withself = true)
    {
        $cacheName = 'childrens-' . $ids . '-' . $withself;
        $result = Cache::get($cacheName);
        if ($result === false) {
            $channelList = Channel::where('status', 'normal')
                ->order('weigh desc,id desc')
                ->cache(true)
                ->select();

            $result = [];
            $tree = \fast\Tree::instance();
            $tree->init(collection($channelList)->toArray(), 'parent_id');
            $channelIds = is_array($ids) ? $ids : explode(',', $ids);
            foreach ($channelIds as $index => $channelId) {
                $result = array_merge($result, $tree->getChildrenIds($channelId, $withself));
            }
            Cache::set($cacheName, $result);
        }
        return $result;
    }

    /**
     * 获取栏目列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getChannelList($params)
    {
        $type = empty($params['type']) ? '' : $params['type'];
        $typeid = !isset($params['typeid']) ? '' : $params['typeid'];
        $model = !isset($params['model']) ? '' : $params['model'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'weigh' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];
        $where = ['status' => 'normal'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('channellist', $params);

        self::$tagCount++;

        if ($type === 'top') {
            //顶级分类
            $where['parent_id'] = 0;
        } elseif ($type === 'brother') {
            $subQuery = self::where('id', 'in', $typeid)->field('parent_id')->buildSql();
            //同级
            $where['parent_id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'son') {
            $subQuery = self::where('parent_id', 'in', $typeid)->field('id')->buildSql();
            //子级
            $where['id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
        } elseif ($type === 'sons') {
            //所有子级
            $where['id'] = ['in', self::getChannelChildrenIds($typeid)];
        } else {
            if ($typeid !== '') {
                $where['id'] = ['in', $typeid];
            }
        }
        if ($model !== '') {
            $where['model_id'] = ['in', $model];
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $channelModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);
        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('cpage' . self::$tagCount, $params);
            $list = $channelModel->paginate($listRows, $simple, $config);
        } else {
            $list = $channelModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        Service::appendTextAndList('channel', 0, $list, true);

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    /**
     * 渲染数据
     * @param array $list
     * @param int   $imgwidth
     * @param int   $imgheight
     * @return array
     */
    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        foreach ($list as $k => &$v) {
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['name'] . '</a>';
            $v['channellink'] = '<a href="' . $v['url'] . '">' . $v['name'] . '</a>';
            $v['outlink'] = $v['outlink'];
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取面包屑导航
     * @param array $channel
     * @param array $archives
     * @param array $tags
     * @param array $page
     * @param array $diyform
     * @param array $special
     * @return array
     */
    public static function getBreadcrumb($channel, $archives = [], $tags = [], $page = [], $diyform = [], $special = [])
    {
        $list = [];
        $list[] = ['name' => __('Home'), 'url' => addon_url('cms/index/index', [], false)];
        if ($channel) {
            if ($channel['parent_id']) {
                $channelList = self::where('status', 'normal')
                    ->order('weigh desc,id desc')
                    ->field('id,name,type,parent_id,diyname,outlink')
                    ->cache(true)
                    ->select();
                //获取栏目的所有上级栏目
                $parents = \fast\Tree::instance()->init(collection($channelList)->toArray(), 'parent_id')->getParents($channel['id']);
                foreach ($parents as $k => $v) {
                    $list[] = ['name' => $v['name'], 'url' => $v['url']];
                }
            }
            $list[] = ['name' => $channel['name'], 'url' => $channel['url']];
        }
        if ($archives) {
            //$list[] = ['name' => $archives['title'], 'url' => $archives['url']];
        }

        foreach ([$tags, $page, $diyform, $special] as $index => $item) {
            if ($item && (!$channel || $channel['url'] != $item['url'])) {
                $list[] = ['name' => $item['title'] ?? $item['name'], 'url' => $item['url']];
            }
        }
        return $list;
    }

    /**
     * 获取导航栏目列表HTML
     * @param       $channel
     * @param array $params
     * @return mixed|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getNav($channel, $params = [])
    {
        $config = get_addon_config('cms');
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $maxLevel = !isset($params['maxlevel']) ? 0 : $params['maxlevel'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('nav', $params);

        $cacheName = 'nav-' . md5(serialize($params));
        $result = Cache::get($cacheName);
        if ($result === false) {
            $channelList = Channel::where($condition)
                ->where('status', 'normal')
                ->order('weigh desc,id desc')
                ->cache($cacheKey, $cacheExpire)
                ->select();
            $tree = \fast\Tree::instance();
            $tree->init(collection($channelList)->toArray(), 'parent_id');
            $result = self::getTreeUl($tree, 0, $channel ? $channel['id'] : '', '', 1, $maxLevel);
            Cache::set($cacheName, $result);
        }
        return $result;
    }

    public static function getTreeUl($tree, $myid, $selectedids = '', $disabledids = '', $level = 1, $maxlevel = 0)
    {
        $str = '';
        $childs = $tree->getChild($myid);
        if ($childs) {
            foreach ($childs as $value) {
                $id = $value['id'];
                unset($value['child']);
                $selected = $selectedids && in_array($id, (is_array($selectedids) ? $selectedids : explode(',', $selectedids))) ? 'selected' : '';
                $disabled = $disabledids && in_array($id, (is_array($disabledids) ? $disabledids : explode(',', $disabledids))) ? 'disabled' : '';
                $value = array_merge($value, array('selected' => $selected, 'disabled' => $disabled));
                $value = array_combine(array_map(function ($k) {
                    return '@' . $k;
                }, array_keys($value)), $value);
                $itemtpl = '<li class="@dropdown" value=@id @selected @disabled><a data-toggle="@toggle" data-target="#" href="@url">@name @caret</a> @childlist</li>';
                $nstr = strtr($itemtpl, $value);
                $childlist = '';
                if (!$maxlevel || $level < $maxlevel) {
                    $childdata = self::getTreeUl($tree, $id, $selectedids, $disabledids, $level + 1, $maxlevel);
                    $childlist = $childdata ? '<ul class="dropdown-menu" role="menu">' . $childdata . '</ul>' : "";
                }
                $str .= strtr($nstr, [
                    '@childlist' => $childlist,
                    '@caret'     => $childlist ? ($level == 1 ? '<span class="caret"></span>' : '') : '',
                    '@dropdown'  => $childlist ? ($level == 1 ? 'dropdown' : 'dropdown-submenu') : '',
                    '@toggle'    => $childlist ? 'dropdown' : ''
                ]);
            }
        }
        return $str;
    }

    public static function getChannelInfo($params)
    {
        $config = get_addon_config('cms');
        $cid = empty($params['cid']) ? '' : $params['cid'];
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

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('channelinfo', $params);

        if ($cid !== '') {
            $where['id'] = $cid;
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

    public static function getChannelByLinktype($type, $source_id)
    {
        $channel = (new self())->where('linktype', $type)->where('linkid', $source_id)->order('weigh DESC,id DESC')->find();
        return $channel;
    }

    public function model()
    {
        return $this->belongsTo('Modelx', 'model_id')->setEagerlyType(0);
    }

    public function parent()
    {
        return $this->belongsTo("Channel", "parent_id");
    }

}
