<?php

namespace addons\ask\model;

use addons\ask\library\Askdown;
use addons\ask\library\FulltextSearch;
use addons\ask\library\Service;
use app\common\library\Auth;
use fast\Date;
use think\Cache;
use think\Db;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

/**
 * 问题模型
 */
class Question extends Model
{
    protected $name = "ask_question";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    protected $type = [
        'price' => 'float',
        'score' => 'integer',
    ];

    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'create_date',
        'images_list'
    ];
    protected static $config = [];

    use SoftDelete;

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;

        self::beforeWrite(function ($row) use ($config) {
            $images = array_unique(array_filter(Service::getContentImages($row['content_fmt'])));
            $row['images'] = implode(',', array_slice($images, 0, 4));
        });

        //发送@消息
        self::afterInsert(function ($row) use ($config) {
            Askdown::instance()->notification('question', $row->id);
            User::increase('questions', 1, $row->user_id);
            User::increase('unadopted', 1, $row->user_id);
            //增加积分
            Score::increase('postquestion', $row->user_id);
            if ($config['searchtype'] == 'xunsearch') {
                //全文搜索
                FulltextSearch::add($row);
            }
        });
        self::afterWrite(function ($row) use ($config) {
            $changedData = $row->getChangedData();
            if (isset($changedData['status']) && $changedData['status'] == 'normal') {
                //推送到熊掌号和百度站长
                if ($config['baidupush']) {
                    $urls = [$row->fullurl];
                    \think\Hook::listen("baidupush", $urls);
                }
                if ($config['searchtype'] == 'xunsearch') {
                    //全文搜索
                    FulltextSearch::update($row);
                }
            }
        });
        self::afterDelete(function ($row) use ($config) {
            //删除相关回答
            $answerList = Answer::where('question_id', $row['id'])->select();
            foreach ($answerList as $index => $item) {
                $item->delete();
            }
            //删除相关评论
            $commentList = Comment::where('type', 'question')->where('source_id', $row['id'])->select();
            foreach ($commentList as $index => $item) {
                $item->delete();
            }
            User::decrease('questions', 1, $row->user_id);
            if (!$row['best_answer_id']) {
                User::decrease('unadopted', 1, $row->user_id);
            }
            //悬赏退回
            if (($row->price > 0 || $row->score > 0) && !$row->best_answer_id) {
                $row->price > 0 && \app\common\model\User::money($row->price, $row->user_id, '悬赏退回');
                $row->score > 0 && \app\common\model\User::score($row->score, $row->user_id, '悬赏退回');
                $row->save(['price' => 0, 'score' => 0, 'rewardtime' => null]);
            }
            //邀请回答退回
            Invite::refund($row['id']);
            //更新Tags信息
            Tag::where('questions', '>', '0')->where('id', 'in', $row->tags_ids)->setDec("questions");
            //减少积分
            Score::decrease('postquestion', $row->user_id);
            //删除动态
            Feed::where('user_id', $row->user_id)->where('action', 'post_question')->delete();
            if ($config['searchtype'] == 'xunsearch') {
                //全文搜索
                FulltextSearch::del($row);
            }
        });
        self::afterUpdate(function ($row) use ($config) {
            $changedData = $row->getChangedData();
            //增减未采纳数
            if (isset($changedData['best_answer_id'])) {
                if ($changedData['best_answer_id']) {
                    User::decrease('unadopted', 1, $row->user_id);
                } else {
                    User::increase('unadopted', 1, $row->user_id);
                }
            }
            if (isset($changedData['status'])) {
                //关闭问题时悬赏退回
                if ($changedData['status'] == 'closed') {
                    //减少未采纳数
                    if (!$row['best_answer_id']) {
                        User::decrease('unadopted', 1, $row->user_id);
                    }
                    //退回赏金
                    if (($row->price > 0 || $row->score > 0) && !$row->best_answer_id) {
                        $row->price > 0 && \app\common\model\User::money($row->price, $row->user_id, '悬赏退回');
                        $row->score > 0 && \app\common\model\User::score($row->score, $row->user_id, '悬赏退回');
                        $row->save(['price' => 0, 'score' => 0, 'rewardtime' => null]);
                    }
                } elseif ($changedData['status'] == 'normal') {
                    //增加未采纳数
                    if (!$row['best_answer_id']) {
                        User::increase('unadopted', 1, $row->user_id);
                    }
                }
                if ($changedData['status'] == 'closed' || $changedData['status'] == 'solved') {
                    //邀请回答退回
                    Invite::refund($row['id']);
                }
            }
            if ($config['searchtype'] == 'xunsearch') {
                //全文搜索
                FulltextSearch::update($row);
            }
        });
    }

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

    public function setTagData($data)
    {
        $this->data['tags'][] = $data;
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

    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : self::$config['default_question_image'];
        return cdnurl($value, true);
    }

    public function getImagesAttr($value, $data)
    {
        $value = $data['images'] ?? '';
        if (!$value) {
            return '';
        }
        $images = explode(',', $value);
        foreach ($images as $index => &$image) {
            $image = cdnurl($image, true);
        }
        return implode(',', $images);
    }

    public function getImagesListAttr($value, $data)
    {
        $images = $this->getAttr("images");
        return $images ? array_filter(explode(',', $images)) : [];
    }

    public function getCurrencyAttr($value, $data)
    {
        if (isset($this->data['currency'])) {
            return $this->data['currency'];
        }
        if ($data['price'] > 0) {
            $currency = 'money';
        } elseif ($data['score'] > 0) {
            $currency = 'score';
        } else {
            $config = get_addon_config('ask');
            $currency = $config['awardtype'] == 'score' ? 'score' : 'money';
        }

        return $currency;
    }

    public function getTagsAttr($value, $data)
    {
        if (isset($this->data['tags'])) {
            return $this->data['tags'];
        }
        $tags = Tag::getTags('question', $data['id']);
        return $tags;
    }

    public function getTagsTextAttr($value, $data)
    {
        if (isset($this->data['tags_text'])) {
            return $this->data['tags_text'];
        }
        $tagsArr = [];
        $tagsList = $this->getTagsAttr($value, $data);
        foreach ($tagsList as $index => $item) {
            $tagsArr[] = $item->name;
        }
        return implode(',', $tagsArr);
    }

    public function getTagsIdsAttr($value, $data)
    {
        if (isset($this->data['tags_ids'])) {
            return $this->data['tags_ids'];
        }
        $tagsArr = [];
        $tagsList = $this->getTagsAttr($value, $data);
        foreach ($tagsList as $index => $item) {
            $tagsArr[] = $item->id;
        }
        return $tagsArr;
    }

    public function getViewsFormatAttr($value, $data)
    {
        $result = $data['views'];
        if ($data['views'] > 1000) {
            $result = round($data['views'] / 1000, 1) . 'k';
        }
        return $result;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/question/show', [':id' => $data['id']], static::$config['urlsuffix']);
    }

    public function getFullUrlAttr($value, $data)
    {
        return addon_url('ask/question/show', [':id' => $data['id']], static::$config['urlsuffix'], true);
    }

    public function getStyleTextAttr($value, $data)
    {
        $color = $this->getAttr("style_color");
        $color = $color ? $color : "inherit";
        $color = str_replace(['#', ' '], '', $color);
        $bold = $this->getAttr("style_bold") ? "bold" : "normal";
        $underline = $this->getAttr("style_underline") ? "underline" : "none";
        $attr = [];
        if ($bold) {
            $attr[] = "font-weight:{$bold};";
        }
        if ($underline) {
            $attr[] = "text-decoration:{$underline};";
        }
        if (stripos($color, ',') !== false) {
            list($first, $second) = explode(',', $color);
            $attr[] = "background-image: -webkit-linear-gradient(0deg, #{$first} 0%, #{$second} 100%);background-image: linear-gradient(90deg, #{$first} 0%, #{$second} 100%);-webkit-background-clip: text;-webkit-text-fill-color: transparent;";
        } else {
            $attr[] = "color:#{$color};";
        }

        return implode('', $attr);
    }

    public function getStyleColorFirstAttr($value, $data)
    {
        $color = $this->getAttr('style_color');
        $colorArr = explode(',', $color);
        return $colorArr[0];
    }

    public function getStyleColorSecondAttr($value, $data)
    {
        $color = $this->getAttr('style_color');
        $colorArr = explode(',', $color);
        return isset($colorArr[1]) ? $colorArr[1] : '';
    }

    public function getStyleBoldAttr($value, $data)
    {
        return in_array('b', explode('|', $data['style']));
    }

    public function getStyleUnderlineAttr($value, $data)
    {
        return in_array('u', explode('|', $data['style']));
    }

    public function getStyleColorAttr($value, $data)
    {
        $styleArr = explode('|', $data['style']);
        foreach ($styleArr as $index => $item) {
            if (preg_match('/\,|#/', $item)) {
                return $item;
            }
        }
        return '';
    }

    public function getFlagListAttr($value, $data)
    {
        return explode(',', $data['flag']);
    }

    /**
     * 悬赏是否已过期
     */
    public function getIsRewardExpiredAttr($value, $data)
    {
        return ($data['price'] > 0 || $data['score'] > 0) && (time() - $data['rewardtime']) > self::$config['adoptdays'] * 86400;
    }

    /**
     * 悬赏剩余时长
     */
    public function getRewardRemainSecondsAttr($value, $data)
    {
        $seconds = ($data['rewardtime'] ? $data['rewardtime'] : $data['createtime']) + self::$config['adoptdays'] * 86400 - time();
        $seconds = $seconds < 0 ? 0 : $seconds;
        return $seconds;
    }

    /**
     * 悬赏剩余时长(文字)
     */
    public function getRewardRemainTextAttr($value, $data)
    {
        $span = Date::span(($data['rewardtime'] ? $data['rewardtime'] : $data['createtime']) + self::$config['adoptdays'] * 86400, null, 'days,hours,minutes,seconds');
        $span['hours'] = str_pad($span['hours'], 2, "0", STR_PAD_LEFT);
        $span['minutes'] = str_pad($span['minutes'], 2, "0", STR_PAD_LEFT);
        $span['seconds'] = str_pad($span['seconds'], 2, "0", STR_PAD_LEFT);
        return "{$span['days']}天{$span['hours']}时{$span['minutes']}分{$span['seconds']}秒";
    }

    /**
     * 付费查看是否已过期
     */
    public function getIsPeepExpiredAttr($value, $data)
    {
        if ($data['price'] > 0 || $data['score'] > 0) {
            return $this->getIsRewardExpiredAttr($value, $data);
        } else {
            return (time() - $data['createtime']) > self::$config['adoptdays'] * 86400;
        }
    }

    /**
     * 付费查看是否已禁用
     */
    public function getIsPeepDisabledAttr($value, $data)
    {
        $auth = Auth::instance();
        return $data['best_answer_id'] || $this->getIsPeepExpiredAttr($value, $data) || $auth->score < self::$config['limitscore']['peepsetting'];
    }

    /**
     * 获取付费查看禁用原因
     */
    public function getPeepDisabledReasonAttr($value, $data)
    {
        $auth = Auth::instance();
        $reason = '';
        if ($data['best_answer_id']) {
            $reason = "提问者已经采纳最佳答案";
        } elseif ($this->getIsPeepExpiredAttr($value, $data)) {
            $reason = "提问者在 " . self::$config['adoptdays'] . " 天内未采纳任何最佳答案";
        } elseif ($auth->score < self::$config['limitscore']['peepsetting']) {
            $reason = "你的积分小于" . self::$config['limitscore']['peepsetting'];
        }
        return $reason;
    }

    /**
     * 获取问题列表
     */
    public static function getQuestionList($tag)
    {
        $category = !isset($tag['category']) ? '' : $tag['category'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($tag['field']) ? '*' : $tag['field'];
        $flag = empty($tag['flag']) ? '' : $tag['flag'];
        $row = empty($tag['row']) ? 10 : (int)$tag['row'];
        $orderby = empty($tag['orderby']) ? 'createtime' : $tag['orderby'];
        $orderway = empty($tag['orderway']) ? 'desc' : strtolower($tag['orderway']);
        $limit = empty($tag['limit']) ? $row : $tag['limit'];
        $cache = !isset($tag['cache']) ? true : (is_bool($tag['cache']) ? $tag['cache'] : (int)$tag['cache']);
        $orderway = in_array($orderway, ['asc', 'desc']) ? $orderway : 'desc';
        $where = ['status' => ['<>', 'hidden']];

        $where['deletetime'] = ['exp', Db::raw('IS NULL')]; //by erastudio
        if ($category !== '') {
            $where['category_id'] = ['in', $category];
        }
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
                foreach (array_merge(explode(',', $flag), explode('|', $flag)) as $k => $v) {
                    $arr[] = "FIND_IN_SET('{$v}', flag)";
                }
                if ($arr) {
                    $condition .= "(" . implode(' OR ', $arr) . ")";
                }
            }
        }
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['createtime', 'updatetime', 'answers', 'views', 'weigh', 'id']) ? "{$orderby} {$orderway}" : "createtime {$orderway}");
        $questionModel = self::with(['category', 'user']);

        $list = $questionModel
            ->where($where)
            ->where($condition)
            ->field($field)
            ->cache($cache)
            ->orderRaw($order)
            ->limit($limit)
            ->select();
        Tag::render($list, 'question');
        return $list;
    }

    /**
     * 获取上一页下一页
     * @param string $type
     * @param string $question
     * @param string $category
     * @return array
     */
    public static function getPrevNext($type, $question, $category)
    {
        $model = self::where('id', $type === 'prev' ? '<' : '>', $question)->where('status', 'normal');
        if ($category !== '') {
            $model->where('category_id', 'in', $category);
        }
        $model->order($type === 'prev' ? 'id desc' : 'id asc');
        $row = $model->find();
        return $row;
    }

    /**
     * 获取SQL查询结果
     */
    public static function getQueryList($tag)
    {
        $config = get_addon_config('ask');
        $sql = isset($tag['sql']) ? $tag['sql'] : '';
        $bind = isset($tag['bind']) ? explode(',', $tag['bind']) : [];
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] === 'true' ? true : (int)$config['cachelifetime'] : (int)$tag['cache'];
        $name = md5("sql-" . $tag['sql']);
        $list = Cache::get($name);
        if (!$list) {
            $list = Db::query($sql, $bind);
            Cache::set($name, $list, $cache);
        }
        return $list;
    }

    public static function getIndexQuestionList($type, $category_id = null, $user_id = null, $zone_id = null, $keyword = null, $conditions = [])
    {
        $typeArr = [
            'new'       => 'createtime',
            'hot'       => 'answers',
            'price'     => 'createtime',
            'unsolved'  => 'createtime',
            'unanswer'  => 'createtime',
            'unsettled' => 'rewardtime',
            'solved'    => 'createtime',
        ];
        $questionModel = self::with(['category', 'user'])->field('reports,peeps,deletetime', true);

        $list = $questionModel
            ->where('status', '<>', 'hidden')
            ->where(function ($query) use ($type, $category_id, $user_id, $zone_id) {
                if ($type == 'price') {
                    $query->where('price|score', '>', 0);
                } elseif ($type == 'unsolved') {
                    $query->where('best_answer_id', '=', 0);
                    $query->where('status', '=', 'normal');
                } elseif ($type == 'unanswer') {
                    $query->where('answers', '=', 0);
                } elseif ($type == 'unsettled') {
                    $query->where('price|score', '>', 0);
                    $query->where('best_answer_id', '=', 0);
                    $query->where('rewardtime', '<', strtotime("-" . self::$config['adoptdays'] . ' days'));
                } elseif ($type == 'solved') {
                    $query->where('best_answer_id', '>', 0);
                }
                if ($category_id) {
                    $query->where('category_id', '=', $category_id);
                }
                if ($user_id) {
                    $query->where('user_id', '=', $user_id);
                }
                if ($zone_id) {
                    $query->where('zone_id', '=', $zone_id);
                }
            })
            ->where(function ($query) use ($keyword) {
                $arr = array_filter(explode(' ', $keyword));
                foreach ($arr as $index => $item) {
                    $query->where('title', 'like', "%{$item}%");
                }
            })
            ->where($conditions)
            ->order(isset($typeArr[$type]) ? $typeArr[$type] : $typeArr['new'], $type == 'unsettled' ? 'asc' : 'desc')
            ->paginate(self::$config['pagesize']['question'], isset(self::$config['pagesimple']) && stripos(self::$config['pagesimple'], 'question') !== false);
        //渲染标签
        Tag::render($list, 'question');
        return $list;
    }

    public function refund()
    {
        Db::startTrans();
        try {
            $price = $this->price;
            $score = $this->score;
            $this->save(['price' => 0, 'score' => 0, 'rewardtime' => null]);
            $price && \app\common\model\User::money($price, $this->user_id, "悬赏退回");
            $score && \app\common\model\User::score($score, $this->user_id, "悬赏退回");
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
        }
    }

    /**
     * 关联分类模型
     */
    public function category()
    {
        return $this->belongsTo('Category', 'category_id', 'id')->setEagerlyType(1);
    }

    /**
     * 关联会员模型
     */
    public function user()
    {
        return $this->belongsTo('\app\common\model\User', 'user_id', 'id')->field('password,salt,token', true)->setEagerlyType(1);
    }
}
