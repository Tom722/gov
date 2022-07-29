<?php

namespace addons\cms\model;

class Fields extends \think\Model
{

    // 表名
    protected $name = 'cms_fields';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'content_list',
        'isrequire',
    ];
    protected $type = [
        'setting' => 'json',
    ];
    protected static $listFields = ['select', 'selects', 'checkbox', 'radio', 'array', 'selectpage', 'selectpages'];

    protected static function init()
    {
    }

    public function getIsrequireAttr($value, $data)
    {
        return $data['rule'] && in_array('required', explode(',', $data['rule']));
    }

    public function getDownloadListAttr($value, $data)
    {
        $config = get_addon_config('cms');
        $downloadtype = $config['downloadtype'];
        $result = [];
        foreach ($downloadtype as $index => $item) {
            $result[] = ['name' => $index, 'url' => '', 'password' => ''];
        }
        return json_encode($result);
    }

    public function getExtendHtmlAttr($value, $data)
    {
        $result = preg_replace_callback("/\{([a-zA-Z]+)\}/", function ($matches) use ($data) {
            if (isset($data[$matches[1]])) {
                return $data[$matches[1]];
            }
        }, $data['extend']);
        return $result;
    }

    /**
     * 获取字典列表字段
     * @return array
     */
    public static function getListFields()
    {
        return self::$listFields;
    }

    public function getContentListAttr($value, $data)
    {
        $result = \app\common\model\Config::decode($data['content'] ?? '');
        return $result ?: [];
    }

    public function getFilterListAttr($value, $data)
    {
        $result = \app\common\model\Config::decode($data['filterlist'] ?? '');
        return $result ?: [];
    }

    public static function getFieldsContentList($source, $source_id = 0)
    {
        $list = Fields::where('source', $source)
            ->where('source_id', $source_id)
            ->field('id,name,type,content')
            ->where('status', 'normal')
            ->cache(true)
            ->select();
        $fieldsList = [];
        $listFields = Fields::getListFields();
        foreach ($list as $index => $item) {
            if (in_array($item['type'], $listFields)) {
                $fieldsList[$item['name']] = $item['content_list'];
            }
        }
        return $fieldsList;
    }

}
