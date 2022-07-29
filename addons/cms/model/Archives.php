<?php

namespace addons\cms\model;

use addons\cms\library\IntCode;
use addons\cms\library\Service;
use app\common\library\Auth;
use Hashids\Hashids;
use think\Cache;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

/**
 * 文章模型
 */
class Archives extends Model
{
    protected $name = "cms_archives";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'likeratio',
        'taglist',
        'create_date',
    ];
    protected static $config = [];

    protected static $tagCount = 0;

    use SoftDelete;

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
        //替换付费内容标签
        if (isset($data['content'])) {
            $data['content'] = str_replace(['##paidbegin##', '##paidend##'], ['<paid>', '</paid>'], $data['content']);
            $data['content'] = str_replace(['$$paidbegin$$', '$$paidend$$'], ['<paid>', '</paid>'], $data['content']);
        }
        $this->data = array_merge($this->data, $data);
        $this->origin = $this->data;
        return $this;
    }

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
            return Service::getRelationFieldValue('model', $this->data['model_id'], $matches[1], $key);
        }
        return parent::getAttr($name);
    }

    /**
     * 获取加密后的ID
     * @param $value
     * @param $data
     * @return string
     */
    public function getEidAttr($value, $data)
    {
        $value = $data['id'];
        if (self::$config['archiveshashids']) {
            $value = IntCode::encode($value);
        }
        return $value;
    }

    public function getCreateDateAttr($value, $data)
    {
        return human_date($data['createtime']);
    }

    public function getHasimageAttr($value, $data)
    {
        return $this->getData("image") ? true : false;
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
        $value = $value ? $value : self::$config['default_archives_img'];
        return cdnurl($value, true);
    }

    public function getImagesAttr($value, $data)
    {
        if (!$data['images']) {
            return '';
        }
        $images = explode(',', $data['images']);
        foreach ($images as $index => &$image) {
            $image = cdnurl($image, true);
        }
        return implode(',', $images);
    }

    public function getImagesListAttr($value, $data)
    {
        if (isset($data['images_list'])) {
            return $data['images_list'];
        }
        $images = $this->getAttr("images");
        return $images ? array_filter(explode(',', $images)) : [];
    }

    /**
     * 获取格式化的内容
     */
    public function getContentAttr($value, $data)
    {
        //如果内容中包含有付费标签
        $value = $data['content'];
        $pattern = '/<paid>(.*?)<\/paid>/is';
        if (preg_match($pattern, $value) && !$this->getAttr('ispaid')) {
            $paytype = static::$config['defaultpaytype'];
            $payurl = addon_url('cms/order/submit', ['id' => $data['id'], 'paytype' => $paytype]);
            if (!isset($data['price']) || $data['price'] <= 0) {
                $value = preg_replace($pattern, "<div class='alert alert-warning alert-paid'><a href='javascript:' class=''>内容已经隐藏</a></div>", $value);
            } else {
                $value = preg_replace($pattern, "<div class='alert alert-warning alert-paid'><a href='{$payurl}' class='btn-paynow' data-price='{$data['price']}' data-paytype='{$paytype}'>内容已经隐藏，点击付费后查看</a></div>", $value);
            }
        }
        $config = get_addon_config('cms');
        if (isset($config['realtimereplacelink']) && $config['realtimereplacelink']) {
            $value = Service::autolinks($value);
        }
        return $value;
    }

    /**
     * 获取金额
     */
    public function getPriceAttr($value, &$data)
    {
        if (isset($data['price'])) {
            return $data['price'];
        }
        $price = 0;
        if (isset($data['model_id'])) {
            $model = Modelx::get($data['model_id']);
            if ($model && in_array('price', $model['fields'])) {
                $price = Db::name($model['table'])->where('id', $data['id'])->value('price');
            }
        }
        $data['price'] = $price;
        return $price;
    }

    /**
     * 判断是否支付
     */
    public function getIspayAttr($value, &$data)
    {
        return $this->getAttr('ispaid');
    }

    /**
     * 判断是否支付
     */
    public function getIspaidAttr($value, &$data)
    {
        if (isset($data['ispaid'])) {
            return $data['ispaid'];
        }

        $channel = isset($this->channel) ? $this->channel : null;
        //只有当未设定VIP时才判断付费字段
        if ($channel && !$channel->vip) {
            //如果未定义price字段或price字段值为0
            if (!isset($data['price']) || $data['price'] == 0) {
                return true;
            }
        }

        $isvip = $channel && isset($channel['vip']) && $channel['vip'] && Auth::instance()->vip >= $channel->vip ? true : false;
        $data['ispaid'] = $isvip || \addons\cms\library\Order::check($data['id']);
        return $data['ispaid'];
    }

    /**
     * 判断是否是部分内容付费
     */
    public function getIsPaidPartOfContentAttr($value, $data)
    {
        if (isset($data['is_paid_part_of_content'])) {
            return $data['is_paid_part_of_content'];
        }
        $value = isset($this->origin['content']) ? $this->origin['content'] : '';
        $result = preg_match('/<paid>(.*?)<\/paid>/is', $value);
        $data['is_paid_part_of_content'] = $result;
        return $result;
    }

    /**
     * 获取下载地址列表
     */
    public function getDownloadurlListAttr($value, $data)
    {
        $titleArr = isset(self::$config['downloadtype']) ? self::$config['downloadtype'] : [];
        $downloadurl = is_array($data['downloadurl']) ? $data['downloadurl'] : (array)json_decode($data['downloadurl'], true);
        $downloadurl = array_filter($downloadurl);
        $list = [];
        foreach ($downloadurl as $index => $item) {
            if (!is_array($item)) {
                $urlArr = explode(' ', $item);
                $result['name'] = $index;
                $result['title'] = isset($titleArr[$index]) ? $titleArr[$index] : '其它';
                $result['url'] = cdnurl($urlArr[0], true);
                $result['password'] = isset($urlArr[1]) ? $urlArr[1] : '';
                $list[] = $result;
            } elseif (is_array($item) && isset($item['name']) && isset($item['url']) && $item['url']) {
                $item['url'] = cdnurl($item['url'], true);
                $result = $item;
                $result['title'] = isset($titleArr[$item['name']]) ? $titleArr[$item['name']] : '其它';
                $result['password'] = $result['password'] ?? '';
                $list[] = $result;
            }
        }
        return $list;
    }

    public function getTaglistAttr($value, &$data)
    {
        if (isset($data['taglist'])) {
            return $data['taglist'];
        }
        $tags = array_filter(explode(",", $data['tags']));
        $tagList = [];
        if (stripos(self::$config['rewrite']['tag/index'], ":id") !== false) {
            $tagList = Tag::where('name', 'in', $tags)->column('name,id');
        }
        $tagList = array_change_key_case($tagList, CASE_LOWER);
        $time = $data['createtime'] ?? time();
        $list = [];
        foreach ($tags as $k => $v) {
            $v_lower = strtolower($v);
            $vars = [':name' => $v, ':diyname' => $v, ':id' => isset($tagList[$v_lower]) ? $tagList[$v_lower] : '0', ':year' => date("Y", $time), ':month' => date("m", $time), ':day' => date("d", $time)];
            $list[] = ['name' => $v, 'url' => addon_url('cms/tag/index', $vars)];
        }
        $data['taglist'] = $list;
        return $list;
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
        $catename = isset($this->channel) && $this->channel ? $this->channel->diyname : 'all';
        $cateid = isset($this->channel) && $this->channel ? $this->channel->id : 0;
        $time = $data['publishtime'] ?? time();
        $vars = [
            ':id'       => $data['id'],
            ':eid'      => $this->getEidAttr($data['id'], $data),
            ':diyname'  => $diyname,
            ':channel'  => $data['channel_id'],
            ':catename' => $catename,
            ':cateid'   => $cateid,
            ':year'     => date("Y", $time),
            ':month'    => date("m", $time),
            ':day'      => date("d", $time),
        ];
        $suffix = static::$config['moduleurlsuffix']['archives'] ?? static::$config['urlsuffix'];
        return addon_url('cms/archives/index', $vars, $suffix, $domain);
    }

    public function getLikeratioAttr($value, $data)
    {
        return ($data['dislikes'] > 0 ? min(1, $data['likes'] / ($data['dislikes'] + $data['likes'])) : ($data['likes'] ? 1 : 0.5)) * 100;
    }

    public function getStyleTextAttr($value, $data)
    {
        $color = $this->getAttr("style_color");
        $color = $color ? $color : "inherit";
        $color = str_replace(['#', ' '], '', $color);
        $bold = $this->getAttr("style_bold") ? "bold" : "normal";
        $attr = ["font-weight:{$bold};"];
        if (stripos($color, ',') !== false) {
            list($first, $second) = explode(',', $color);
            $attr[] = "background-image: -webkit-linear-gradient(0deg, #{$first} 0%, #{$second} 100%);background-image: linear-gradient(90deg, #{$first} 0%, #{$second} 100%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;";
        } else {
            $attr[] = "color:#{$color};";
        }

        return implode('', $attr);
    }

    public function getStyleBoldAttr($value, $data)
    {
        return in_array('b', explode('|', $data['style']));
    }

    public function getStyleColorAttr($value, $data)
    {
        $styleArr = explode('|', $data['style']);
        foreach ($styleArr as $index => $item) {
            if (preg_match('/\,|#/', $item)) {
                return $item;
            }
        }
        return '';
    }

    /**
     * 获取内容页分页HTML
     */
    public function getPagerHTML($page, $total, $simple = false)
    {
        if ($total <= 1) {
            return '';
        }
        $result = \addons\cms\library\Bootstrap::make([], 1, $page, $total, $simple, ['path' => $this->url, 'simple' => $simple]);
        return "<div class='pager'>" . $result->render() . "</div>";
    }

    /**
     * 获取文档列表
     * @param $params
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public static function getArchivesList($params)
    {
        $type = empty($params['type']) ? '' : $params['type'];
        $model = !isset($params['model']) ? '' : $params['model'];
        $channel = !isset($params['channel']) ? '' : $params['channel'];
        $special = !isset($params['special']) ? '' : $params['special'];
        $tags = empty($params['tags']) ? '' : $params['tags'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $flag = empty($params['flag']) ? '' : $params['flag'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'weigh' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $addon = empty($params['addon']) ? false : $params['addon'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];
        $page = !isset($params['page']) ? 1 : (int)$params['page'];
        $with = !isset($params['with']) ? 'channel' : $params['with'];
        $where = ['status' => 'normal'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('arclist', $params);

        $where['deletetime'] = ['exp', Db::raw('IS NULL')]; //by erastudio
        if ($model !== '') {
            $where['model_id'] = ['in', $model];
        }

        self::$tagCount++;

        $archivesModel = self::with($with)->alias('a');
        if ($channel !== '') {
            if ($type === 'son') {
                $subQuery = Channel::where('parent_id', 'in', $channel)->field('id')->buildSql();
                //子级
                $where['channel_id'] = ['exp', Db::raw(' IN ' . '(' . $subQuery . ')')];
            } elseif ($type === 'sons') {
                //所有子级
                $where['channel_id'] = ['in', Channel::getChannelChildrenIds($channel)];
            } else {
                $where['channel_id'] = ['in', $channel];
            }
        }
        //如果有设置标志,则拆分标志信息并构造condition条件
        if ($flag !== '') {
            if (stripos($flag, '&') !== false) {
                $arr = [];
                foreach (explode('&', $flag) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr) {
                    $condition .= "(" . implode(' AND ', $arr) . ")";
                }
            } else {
                $condition .= ($condition ? ' AND ' : '');
                $arr = [];
                foreach (explode(',', str_replace('|', ',', $flag)) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr) {
                    $condition .= "(" . implode(' OR ', $arr) . ")";
                }
            }
        }
        if ($special) {
            $specialModel = Special::get($special, [], true);
            if ($specialModel && $specialModel['tag_ids']) {
                $archivesModel->where("a.id", "in", function ($query) use ($specialModel) {
                    return $query->name("cms_taggable")->where("tag_id", "in", $specialModel['tag_ids'])->field("archives_id");
                });
            }
        }

        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',publishtime DESC' : $order;

        // 如果有筛选标签,则采用子查询
        if ($tags) {
            $tagIds = Tag::where('name', 'in', explode(',', $tags))->cache(true)->column("id");

            $archivesModel->where("a.id", "in", function ($query) use ($tagIds) {
                return $query->name("cms_taggable")->where("tag_id", "in", $tagIds)->field("archives_id");
            });
        }

        $modelInfo = null;
        $prefix = config('database.prefix');
        $archivesModel
            ->where($where)
            ->where($condition)
            ->field($field, false, $prefix . "cms_archives", "a")
            ->orderRaw($order);
        if ($addon && (is_numeric($model) || $channel)) {
            if ($channel) {
                //如果channel设置了多个值则只取第一个作为判断
                $channelArr = explode(',', $channel);
                $channelinfo = Channel::get($channelArr[0], [], true);
                $model = $channelinfo ? $channelinfo['model_id'] : $model;
            }
            // 查询相关联的模型信息
            $modelInfo = Modelx::get($model, [], true);
            if ($modelInfo) {
                $archivesModel->join($modelInfo['table'] . ' n', 'a.id=n.id', 'LEFT');
                if ($addon == 'true') {
                    $archivesModel->field('id,content', true, $prefix . $modelInfo['table'], 'n');
                } else {
                    $archivesModel->field($addon, false, $prefix . $modelInfo['table'], 'n');
                }
            }
        }

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams((isset($params['page']) ? 'page' : 'apage' . self::$tagCount), $params);
            if (isset($params['page'])) {
                $config['page'] = $page;
            }
            $list = $archivesModel->paginate($listRows, $simple, $config);
        } else {
            $list = $archivesModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        if ($modelInfo && $modelInfo->fields) {
            Service::appendTextAndList('model', $modelInfo->id, $list, true);
        }

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    /**
     * 标题高亮搜索结果
     */
    public function highlight($title, $keywords = '')
    {
        if ($keywords == '') {
            return $title;
        }
        $re = '/(' . str_replace(" ", '|', preg_quote($keywords)) . ')/i';
        return preg_replace($re, '<span class="highlight">$0</span>', $title);
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
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['channellink'] = '<a href="' . $v['channel']['url'] . '">' . $v['channel']['name'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取分页列表
     * @param array $list
     * @param array $params
     * @return array
     */
    public static function getPageList($list, $params)
    {
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        return self::render($list, $imgwidth, $imgheight);
    }

    /**
     * 获取分页过滤
     * @param array $list
     * @param array $params
     * @return array
     */
    public static function getPageFilter($list, $params)
    {
        $exclude = empty($params['exclude']) ? '' : $params['exclude'];
        return $list;
    }

    /**
     * 获取分页排序
     * @param array $list
     * @param array $params
     * @return array
     */
    public static function getPageOrder($list, $params)
    {
        $exclude = empty($params['exclude']) ? '' : $params['exclude'];
        return $list;
    }

    /**
     * 获取上一页下一页
     * @param array $params
     * @return array
     */
    public static function getPrevNext($params = [])
    {
        $type = isset($params['type']) ? $params['type'] : 'prev';
        $channel = isset($params['channel']) ? $params['channel'] : '';
        $archives = isset($params['archives']) ? $params['archives'] : '';
        $condition = isset($params['condition']) ? $params['condition'] : '';
        $model = self::where('id', $type === 'prev' ? '<' : '>', $archives)->where('status', 'normal');
        if ($channel !== '') {
            $model->where('channel_id', 'in', $channel);
        }
        if (isset($condition)) {
            $model->where($condition);
        }
        $model->order($type === 'prev' ? 'id desc' : 'id asc');
        $row = $model->find();
        return $row;
    }

    /**
     * 获取SQL查询结果
     */
    public static function getQueryList($params)
    {
        $sql = isset($params['sql']) ? $params['sql'] : '';
        $bind = isset($params['bind']) ? explode(',', $params['bind']) : [];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('sql', $params);
        $list = Cache::get($cacheKey);
        if (!$list) {
            $list = Db::query($sql, $bind);
            Cache::set($cacheKey, $list, $cacheExpire);
        }
        return $list;
    }

    /**
     * 关联模型
     */
    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }

    /**
     * 关联模型
     */
    public function model()
    {
        return $this->belongsTo("Modelx", 'model_id')->setEagerlyType(1);
    }

    /**
     * 关联栏目模型
     */
    public function channel()
    {
        return $this->belongsTo("Channel", 'channel_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }
}
