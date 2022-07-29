<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Model;

class Diydata extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    public $diyform = null;
    protected static $config = [];
    protected static $diyformModel = null;

    protected static function init()
    {
        $config = get_addon_config('cms');
        self::$config = $config;
    }

    public function __construct($data = [], $diyform = null)
    {
        if ($diyform) {
            static::$diyformModel = $diyform;
            $this->diyform = $diyform;
            $this->name = $this->diyform['table'];
            $class = get_class($this);
            //避免query不重新创建
            unset(static::$initialized[$class]);
            unset(self::$links[$class]);
        }
        return parent::__construct($data);
    }

    public function getDiyform()
    {
        return static::$diyformModel;
    }

    public function getAttr($name)
    {
        //获取自定义字段关联表数据
        if (!isset($this->data[$name]) && preg_match("/(.*)_value\$/i", $name, $matches)) {
            $key = $this->data[$matches[1]] ?? '';
            if (!$key) {
                return '';
            }
            return Service::getRelationFieldValue('diyform', static::$diyformModel['id'], $matches[1], $key);
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
        $diyname = !empty(static::$diyformModel['diyname']) ? static::$diyformModel['diyname'] : 'all';
        $time = $data['createtime'] ?? time();

        $vars = [
            ':id'      => $data['id'],
            ':diyname' => $diyname,
            ':year'    => date("Y", $time),
            ':month'   => date("m", $time),
            ':day'     => date("d", $time)
        ];
        $suffix = static::$config['moduleurlsuffix']['diyform_show'] ?? static::$config['urlsuffix'];
        return addon_url('cms/diyform/show', $vars, $suffix, $domain);
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden'), 'rejected' => __('Rejected')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : $data['status'];
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

}
