<?php

namespace addons\ask\model;

use addons\ask\library\Askdown;
use addons\ask\library\Service;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

/**
 * 评论模型
 */
class Comment Extends Model
{

    protected $name = "ask_comment";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    // 追加属性
    protected $append = [
        'create_date',
    ];
    protected static $config = [];

    use SoftDelete;

    //自定义初始化
    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;

        //发送@消息
        self::afterInsert(function ($row) use ($config) {
            //Askdown::instance()->notification($row->getData("type"), $row->source_id);
            User::increase('comments', 1, $row->user_id);
            //增加积分
            Score::increase('postcomment', $row->user_id);
        });
        self::afterDelete(function ($row) use ($config) {
            $model = Service::getModelByType($row['type'], $row['source_id']);
            if ($model) {
                try {
                    $model->setDec("comments");
                    User::decrease('comments', 1, $row->user_id);
                } catch (Exception $e) {
                }
            }
            //减少积分
            Score::increase('postcomment', $row->user_id);
        });
    }

    public function getContentOutputAttr($value, $data)
    {
        return str_replace(["<", ">"], ["&lt;", "&gt;"], $data['content']);
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }

    public function getCreateDateAttr($value, $data)
    {
        return time() - $data['createtime'] > 7 * 86400 ? date("Y-m-d", $data['createtime']) : human_date($data['createtime']);
    }

    /**
     * 获取评论列表
     * @param $params
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getCommentList($params)
    {
        $type = empty($params['type']) ? 'archives' : $params['type'];
        $aid = empty($params['aid']) ? 0 : $params['aid'];
        $pid = empty($params['pid']) ? 0 : $params['pid'];
        $condition = empty($params['condition']) ? '' : $params['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
        $fragment = empty($params['fragment']) ? 'comments' : $params['fragment'];
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $orderby = empty($params['orderby']) ? 'nums' : $params['orderby'];
        $orderway = empty($params['orderway']) ? 'desc' : strtolower($params['orderway']);
        $pagesize = empty($params['pagesize']) ? $row : $params['pagesize'];
        $cache = !isset($params['cache']) ? false : (int)$params['cache'];
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';

        $where = [
            'status' => 'normal'
        ];
        if ($type) {
            $where['type'] = $type;
        }
        if ($aid !== '') {
            $where['aid'] = $aid;
        }
        if ($pid) {
            $where['pid'] = $pid;
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['pid', 'id', 'createtime', 'updatetime']) ? "{$orderby} {$orderway}" : "id {$orderway}");

        $list = self::with('user')
            ->where($where)
            ->where($condition)
            ->field($field)
            ->orderRaw($order)
            ->cache($cache)
            ->paginate($pagesize, false, ['type' => '\\addons\\ask\\library\\Bootstrap', 'var_page' => 'cp', 'fragment' => $fragment]);
        self::render($list);
        return $list;
    }

    public static function render(&$list)
    {
        return $list;
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo("app\common\model\User")->setEagerlyType(1);
    }

    /**
     * 关联回复会员模型
     */
    public function replyuser()
    {
        return $this->belongsTo("app\common\model\User", "reply_user_id", "id", [], "LEFT")->setEagerlyType(1);
    }

}
