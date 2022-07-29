<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Db;
use think\Model;

/**
 * 自定义表单模型
 */
class Diyform extends Model
{
    protected $name = "cms_diyform";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = '';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
        'url',
    ];
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
        $diyname = isset($data['diyname']) && $data['diyname'] ? $data['diyname'] : $data['id'];
        $time = $data['createtime'] ?? time();

        $vars = [
            ':id'      => $data['id'],
            ':diyname' => $diyname,
            ':year'    => date("Y", $time),
            ':month'   => date("m", $time),
            ':day'     => date("d", $time)
        ];
        $suffix = static::$config['moduleurlsuffix']['diyform'] ?? static::$config['urlsuffix'];
        return addon_url('cms/diyform/index', $vars, $suffix, $domain);
    }

    public function getPosturlAttr($value, $data)
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
        $suffix = static::$config['moduleurlsuffix']['diyform_post'] ?? static::$config['urlsuffix'];
        return addon_url('cms/diyform/post', $vars, $suffix);
    }

    public function getPosttitleAttr($value, $data)
    {
        return isset($data['posttitle']) && $data['posttitle'] ? $data['posttitle'] : "发布" . $data['title'];
    }

    public function getSettingAttr($value, $data)
    {
        return is_array($value) ? $value : (array)json_decode($data['setting'], true);
    }

    public function setSettingAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }

    public static function getDiyformFields($diyform_id, $values = [])
    {
        $fields = Service::getCustomFields('diyform', $diyform_id, $values, ['iscontribute' => 1]);
        return $fields;
    }

    public function getOrderFields()
    {
        $setting = $this->setting;
        $orderfields = isset($setting['orderfields']) ? $setting['orderfields'] : [];
        $orders = [
            ['name' => 'id', 'field' => 'id', 'title' => "ID"],
            ['name' => 'createtime', 'field' => 'createtime', 'title' => "添加时间"],
            ['name' => 'updatetime', 'field' => 'updatetime', 'title' => "更新时间"],
        ];

        return array_filter($orders, function ($item) use ($orderfields) {
            return in_array($item['name'], $orderfields);
        });
    }

    /**
     * 获取自定义表单数据列表
     * @param array $params
     * @return false|\PDOStatement|string|\think\Collection|array
     */
    public static function getDiydataList($params)
    {
        $config = get_addon_config('cms');
        $form = empty($params['diyform']) ? '' : $params['diyform'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'createtime' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $limit = empty($params['limit']) ? $row : $params['limit'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];

        list($cacheKey, $cacheExpire) = Service::getCacheKeyExpire('diydatalist', $params);

        self::$tagCount++;

        $where = [];
        $diyform = null;
        $diyform = Diyform::where("id", $form)->cache(true)->find();
        if (!$diyform) {
            return [];
        }
        $order = $orderby == 'rand' ? Db::raw('rand()') : (preg_match("/\,|\s/", $orderby) ? $orderby : "{$orderby} {$orderway}");

        $diydataModel = (new Diydata([], $diyform))->where($where)
            ->where('status', 'normal')
            ->where('status', '<>', $diyform['id'])
            ->where($condition)
            ->field($field)
            ->orderRaw($order);
        if ($paginate) {
            list($listRows, $simple, $config) = Service::getPaginateParams('dpage' . self::$tagCount, $params);
            $list = $diydataModel->paginate($listRows, $simple, $config);
        } else {
            $list = $diydataModel->limit($limit)->cache($cacheKey, $cacheExpire)->select();
        }
        return $list;
    }
}
