<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Cache;
use think\Db;
use think\Model;
use think\View;

/**
 * 区块模型
 */
class Block extends Model
{
    protected $name = "cms_block";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
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
            return Service::getRelationFieldValue('block', 0, $matches[1], $key);
        }
        return parent::getAttr($name);
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_block_img'];
        return cdnurl($value);
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

    /**
     * 获取区块列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBlockList($params)
    {
        $config = get_addon_config('cms');
        $type = empty($params['type']) ? '' : $params['type'];
        $name = empty($params['name']) ? '' : $params['name'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'id' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $cache = !isset($params['cache']) ? $config['cachelifetime'] : $params['cache'];
        $imgwidth = empty($params['imgwidth']) ? '' : $params['imgwidth'];
        $imgheight = empty($params['imgheight']) ? '' : $params['imgheight'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('blocklist', $params);

        self::$tagCount++;

        $where = ['status' => 'normal'];
        if ($type !== '') {
            $where['type'] = $type;
        }
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");
        $order = $orderby == 'weigh' ? $order . ',id DESC' : $order;

        $blockModel = self::where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order);

        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('bpage' . self::$tagCount, $params);
            $list = $blockModel->paginate($listRows, $simple, $config);
        } else {
            $list = $blockModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }

        Service::appendTextAndList('block', 0, $list, true);

        self::render($list, $imgwidth, $imgheight);
        return $list;
    }

    public static function render(&$list, $imgwidth, $imgheight)
    {
        $width = $imgwidth ? 'width="' . $imgwidth . '"' : '';
        $height = $imgheight ? 'height="' . $imgheight . '"' : '';
        $time = time();
        foreach ($list as $k => &$v) {
            if (($v['begintime'] && $time < $v['begintime']) || ($v['endtime'] && $time > $v['endtime'])) {
                unset($list[$k]);
                continue;
            }
            $v['textlink'] = '<a href="' . $v['url'] . '">' . $v['title'] . '</a>';
            $v['imglink'] = '<a href="' . $v['url'] . '"><img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' /></a>';
            $v['img'] = '<img src="' . $v['image'] . '" border="" ' . $width . ' ' . $height . ' />';
        }
        return $list;
    }

    /**
     * 获取区块内容
     * @param $params
     * @return string
     */
    public static function getBlockContent($params)
    {
        $fieldName = isset($params['id']) ? 'id' : 'name';
        $value = isset($params[$fieldName]) ? $params[$fieldName] : '';
        $field = isset($params['field']) ? $params['field'] : '';

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('blockcontent', $params);

        $row = self::where($fieldName, $value)
            ->where('status', 'normal')
            ->cache($cacheKey, $cacheExpire)
            ->find();
        $result = '';
        if ($row) {
            Service::appendTextAndList('block', 0, $row);

            $content = $row->getData('content');
            if ($field && isset($row[$field])) {
                $result = $row->getData($field);
            } else {
                if ($content) {
                    $result = $content;
                } elseif ($row['image']) {
                    $result = '<img src="' . $row['image'] . '" class="img-responsive"/>';
                } else {
                    $result = $row['title'];
                }
                if ($row['url'] && !$content) {
                    $result = $row['url'] ? '<a href="' . (preg_match("/^https?:\/\/(.*)/i", $row['url']) ? $row['url'] : url($row['url'])) . '" target="_blank">' . $result . '</a>' : $result;
                }
            }
            $row['begintime'] = (int)$row['begintime'];
            $row['endtime'] = (int)$row['endtime'];
            if (!$content || ($field && isset($row[$field]))) {
                return $result;
            } else {
                if (!$row['parsetpl']) {
                    $tagIdentify = "taglib_cms_block_content_" . $row['id'];
                    Cache::set($tagIdentify, $result);
                    $result = "{:cache('{$tagIdentify}')}";
                }
            }
            //未开始或过期处理
            $result = "{if (!{$row['begintime']} || time()>{$row['begintime']})&&(!{$row['endtime']} || time()<{$row['endtime']})}{$result}{/if}";
        }
        return $result;
    }
}
