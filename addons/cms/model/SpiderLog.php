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
 * 搜索引擎蜘蛛来访记录
 */
class SpiderLog extends Model
{
    protected $name = "cms_spider_log";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'firsttime';
    protected $updateTime = 'lasttime';

    // 追加属性
    protected $append = [
    ];
    protected static $config = [];

    /**
     * 记录搜索引擎蜘蛛请求记录
     * @param string $type
     * @param int $aid
     */
    public static function record($type, $aid = 0)
    {
        $config = get_addon_config('cms');
        if (!($config['spiderrecord'] ?? false)) {
            return;
        }
        $spiderName = Service::isSpider();
        if (!$spiderName) {
            return;
        }
        $spider = SpiderLog::where('type', $type)->where('aid', $aid)->where('name', $spiderName)->find();
        if (!$spider) {
            $lastdata = [time()];
            $data = [
                'type'     => $type,
                'aid'      => $aid,
                'url'      => request()->url(true),
                'name'     => $spiderName,
                'nums'     => 1,
                'lastdata' => implode(',', $lastdata)
            ];
            SpiderLog::create($data);
        } else {
            $lastdata = explode(',', $spider['lastdata']);
            $lastdata[] = time();
            $lastdata = array_slice($lastdata, -5);
            $spider->save(['lastdata' => implode(',', $lastdata), 'nums' => $spider['nums'] + 1]);
        }
    }
}
