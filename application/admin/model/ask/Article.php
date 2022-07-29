<?php

namespace app\admin\model\ask;

use addons\ask\library\Askdown;
use addons\ask\library\FulltextSearch;
use addons\ask\model\Taggable;
use addons\ask\model\User;
use addons\pay\library\Service;
use think\Exception;
use think\Model;
use traits\model\SoftDelete;

class Article extends Model
{
    use SoftDelete;
    // 表名
    protected $name = 'ask_article';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $auto = ['content_fmt'];

    // 追加属性
    protected $append = [
        'url',
        'flag_text',
        'deletetime_text',
        'status_text'
    ];
    protected static $config = [];

    protected static function init()
    {
        $config = get_addon_config('ask');
        self::$config = $config;

        self::afterDelete(function ($row) use ($config) {
            $data = $row->withTrashed()->where('id', $row->id)->find();
            if ($data) {
                //删除相关评论
                $commentList = Comment::where('type', 'article')->where('source_id', $row['id'])->select();
                foreach ($commentList as $index => $item) {
                    $item->delete();
                }
                User::decrease('articles', 1, $row->user_id);
                //更新Tags信息
                Tag::where('articles', '>', '0')->where('id', 'in', $row->tags_ids)->setDec("articles");
                //减少积分
                $config['score']['postarticle'] && \app\common\model\User::score(-$config['score']['postarticle'], $row->user_id, '删除文章');
                if ($config['searchtype'] == 'xunsearch') {
                    FulltextSearch::del($row);
                }
            } else {
                //删除相关评论
                $commentList = Comment::withTrashed()->where('type', 'article')->where('source_id', $row['id'])->select();
                foreach ($commentList as $index => $item) {
                    $item->delete(true);
                }
                //删除相关标签
                $tagableList = Taggable::where('type', 'article')->where('source_id', $row['id'])->select();
                foreach ($tagableList as $index => $item) {
                    $item->delete();
                }
            }
        });

        self::afterUpdate(function ($row) use ($config) {
            $changedData = $row->getChangedData();
            if (array_key_exists('deletetime', $changedData) && is_null($changedData['deletetime'])) {
                //更新Tags信息
                Tag::where('id', 'in', $row->tags_ids)->setInc("articles");
                User::increase('articles', 1, $row->user_id);
                $config['score']['postarticle'] && \app\common\model\User::score($config['score']['postarticle'], $row->user_id, '恢复文章');
                if ($config['searchtype'] == 'xunsearch') {
                    FulltextSearch::update($row);
                }
            }
        });
    }

    public function getUrlAttr($value, $data)
    {
        return addon_url('ask/article/show', [':id' => $data['id']], static::$config['urlsuffix']);
    }

    public function setContentFmtAttr($data)
    {
        $content = Askdown::instance()->format($this->data['content']);
        return $content;
    }

    public function getTagsAttr($value, $data)
    {
        if (isset($this->data['tags'])) {
            return $this->data['tags'];
        }
        $tags = Tag::getTags('article', $data['id']);
        return $tags;
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

    public function getFlagList()
    {
        return ['index' => __('Index'), 'hot' => __('Hot'), 'recommend' => __('Recommend'), 'top' => __('Top')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }


    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $valueArr = explode(',', $value);
        $list = $this->getFlagList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getDeletetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['deletetime']) ? $data['deletetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setFlagAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    protected function setDeletetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function user()
    {
        return $this->belongsTo("\app\common\model\User", 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function category()
    {
        return $this->belongsTo("Category", 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
