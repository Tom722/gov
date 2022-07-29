<?php

namespace addons\ask\model;

use addons\ask\library\Askdown;
use addons\ask\library\FulltextSearch;
use addons\ask\library\Service;
use app\common\library\Auth;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

/**
 * 文章模型
 */
class Article Extends Model
{

    protected $name = "ask_article";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    // 追加属性
    protected $append = [
        'url',
        'fullurl',
        'create_date',
        'create_text',
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
            Askdown::instance()->notification('article', $row->id);
            User::increase('articles', 1, $row->user_id);
            //增加积分
            Score::increase('postarticle', $row->user_id);
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
            //删除相关评论
            $commentList = Comment::where('type', 'article')->where('source_id', $row['id'])->select();
            foreach ($commentList as $index => $item) {
                $item->delete();
            }
            User::decrease('articles', 1, $row->user_id);
            //更新Tags信息
            Tag::where('articles', '>', '0')->where('id', 'in', $row->tags_ids)->setDec("articles");
            //减少积分
            Score::decrease('postarticle', $row->user_id);
            if ($config['searchtype'] == 'xunsearch') {
                //全文搜索
                FulltextSearch::del($row);
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

    public function getCreateDateAttr($value, $data)
    {
        return time() - $data['createtime'] > 7 * 86400 ? date("Y-m-d", $data['createtime']) : human_date($data['createtime']);
    }

    public function getCreateTextAttr($value, $data)
    {
        return date("Y-m-d H:i:s", $data['createtime']);
    }


    public function getPaidStatusAttr($value, $data)
    {
        if (isset($this->data['paid_status'])) {
            return $this->data['paid_status'];
        }
        $user_id = Auth::instance()->id;

        //如果金额为0、本人、管理员 均可查看
        if ($data['user_id'] == $user_id || ($data['price'] == 0 && $data['score'] == 0)) {
            return 'noneed';
        } else {
            if ($this->getAttr('paid')) {
                return 'paid';
            } else {
                return 'unpaid';
            }
        }
    }

    public function getIsPaidPartOfContentAttr($value, $data)
    {
        $pattern = '/\$\$paidbegin\$\$(.*?)\$\$paidend\$\$/is';
        return preg_match($pattern, $value);
    }

    public function getContentFmtPartAttr($value, $data)
    {
        //如果内容中包含有付费标签
        $pattern = '/\$\$paidbegin\$\$(.*?)\$\$paidend\$\$/is';
        if (preg_match($pattern, $value) && !$this->getAttr('paid') && ($data['price'] > 0 || $data['score'] > 0)) {
            $money = (int)Auth::instance()->money;
            $currency = $data['price'] > 0 ? 'money' : 'score';
            $btn = "<a href='javascript:' class='btn btn-primary btn-paynow' style='color:white' data-id='{$data['id']}' data-type='article' data-price='{$data['price']}' data-score='{$data['score']}' data-currency='{$currency}' data-money='{$money}'>内容已经隐藏，点击付费后查看</a>";
            $value = preg_replace($pattern, "<div class='alert alert-warning alert-paid'>{$btn}</div>", $value);
        }
        return $value;
    }

    public function getContentFmtAttr($value, $data)
    {
        //如果内容中包含有付费标签
        $pattern = '/\$\$paidbegin\$\$(.*?)\$\$paidend\$\$/is';
        if (preg_match($pattern, $value)) {
            $value = preg_replace($pattern, "$1", $value);
        }
        return $value;
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }

    public function setTagData($data)
    {
        $this->data['tags'][] = $data;
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/article/show', [':id' => $data['id']], static::$config['urlsuffix']);
    }

    public function getFullUrlAttr($value, $data)
    {
        return addon_url('ask/article/show', [':id' => $data['id']], static::$config['urlsuffix'], true);
    }

    public function getPaidAttr($value, $data)
    {
        if (isset($this->data['paid'])) {
            return $this->data['paid'];
        }
        $this->data['paid'] = \addons\ask\library\Order::check('article', $data['id']);
        return $this->data['paid'];
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
        $tags = Tag::getTags('article', $data['id']);
        return $tags;
    }


    public function getImageAttr($value, $data)
    {
        $config = get_addon_config('ask');
        $value = $value ? $value : $config['default_article_image'];
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

    public static function getIndexArticleList($type, $category_id, $user_id = null, $zone_id = null, $keyword = null, $conditions = [])
    {
        $typeArr = [
            'new'   => 'createtime',
            'hot'   => 'views',
            'price' => 'createtime',
        ];
        $articleModel = self::with(['category', 'user'])->field('sales,shares,reports', true);

        $list = $articleModel
            ->where('status', '<>', 'hidden')
            ->where(function ($query) use ($type, $category_id, $user_id, $zone_id) {
                if ($type == 'price') {
                    $query->where('price|score', '>', 0);
                }
                if ($user_id) {
                    $query->where('user_id', '=', $user_id);
                }
                if ($category_id) {
                    $query->where('category_id', '=', $category_id);
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
            ->order(isset($typeArr[$type]) ? $typeArr[$type] : $typeArr['new'], 'desc')
            ->paginate(self::$config['pagesize']['article'], isset(self::$config['pagesimple']) && stripos(self::$config['pagesimple'], 'article') !== false);
        //渲染标签
        Tag::render($list, 'article');
        //渲染投票
        Vote::render($list, 'article');
        return $list;
    }

    public static function getArticleList($tag)
    {
        $category = !isset($tag['category']) ? '' : $tag['category'];
        $condition = empty($tag['condition']) ? '' : $tag['condition'];
        $field = empty($params['field']) ? '*' : $params['field'];
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
        $order = $orderby == 'rand' ? 'rand()' : (in_array($orderby, ['createtime', 'updatetime', 'views', 'weigh', 'id']) ? "{$orderby} {$orderway}" : "createtime {$orderway}");

        $articleModel = self::with(['category', 'user']);

        $list = $articleModel
            ->where($where)
            ->where($condition)
            ->field($field)
            ->cache($cache)
            ->orderRaw($order)
            ->limit($limit)
            ->select();
        //渲染标签
        Tag::render($list, 'article');
        //渲染投票
        Vote::render($list, 'article');
        return $list;
    }

    public static function notify($order)
    {
        $article = self::get($order->source_id);
        if ($article) {
            $config = get_addon_config('ask');
            if ($config['peepanswerratio']) {
                list($systemRatio, $authorRatio) = explode(':', $config['articleratio']);
                $amount = $article->price > 0 ? $article->price : $article->score;
                $method = $article->price > 0 ? "money" : "score";
                $method = "\\app\\common\\model\\User::{$method}";
                //付费文章分成
                $systemRatio > 0 && call_user_func_array($method, [$systemRatio * $amount, $config['system_user_id'], '付费文章分成']);
                $authorRatio > 0 && call_user_func_array($method, [$authorRatio * $amount, $article->user_id, '付费文章分成']);
            }
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
