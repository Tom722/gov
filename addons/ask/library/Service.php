<?php

namespace addons\ask\library;

use addons\ask\library\aip\AipNlp;
use addons\ask\library\aip\AipContentCensor;
use app\common\library\Auth;
use app\common\library\Email;
use app\common\model\User;
use DOMDocument;
use think\Config;
use think\Exception;
use think\Queue;
use think\View;

class Service
{
    /**
     * 根据类型获取Model
     * @param string $type
     * @param string $source_id
     * @param array  $with
     * @return null|\think\Model
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public static function getModelByType($type, $source_id = '', $with = [])
    {
        if (!in_array($type, ['question', 'answer', 'comment', 'thanks', 'collection', 'attention', 'tag', 'article'])) {
            throw new Exception("未找到指定类型");
        }
        $type = ucfirst(strtolower($type));
        $model = model("\\addons\ask\\model\\{$type}");
        if (!$model)
            return null;
        if ($source_id) {
            $model = $model->get($source_id, $with);
        }
        return $model;
    }

    /**
     * 检测内容是否合法
     * @param string $content 检测内容
     * @param string $type    类型
     * @return bool
     */
    public static function isContentLegal($content, $type = null)
    {
        $config = get_addon_config('ask');
        $type = is_null($type) ? $config['audittype'] : $type;
        $content = Askdown::instance()->format($content);
        if ($type == 'local') {
            // 敏感词过滤
            $handle = SensitiveHelper::init()->setTreeByFile(ADDON_PATH . 'ask/data/words.dic');
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
     * @param string $title
     * @return array
     */
    public static function getContentTags($title)
    {
        $arr = [];
        $config = get_addon_config('ask');
        if ($config['nlptype'] == 'local') {
            !defined('_VIC_WORD_DICT_PATH_') && define('_VIC_WORD_DICT_PATH_', ADDON_PATH . 'ask/data/dict.json');
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
     * 获取内容中所有的图片
     * @param string $html
     * @return array
     */
    public static function getContentImages($html)
    {
        $result = [];
        try {
            $htmlDom = new DOMDocument;
            $htmlDom->loadHTML($html);
            $imageTags = $htmlDom->getElementsByTagName('img');
            $result = [];
            foreach ($imageTags as $imageTag) {
                $result[] = $imageTag->getAttribute('src');
            }
        } catch (\Exception $e) {

        }
        return $result;
    }

    /**
     * 发送邮箱
     * @param string $email    邮箱
     * @param string $subject  主题
     * @param mixed  $content  内容
     * @param null   $event    事件
     * @param null   $template 模板
     * @return bool
     */
    public static function sendEmail($email, $subject, $content, $event = null, $template = null)
    {
        if (!$email) {
            return false;
        }
        $config = get_addon_config('ask');
        if ($event && !in_array($event, explode(',', $config['emailnotice']))) {
            return false;
        }
        $template = $template ? $template : "common/email";
        if (!is_array($content)) {
            $content = ['content' => $content];
        }
        if (is_numeric($email)) {
            $user = User::get($email);
            if ($user && $user->email) {
                $email = $user->email;
                $content['nickname'] = $user->nickname;
            } else {
                return false;
            }
        }
        $view = new View(Config::get('template'), Config::get('view_replace_str'));
        $view->config('view_path', ADDON_PATH . 'ask' . DS . 'view' . DS);
        $content['siteurl'] = addon_url('ask/index/index', '', false, true);

        //去除类似admin.php，避免暴露后台地址
        $content['siteurl'] = preg_replace("/\/([a-zA-Z0-9]+)\.php\//i", '/', $content['siteurl']);
        if (isset($content['url'])) {
            $content['url'] = preg_replace("/\/([a-zA-Z0-9]+)\.php\//i", '/', $content['url']);
        }
        $nickname = isset($content['nickname']) ? $content['nickname'] : '';
        $url = isset($content['url']) ? $content['url'] : '';
        $view->assign($content);
        $content = $view->fetch($template);

        $result = false;
        $config = get_addon_config('ask');
        try {
            if ($config['sendemailmode'] == 'queue') {
                if (extension_loaded('redis') && class_exists('\think\Queue')) {
                    //使用队列发送邮件
                    $jobData = ['subject' => $subject, 'content' => $content, 'email' => $email, 'username' => $nickname, 'url' => $url];
                    $result = Queue::push('addons\ask\controller\queue\Email', $jobData, 'emailQueue');
                }
            } else {
                //常规发送邮件
                $object = new Email();
                $result = $object->to($email)
                    ->subject($subject)
                    ->message($content)
                    ->send();
            }
        } catch (\Exception $e) {
            $result = false;
        }
        return $result ? true : false;
    }

    /**
     * 判断是否前台管理员
     * @return bool
     */
    public static function isAdmin()
    {
        $auth = Auth::instance();
        $config = get_addon_config('ask');
        return $config['admin_user_ids'] && in_array($auth->id, explode(',', $config['admin_user_ids']));
    }

    /**
     * 获取Markdown格式化后的内容
     * @param $content
     * @return null|string|string[]
     */
    public static function getFormatContent($content)
    {
        $markdown = Hyperdown::instance();
        $html = $markdown->makeHtml($content);

        $html = self::linkify($html);

        return $html;
    }

    /**
     * 内容自动加链接
     */
    public static function linkify($value, $protocols = ['http', 'https', 'ftp'])
    {
        $links = [];

        $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $value);

        foreach ($protocols as $protocol) {
            switch ($protocol) {
                case 'http':
                case 'https':
                    $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links) {
                        if ($match[1]) $protocol = $match[1];
                        $link = $match[2] ?: $match[3];
                        $nofollow = stripos($link, request()->host()) !== false ? '' : ' rel="nofollow"';
                        return '<' . array_push($links, "<a target=\"_blank\"$nofollow href=\"$protocol://$link\">$link</a>") . '>';
                    }, $value);
                    break;
                case 'mail':
                    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links) {
                        return '<' . array_push($links, "<a target=\"_blank\" href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>';
                    }, $value);
                    break;
                default:
                    $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links) {
                        return '<' . array_push($links, "<a target=\"_blank\" href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>';
                    }, $value);
                    break;
            }
        }

        return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $value);
    }

}
