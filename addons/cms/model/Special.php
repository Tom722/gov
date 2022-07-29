<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Cache;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

/**
 * 专题模型
 */
class Special extends Model
{
    use SoftDelete;
    protected $name = "cms_special";
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
        'create_date',
    ];
    protected static $config = [];

    protected static $tagCount = 0;

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
            return Service::getRelationFieldValue('special', 0, $matches[1], $key);
        }
        return parent::getAttr($name);
    }

    public function getCreateDateAttr($value, $data)
    {
        return human_date($data['createtime']);
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
        $value = $value ? $value : self::$config['default_special_img'];
        return cdnurl($value, true);
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
        $suffix = static::$config['moduleurlsuffix']['special'] ?? static::$config['urlsuffix'];
        return addon_url('cms/special/index', $vars, $suffix, $domain);
    }

    public function getHasimageAttr($value, $data)
    {
        return $this->getData("image") ? true : false;
    }

    /**
     * 获取专题列表
     * @param $params
     * @return array|false|\PDOStatement|string|\think\Collection
     */
    public static function getSpecialList($params)
    {
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $flag = empty($params['flag']) ? '' : $params['flag'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'createtime' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];
        $where = ['status' => 'normal'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('speciallist', $params);

        self::$tagCount++;

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
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $specialModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('spage' . self::$tagCount, $params);
            $list = $specialModel->paginate($listRows, $simple, $config);
        } else {
            $list = $specialModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        Service::appendTextAndList('special', 0, $list, true);

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
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取专题文档集合
     */
    public static function getArchivesIds($special_id)
    {
        $ids = Archives::whereRaw("FIND_IN_SET('{$special_id}', `special_ids`)")->cache(86400)->column('id');
        return $ids;
    }

}
