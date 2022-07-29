<?php

namespace addons\ask\model;

use addons\ask\library\Service;
use app\common\library\Auth;
use app\common\model\Addon;
use app\common\model\AddonFaq;
use think\Hook;
use think\Model;

/**
 * 专区模型
 */
class Zone extends Model
{

    protected $name = "ask_zone";
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
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;
    }

    // 定义全局的查询范围
    protected function base($query)
    {
        $query->where('status', 'normal');
    }

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_zone_image'];
        return cdnurl($value, true);
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/zone/show', [':id' => $data['id'], ':diyname' => $data['diyname']], static::$config['urlsuffix']);
    }

    public function getFullurlAttr($value, $data)
    {
        return addon_url('ask/zone/show', [':id' => $data['id'], ':diyname' => $data['diyname']], static::$config['urlsuffix'], true);
    }

    public function getQuestionsAttr($value, $data)
    {
        if (isset($this->data['questions'])) {
            return $this->data['questions'];
        }
        $this->data['questions'] = (int)Question::where('zone_id', $data['id'])->count();
        return $this->data['questions'];
    }

    public function getArticlesAttr($value, $data)
    {
        if (isset($this->data['articles'])) {
            return $this->data['articles'];
        }
        $this->data['articles'] = (int)Article::where('zone_id', $data['id'])->count();
        return $this->data['articles'];
    }

    public function getTopTagIdAttr($value, $data)
    {
        if (isset($this->data['top_tag_id'])) {
            return $this->data['top_tag_id'];
        }
        $tag = Tag::where('zone_id', $data['id'])->order('questions', 'desc')->find();
        $this->data['top_tag_id'] = $tag ? $tag->id : 0;
        return $this->data['top_tag_id'];
    }


    /**
     * 检测是否有专区权限
     * @param array $tags              标签集合
     * @param array $zoneProductList   受限的产品列表
     * @param array $zoneConditionList 受限的条件原因
     * @param array $zoneList          关联的专区列表
     * @return bool
     */
    public static function checkTags($tags = [], &$zoneProductList = [], &$zoneConditionList = [], &$zoneList = [])
    {
        $zoneList = [];
        if (!$tags) {
            return true;
        }
        $zoneIds = array_map(function ($row) {
            if ($row['zone_id']) {
                return $row['zone_id'];
            }
        }, $tags);
        $zoneIds = array_unique(array_filter($zoneIds));
        if (!$zoneIds) {
            return true;
        }
        return self::check($zoneIds, $zoneProductList, $zoneConditionList, $zoneList);
    }

    /**
     * 判断是否有专区浏览权限
     * @param array $zoneIds           专区ID集合
     * @param array $zoneProductList   受限的产品列表
     * @param array $zoneConditionList 受限的条件原因
     * @param array $zoneList          关联的专区列表
     * @return bool
     */
    public static function check($zoneIds = [], &$zoneProductList = [], &$zoneConditionList = [], &$zoneList = [])
    {
        $zoneProductList = $zoneConditionList = $zoneList = [];
        if (Service::isAdmin()) {
            return true;
        }
        $zoneIds = is_array($zoneIds) ? $zoneIds : [$zoneIds];
        $zoneList = self::where('id', 'in', $zoneIds)->order('weigh DESC,id DESC')->select();
        $conditionArr = [];
        foreach ($zoneList as $index => $item) {
            $arr = json_decode($item['condition'], true);
            if (!$arr) {
                continue;
            }
            array_walk($arr, function ($value, $key) use (&$conditionArr) {
                $conditionArr[$key] = isset($conditionArr[$key]) ? max($conditionArr[$key], $value) : $value;
            });
        }
        foreach ($zoneList as $index => $item) {
            if ($item['productid'] && $item['productname']) {
                $zoneProductList[] = $item;
            }
        }
        $conditionArr = array_unique(array_filter($conditionArr));
        $zoneConditionList = self::getConditionList($conditionArr);
        $user_id = Auth::instance()->id;
        if (!$user_id) {
            return false;
        }
        $zoneConditionList = self::getConditionList($conditionArr, true);

        if ($zoneProductList) {
            $result = Hook::listen('ask_zone_check', $zoneProductList, null, true);
            if ($result) {
                $zoneProductList = [];
            }
        }
        //如果未设置任何条件则直接允许
        if (!$zoneProductList && !$zoneConditionList) {
            return true;
        }
        return false;
    }

    /**
     * 获得原因
     * @param array $condition 限制条件
     * @param bool  $check     是否验证登录会员
     * @return array
     */
    public static function getConditionList($condition, $check = false)
    {
        $auth = Auth::instance();
        $arr = is_array($condition) ? $condition : (array)json_decode($condition, true);
        $reason = [];
        if (isset($arr['level']) && $arr['level']) {
            if (!$check || !$auth->id || $auth->level < $arr['level']) {
                $reason['level'] = "等级大于{$arr['level']}";
            }
        }
        if (isset($arr['vip']) && $arr['vip']) {
            if (!$check || !$auth->id || $auth->vip < $arr['vip']) {
                $reason['vip'] = "VIP大于{$arr['vip']}";
            }
        }
        if (isset($arr['score']) && $arr['score']) {
            if (!$check || !$auth->id || $auth->score < $arr['score']) {
                $reason['score'] = "积分大于{$arr['score']}";
            }
        }
        if (isset($arr['joindays']) && $arr['joindays']) {
            if (!$check || !$auth->id || ceil((time() - $auth->jointime) / 86400) < $arr['joindays']) {
                $reason['joindays'] = "注册天数大于{$arr['joindays']}天";
            }
        }
        if (isset($arr['isexpert']) && $arr['isexpert']) {
            if (!$check || !$auth->id || !$auth->title) {
                $reason['isexpert'] = "通过专家认证";
            }
        }
        return $reason;
    }


}
