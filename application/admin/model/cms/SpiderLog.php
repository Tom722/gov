<?php

namespace app\admin\model\cms;

use fast\Date;
use think\Model;

/**
 * 搜索引擎来访
 * Class SpiderLog
 * @package app\admin\model\cms
 */
class SpiderLog extends Model
{

    // 表名
    protected $name = 'cms_spider_log';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'firsttime_text',
        'lasttime_text'
    ];


    public function getTypeList()
    {
        return ['index' => __('Index'), 'archives' => __('Archives'), 'page' => __('Page'), 'special' => __('Special'), 'channel' => __('Channel'), 'diyform' => __('Diyform'), 'tag' => __('Tag'), 'user' => __('User')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getLastdataTextAttr($value, $data)
    {
        $lastdataArr = explode(',', $data['lastdata']);
        foreach ($lastdataArr as $index => &$item) {
            $item = date("Y-m-d H:i:s", $item);
        }
        return implode(',', $lastdataArr);
    }


    public function getFirsttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['firsttime']) ? $data['firsttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLasttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['lasttime']) ? $data['lasttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setFirsttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setLasttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    /**
     * 渲染来访记录
     * @param mixed  $list
     * @param string $type
     */
    public static function render(&$list, $type)
    {
        if (!$list) {
            return;
        }
        $ids = [];
        foreach ($list as $index => $item) {
            $ids[] = $item['id'];
        }
        $config = get_addon_config('cms');
        $spiderDict = $config['spiders'] ?? [];
        $spiderFollowArr = array_filter(explode(',', $config['spiderfollow'] ?? ''));
        if (!$spiderFollowArr) {
            return;
        }
        $today = Date::unixtime();
        $spiderLogList = \addons\cms\model\SpiderLog::where('type', $type)
            ->where('aid', 'in', $ids)
            ->where('name', 'in', $spiderFollowArr)
            ->field('aid,name,lasttime')
            ->select();
        $logs = [];
        $spiderLogList = $spiderLogList ? collection($spiderLogList)->toArray() : [];
        foreach ($spiderLogList as $index => $item) {
            $logs[$item['aid']] = $logs[$item['aid']] ?? [];
            $logs[$item['aid']][$item['name']] = $item['lasttime'];
        }
        foreach ($list as $index => &$row) {
            $spiders = [];
            foreach ($spiderFollowArr as $key => $name) {
                $status = !isset($logs[$row['id']][$name]) ? 'none' : ($logs[$row['id']][$name] > $today ? 'today' : 'pass');
                $date = !isset($logs[$row['id']][$name]) ? '' : $logs[$row['id']][$name];
                $spiders[$name] = ['name' => $name, 'title' => isset($spiderDict[$name]) ? $spiderDict[$name] . (stripos($spiderDict[$name], '搜索') === false ? '搜索' : '') : '未知', 'status' => $status, 'date' => $date ? date("Y-m-d", $date) : ''];
            }
            $row['spiders'] = array_values($spiders);
        }
        return;
    }

}
