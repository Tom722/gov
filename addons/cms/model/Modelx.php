<?php

namespace addons\cms\model;

use addons\cms\library\Service;
use think\Config;
use think\Model;

/**
 * 模型
 */
class Modelx extends Model
{
    protected $name = "cms_model";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
    ];

    public function getFieldsAttr($value, $data)
    {
        return is_array($value) ? $value : ($value ? explode(',', $value) : []);
    }

    public function getSettingAttr($value, $data)
    {
        return (array)json_decode($value, true);
    }

    public function getFieldsListAttr($value, $data)
    {
        return Fields::where('source', 'model')->where('source_id', $data['id'])->where('status', 'normal')->cache(true)->select();
    }

    /**
     * 判断字段是否可投稿
     * @param string $field 字段名称
     * @return bool
     */
    public function iscontribute($field)
    {
        $setting = $this->setting;
        $contributefields = isset($setting['contributefields']) ? $setting['contributefields'] : [];
        return in_array($field, $contributefields);
    }

    /**
     * 获取排序字段信息
     * @return array
     */
    public function getOrderFields()
    {
        $setting = $this->setting;
        $orderfields = isset($setting['orderfields']) ? $setting['orderfields'] : [];
        $result = [];

        $prefix = Config::get('database.prefix');
        $fields = Service::getTableFields($prefix . "cms_archives");
        $titles = [];
        foreach ($fields as $index => $field) {
            $titles[$field['name']] = $field['title'];
        }
        $titles = array_merge($titles, isset($setting['titlelist']) ? $setting['titlelist'] : []);
        foreach ($orderfields as $index => $orderfield) {
            $title = isset($titles[$orderfield]) ? $titles[$orderfield] : $orderfield;
            $result[] = ['name' => $orderfield, 'field' => $orderfield, 'title' => $title];
        }
        return $result;
    }
}
