<?php

namespace addons\cms\library;

use addons\cms\library\aip\AipContentCensor;
use addons\cms\library\aip\AipNlp;
use addons\cms\model\Autolink;
use addons\cms\model\Diyform;
use addons\cms\model\Fields;
use addons\cms\model\Modelx;
use addons\cms\model\Tag;
use fast\Http;
use fast\Random;
use think\Cache;
use think\Config;
use think\Db;
use think\Exception;
use think\Hook;
use think\Model;
use think\model\Collection;

class Service
{

    /**
     * 检测内容是否合法
     * @param string $content 检测内容
     * @param string $type    类型
     * @return bool
     */
    public static function isContentLegal($content, $type = null)
    {
        $config = get_addon_config('cms');
        $type = is_null($type) ? $config['audittype'] : $type;
        if ($type == 'local') {
            // 敏感词过滤
            $handle = SensitiveHelper::init()->setTreeByFile(ADDON_PATH . 'cms/data/words.dic');
            //首先检测是否合法
            $isLegal = $handle->islegal($content);
            return $isLegal ? true : false;
        } elseif ($type == 'baiduyun') {
            $client = new AipContentCensor($config['aip_appid'], $config['aip_apikey'], $config['aip_secretkey']);
            $result = $client->textCensorUserDefined($content);
            if (!isset($result['conclusionType']) || $result['conclusionType'] > 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取标题的关键字
     * @param $title
     * @return array
     */
    public static function getContentTags($title)
    {
        $arr = [];
        $config = get_addon_config('cms');
        if ($config['nlptype'] == 'local') {
            !defined('_VIC_WORD_DICT_PATH_') && define('_VIC_WORD_DICT_PATH_', ADDON_PATH . 'cms/data/dict.json');
            $handle = new VicWord('json');
            $result = $handle->getAutoWord($title);
            foreach ($result as $index => $item) {
                $arr[] = $item[0];
            }
        } else {
            $client = new AipNlp($config['aip_appid'], $config['aip_apikey'], $config['aip_secretkey']);
            $result = $client->lexer($title);
            if (isset($result['items'])) {
                foreach ($result['items'] as $index => $item) {
                    if (!in_array($item['pos'], ['v', 'vd', 'nd', 'a', 'ad', 'an', 'd', 'm', 'q', 'r', 'p', 'c', 'u', 'xc', 'w'])) {
                        $arr[] = $item['item'];
                    }
                }
            }
        }
        foreach ($arr as $index => $item) {
            if (mb_strlen($item) == 1) {
                unset($arr[$index]);
            }
        }
        return array_filter(array_unique($arr));
    }

    /**
     * 内容关键字自动加链接
     * 优先顺序为 站点配置自动链接 > 自动链接表 > 标签内链
     */
    public static function autolinks($content)
    {
        $stages = [];

        //先移除已有的自动链接
        $content = preg_replace_callback('/\<a\s*data\-rel="autolink".*?\>(.*?)\<\/a\>/i', function ($match) {
            return $match[1];
        }, $content);

        //存储所有A标签
        $content = preg_replace_callback('/\<a(.*?)href\s*=\s*(\'|")(.*?)(\'|")(.*?)\>(.*?)\<\/a\>/i', function ($match) use (&$stages) {
            $data = [$match[3], $match[5], $match[6]];
            return '<' . array_push($stages, $data) . '>';
        }, $content);

        //存在所有HTML标签
        $content = preg_replace_callback('/(<(?!\d+).*?>)/i', function ($match) use (&$stages) {
            return '<' . array_push($stages, $match[1]) . '>';
        }, $content);

        $config = get_addon_config('cms');
        $limit = 2; //单一标签最大替换次数
        $autolinkArr = [];
        $tagList = Tag::where('autolink', 1)->cache(true)->where('status', 'normal')->select();
        foreach ($tagList as $index => $item) {
            $autolinkArr[$item['name']] = ['text' => $item['name'], 'type' => 'tag', 'url' => $item['fullurl']];
        }
        $autolinkList = Autolink::where('status', 'normal')->cache(true)->order('weigh DESC,id DESC')->select();
        foreach ($autolinkList as $index => $item) {
            $autolinkArr[$item['title']] = ['text' => $item['title'], 'type' => 'autolink', 'url' => $item['url'], 'target' => $item['target'], 'id' => $item['id']];
        }
        foreach ($config['autolinks'] as $text => $url) {
            $autolinkArr[$text] = ['text' => $text, 'type' => 'config', 'url' => $url];
        }

        $autolinkArr = array_values($autolinkArr);
        //字符串长的优先替换
        usort($autolinkArr, function ($a, $b) {
            if ($a['text'] == $b['text']) return 0;
            return (strlen($a['text']) > strlen($b['text'])) ? -1 : 1;
        });

        //替换链接
        foreach ($autolinkArr as $index => $item) {
            $content = preg_replace_callback('/(' . preg_quote($item['text'], '/') . ')/i', function ($match) use ($item, $config, &$stages) {
                $url = $item['type'] == 'autolink' && isset($item['id']) ? addon_url('cms/go/index', [], $config['urlsuffix'], true) . '?id=' . $item['id'] : $item['url'];
                $data = [$url, (isset($item['target']) && $item['target'] == 'blank' ? ' target="_blank"' : ''), $match[0]];
                return '<' . array_push($stages, $data) . '>';
            }, $content, $limit);
        }

        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$stages, $config) {
            $data = $stages[$match[1] - 1];
            if (!is_array($data)) {
                return $data;
            }
            $url = $data[0];
            $urlArr = parse_url($url);
            //站内链接不中转，站外链接中转
            if (isset($urlArr['host']) && $urlArr['host'] != request()->host() && ($config['redirecturl'] ?? true)) {
                $url = addon_url('cms/go/index', [], $config['urlsuffix'], true) . '?' . http_build_query(['url' => $url]);
            }
            return "<a href=\"{$url}\" {$data[1]}>{$data[2]}</a>";
        }, $content);
    }

    /**
     * 推送消息通知
     * @param string $content 内容
     * @param string $type
     * @param string $template_id
     */
    public static function notice($content, $type = null, $template_id = null)
    {
        $config = get_addon_config('cms');
        $type = $type ? $type : $config['auditnotice'];
        $template_id = $template_id ? $template_id : $config['noticetemplateid'];

        try {
            if ($type == 'dinghorn') {
                //钉钉通知插件(dinghorn)
                Hook::listen('msg_notice', $template_id, [
                    'content' => $content
                ]);
            } elseif ($type == 'vbot') {
                //企业微信通知(vbot)
                Hook::listen('vbot_send_msg', $template_id, [
                    'content' => $content
                ]);
            } elseif ($type == 'notice') {
                //消息通知插件(notice)
                $params = [
                    'event'  => $template_id,
                    'params' => [
                        'title'   => $content,
                        'content' => $content,
                    ]
                ];
                Hook::listen('notice_to_data', $params);
            }
        } catch (\Exception $e) {

        }
    }

    /**
     * 获取表字段信息
     * @param string $table 表名
     * @return array
     */
    public static function getTableFields($table)
    {
        $tagName = "cms-table-fields-{$table}";
        $fieldlist = Cache::get($tagName);
        if (!Config::get('app_debug') && $fieldlist) {
            return $fieldlist;
        }
        $dbname = Config::get('database.database');
        //从数据库中获取表字段信息
        $sql = "SELECT * FROM `information_schema`.`columns` WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION";
        //加载主表的列
        $columnList = Db::query($sql, [$dbname, $table]);
        $fieldlist = [];
        foreach ($columnList as $index => $item) {
            $fieldlist[] = ['name' => $item['COLUMN_NAME'], 'title' => $item['COLUMN_COMMENT'], 'type' => $item['DATA_TYPE']];
        }
        Cache::set($tagName, $fieldlist);
        return $fieldlist;
    }

    /**
     * 获取指定类型的自定义字段列表
     */
    public static function getCustomFields($source, $source_id, $values = [], $conditions = [])
    {
        $fields = Fields::where('source', $source)
            ->where('source_id', $source_id)
            ->where($conditions)
            ->where('status', 'normal')
            ->order('weigh desc,id desc')
            ->select();
        foreach ($fields as $k => $v) {
            //优先取编辑的值,再次取默认值
            $v->value = isset($values[$v['name']]) ? $values[$v['name']] : (is_null($v['defaultvalue']) ? '' : $v['defaultvalue']);
            $v->rule = str_replace(',', '; ', $v->rule);
            if (in_array($v['type'], ['checkbox', 'lists', 'images'])) {
                $checked = '';
                if ($v['minimum'] && $v['maximum']) {
                    $checked = "{$v['minimum']}~{$v['maximum']}";
                } elseif ($v['minimum']) {
                    $checked = "{$v['minimum']}~";
                } elseif ($v['maximum']) {
                    $checked = "~{$v['maximum']}";
                }
                if ($checked) {
                    $v->rule .= (';checked(' . $checked . ')');
                }
            }
            if (in_array($v['type'], ['checkbox', 'radio']) && stripos($v->rule, 'required') !== false) {
                $v->rule = str_replace('required', 'checked', $v->rule);
            }
            if (in_array($v['type'], ['selects'])) {
                $v->extend .= (' ' . 'data-max-options="' . $v['maximum'] . '"');
            }
        }

        return $fields;
    }

    /**
     * 获取过滤列表
     * @param string $source    来源类型
     * @param int    $source_id 来源ID
     * @param array  $filter    过滤条件
     * @param array  $params    搜索参数
     * @param bool   $multiple  是否为复选模式
     * @return array
     */
    public static function getFilterList($source, $source_id, $filter, $params = [], $multiple = false)
    {
        $fieldsList = Fields::where('source', $source)
            ->where('source_id', $source_id)
            ->where('status', 'normal')
            ->cache(true)
            ->select();

        $filterList = [];
        $multiValueFields = [];

        $fields = [];
        if (in_array($source, ['model', 'diyform'])) {
            //查找主表启用过滤搜索的字段
            $model = $source == 'model' ? Modelx::get($source_id) : Diyform::get($source_id);
            $setting = $model->setting;
            if (isset($setting['filterfields'])) {
                foreach ($setting['filterfields'] as $index => $name) {
                    $title = isset($setting['titlelist'][$name]) ? $setting['titlelist'][$name] : $name;
                    $filterlist = isset($setting['filterlist'][$name]) ? $setting['filterlist'][$name] : '';
                    $filterlist = \app\common\model\Config::decode($filterlist);
                    if (!$filterlist) {
                        continue;
                    }
                    if (in_array($name, ['special_ids', 'channel_ids', 'images', 'tags', 'keywords'])) {
                        $multiValueFields[] = $name;
                    }

                    $fields[] = [
                        'name'    => $name,
                        'title'   => $title,
                        'content' => $filterlist
                    ];
                }
            }
        }
        foreach ($fieldsList as $k => $v) {
            if (!$v['isfilter']) {
                continue;
            }
            $content = isset($v['filter_list']) && $v['filter_list'] ? $v['filter_list'] : $v['content_list'];
            if (!$content) {
                continue;
            }
            //多选值字段需要做特殊处理
            if (in_array($v['type'], ['selects', 'checkbox', 'array', 'selectpages'])) {
                $multiValueFields[] = $v['name'];
            }
            $fields[] = [
                'name'    => $v['name'],
                'title'   => $v['title'],
                'content' => $content
            ];
        }
        $filter = array_intersect_key($filter, array_flip(array_column($fields, 'name')));
        foreach ($fields as $k => $v) {
            $content = [];
            $all = ['' => __('All')] + (is_array($v['content']) ? $v['content'] : []);
            foreach ($all as $m => $n) {
                $filterArr = isset($filter[$v['name']]) && $filter[$v['name']] !== '' ? ($multiple ? explode(';', $filter[$v['name']]) : [$filter[$v['name']]]) : [];
                $active = ($m === '' && !$filterArr) || ($m !== '' && in_array($m, $filterArr)) ? true : false;
                if ($active) {
                    $current = implode(';', array_diff($filterArr, [$m]));
                } else {
                    $current = $multiple ? implode(';', array_merge($filterArr, [$m])) : $m;
                }
                $prepare = $m === '' ? array_diff_key($filter, [$v['name'] => $m]) : array_merge($filter, [$v['name'] => $current]);
                //$url = '?' . http_build_query(array_merge(['filter' => $prepare], array_diff_key($params, ['filter' => ''])));
                $url = '?' . str_replace(['%2C', '%3B'], [',', ';'], http_build_query(array_merge($prepare, array_intersect_key($params, array_flip(['orderby', 'orderway', 'multiple'])))));
                $content[] = ['value' => $m, 'title' => $n, 'active' => $active, 'url' => $url];
            }

            $filterList[] = [
                'name'    => $v['name'],
                'title'   => $v['title'],
                'content' => $content,
            ];
        }
        foreach ($filter as $index => &$item) {
            $item = is_array($item) ? $item : explode(',', str_replace(';', ',', $item));
        }

        return [$filterList, $filter, $params, $fields, $multiValueFields, $fieldsList];
    }

    /**
     * 获取排序列表
     * @param string $orderby
     * @param string $orderway
     * @param array  $orders
     * @param array  $params
     * @param array  $fieldsList
     * @return array
     */
    public static function getOrderList($orderby, $orderway, $orders = [], $params = [], $fieldsList = [])
    {
        $lastOrderby = '';
        $lastOrderway = $orderway && in_array(strtolower($orderway), ['asc', 'desc']) ? $orderway : 'desc';

        foreach ($fieldsList as $index => $field) {
            if ($field['isorder']) {
                $orders[] = ['name' => $field['name'], 'field' => $field['name'], 'title' => $field['title']];
            }
        }

        $orderby = in_array($orderby, array_map(function ($item) {
            return $item['name'];
        }, $orders)) ? $orderby : 'default';

        foreach ($orders as $index => $order) {
            if ($orderby == $order['name']) {
                $lastOrderby = $order['field'];
                break;
            }
        }

        $orderList = [];
        foreach ($orders as $k => $v) {
            $url = '?' . http_build_query(array_merge($params, ['orderby' => $v['name'], 'orderway' => $v['name'] == $orderby ? ($lastOrderway == 'desc' ? 'asc' : 'desc') : 'desc']));
            $v['active'] = $orderby == $v['name'] ? true : false;
            $v['url'] = $url;
            $orderList[] = $v;
        }

        return [$orderList, $lastOrderby, $lastOrderway];
    }

    /**
     * 获取过滤的最终条件和绑定参数
     * @param array $filter           过滤条件
     * @param array $multiValueFields 多值字段
     * @param bool  $multiple         是否为复选模式
     * @return array
     */
    public static function getFilterWhereBind($filter, $multiValueFields, $multiple = false)
    {
        //构造bind数据
        $bind = [];
        foreach ($filter as $field => &$item) {
            if (in_array($field, $multiValueFields)) {
                $item = !is_array($item) && stripos($item, ',') !== false ? explode(',', $item) : $item;
                if (is_array($item)) {
                    foreach ($item as $index => $subitem) {
                        $bind[$field . $index] = $subitem;
                    }
                } else {
                    $bind[$field] = $item;
                }
            }
        }

        $filterWhere = function ($query) use ($filter, $multiValueFields) {
            foreach ($filter as $field => $item) {
                $item = is_array($item) ? $item : [$item];
                if (in_array($field, $multiValueFields)) {
                    $query->where(function ($query) use ($field, $item) {
                        foreach ($item as $subindex => $subitem) {
                            $query->whereOr("FIND_IN_SET(:" . $field . $subindex . ", `{$field}`)");
                        }
                    });
                } else {
                    $query->where(function ($query) use ($field, $item) {
                        foreach ($item as $subindex => $subitem) {
                            //如果匹配区间,以~分隔
                            if (preg_match("/[a-zA-Z0-9\.\-]+\~[a-zA-Z0-9\.\-]+/", $subitem)) {
                                $condition = explode('~', $subitem);
                                //判断是否时间区间
                                $op = preg_match("/\d{4}\-\d{1,2}\-\d{1,2}/", $condition[0]) ? 'between time' : 'between';
                                $query->whereOr($field, $op, $condition);
                            } else {
                                $query->whereOr($field, $subitem);
                            }
                        }
                    });
                }
            }
        };
        return [$filterWhere, $bind];
    }

    /**
     * 获取pagelist标签参数
     * @param string $template
     * @return array
     */
    public static function getPagelistParams($template)
    {
        $config = get_addon_config('cms');
        $templateFile = ADDON_PATH . 'cms' . DS . 'view' . DS . $config['theme'] . $template . '.html';
        if (!is_file($templateFile)) {
            return [];
        }
        $templateContent = file_get_contents($templateFile);
        preg_match("/\{cms:pagelist(.*)\}/i", $templateContent, $matches);
        $attr = [];
        if ($matches) {
            $tagAttrText = $matches[1];
            preg_match_all('/\s+(?>(?P<name>[\w-]+)\s*)=(?>\s*)([\"\'])(?P<value>(?:(?!\\2).)*)\\2/is', $tagAttrText, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $attr[$match['name']] = $match['value'];
            }
            unset($matches);
        }
        return $attr;
    }

    /**
     * 追加_text属性值
     * @param $fieldsContentList
     * @param $row
     */
    public static function appendTextAttr(&$fieldsContentList, &$row)
    {
        //附加列表字段
        array_walk($fieldsContentList, function ($content, $field) use (&$row) {
            if (isset($row[$field])) {
                if (isset($content[$row[$field]])) {
                    $list = [$row[$field] => $content[$row[$field]]];
                } else {
                    $keys = $values = explode(',', $row[$field]);
                    foreach ($values as $index => &$item) {
                        $item = isset($content[$item]) ? $content[$item] : $item;
                    }
                    $list = array_combine($keys, $values);
                }
            } else {
                $list = [];
            }
            $list = array_filter($list);
            $row[$field . '_text'] = implode(',', $list);
            $row[$field . '_list'] = $list;
        });
    }

    /**
     * 追加_text和_list后缀数据
     *
     * @param string  $source
     * @param int     $source_id
     * @param mixed   $row
     * @param boolean $isMultiArray 是否为二维数组
     * @return mixed
     */
    public static function appendTextAndList($source, $source_id, &$row, $isMultiArray = false)
    {
        $list = Fields::where('source', $source)
            ->where('source_id', $source_id)
            ->field('id,name,type,content')
            ->where('status', 'normal')
            ->cache(true)
            ->select();
        $fieldsType = [];
        $fieldsList = [];
        $listFields = Fields::getListFields();
        foreach ($list as $field => $content) {
            $fieldsType[$content['name']] = $content['type'];
            if (in_array($content['type'], $listFields)) {
                $fieldsList[$content['name']] = $content['content_list'];
            }
        }
        $appendFunc = function ($field, $content, &$row) use ($fieldsType) {
            $fieldType = $fieldsType[$field] ?? '';
            if (isset($row[$field])) {
                if (isset($content[$row[$field]])) {
                    $list = [$row[$field] => $content[$row[$field]]];
                } else {
                    $keys = $values = explode(',', $row[$field]);
                    foreach ($values as $index => &$item) {
                        $item = isset($content[$item]) ? $content[$item] : $item;
                    }
                    $list = array_combine($keys, $values);
                }
            } else {
                $list = [];
            }
            $list = array_filter($list);
            $row[$field . '_text'] = $fieldType == 'array' ? $row[$field] : implode(',', $list);
            $row[$field . '_list'] = $fieldType == 'array' ? (array)json_decode($row[$field], true) : $list;
        };
        foreach ($fieldsList as $field => $content) {
            if ($isMultiArray) {
                foreach ($row as $subindex => &$subitem) {
                    $appendFunc($field, $content, $subitem);
                }
            } else {
                $appendFunc($field, $content, $row);
            }
        }

        return $row;
    }

    /**
     * 获取自定义字段关联表数据
     * @param string $source
     * @param int    $source_id
     * @param string $field
     * @param mixed  $key
     * @return string
     */
    public static function getRelationFieldValue($source, $source_id, $field, $key)
    {
        $fieldInfo = Fields::where(['source' => $source, 'source_id' => $source_id, 'name' => $field])->cache(true)->find();
        if (!$fieldInfo) {
            return '';
        }
        $setting = $fieldInfo['setting'];
        if (!$setting || !isset($setting['table'])) {
            return '';
        }
        //显示的字段
        $field = $setting['field'];
        //主键
        $primarykey = $setting['primarykey'];
        //主键值
        $primaryvalue = $key;

        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        $where = [$primarykey => ['in', $primaryvalue]];

        $result = [];
        $datalist = Db::table($setting['table'])->where($where)
            ->field($primarykey . "," . $field)
            ->select();
        foreach ($datalist as $index => &$item) {
            unset($item['password'], $item['salt']);
            $result[] = isset($item[$field]) ? $item[$field] : '';
        }
        return implode(',', $result);
    }

    /**
     * 根据类型获取Model
     * @param string $type
     * @param string $source_id
     * @param array  $with
     * @return null|\think\Model
     * @throws Exception
     */
    public static function getModelByType($type, $source_id = '', $with = [])
    {
        if (!in_array($type, ['page', 'archives', 'special', 'diyform', 'block', 'channel'])) {
            throw new Exception("未找到指定类型");
        }
        $type = ucfirst(strtolower($type));
        $model = model("\\addons\cms\\model\\{$type}");
        if (!$model)
            return null;
        if ($source_id) {
            $model = $model->get($source_id, $with);
        }
        return $model;
    }

    /**
     * 获取缓存标签和时长
     * @param string $type
     * @param array  $tag
     * @return array
     */
    public static function getCacheKeyExpire($type, $tag = [])
    {
        $config = get_addon_config('cms');
        $cache = !isset($tag['cache']) ? $config['cachelifetime'] : $tag['cache'];
        $cache = in_array($cache, ['true', 'false', true, false], true) ? (in_array($cache, ['true', true], true) ? 0 : -1) : (int)$cache;
        $cacheKey = $cache > -1 ? "cms-taglib-{$type}-" . md5(serialize($tag)) : false;
        $cacheExpire = $cache > -1 ? $cache : null;
        return [$cacheKey, $cacheExpire];
    }

    /**
     * 获取分页配置参数
     * @param string $type
     * @param array  $params
     * @return array
     */
    public static function getPaginateParams($type, $params = [])
    {
        $row = empty($params['row']) ? 10 : (int)$params['row'];
        $paginate = !isset($params['paginate']) ? false : $params['paginate'];

        $paginateArr = explode(',', $paginate);
        $listRows = is_numeric($paginate) ? $paginate : (is_numeric($paginateArr[0]) ? $paginateArr[0] : $row);
        $simple = isset($paginateArr[1]) ? $paginateArr[1] : false;
        $simple = in_array($simple, ['true', 'false', true, false], true) ? (in_array($simple, ['true', true], true) ? true : false) : (int)$simple;
        $config = [];
        $config['var_page'] = isset($paginateArr[2]) ? $paginateArr[2] : $type;
        $config['path'] = isset($paginateArr[3]) ? $paginateArr[3] : '';
        $config['fragment'] = isset($paginateArr[4]) ? $paginateArr[4] : '';
        $config['query'] = request()->get();
        $config['type'] = '\\addons\\cms\\library\\Bootstrap';
        return [$listRows, $simple, $config];
    }

    /**
     * 判断来源是否搜索引擎蜘蛛
     */
    public static function isSpider()
    {
        $config = get_addon_config('cms');
        $userAgent = strtolower(request()->server('HTTP_USER_AGENT', ''));
        $spiders = $config['spiders'] ?? [];
        foreach ($spiders as $name => $title) {
            if (stripos($userAgent, $name) !== false) {
                return $name;
            }
        }
        return '';
    }
}
