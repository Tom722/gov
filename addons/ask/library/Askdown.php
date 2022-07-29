<?php

namespace addons\ask\library;

use addons\ask\model\Notification;
use addons\ask\model\Question;
use app\common\model\User;
use think\Db;

class Askdown
{
    protected static $instance;
    protected $content;
    protected $user = [];
    protected $config = [];
    protected $links = [];
    protected $autolinks = [

    ];

    public function __construct()
    {
        $config = get_addon_config('ask');
        $this->config = $config;
        $this->autolinks = isset($config['autolinks']) ? array_merge($this->autolinks, $config['autolinks']) : $this->autolinks;
    }

    /**
     * 获取单例
     * @param array $options
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 发送通知
     * @param string $type 类型
     * @param string $source_id 来源ID
     * @throws \think\Exception
     */
    public function notification($type, $source_id)
    {
        foreach ($this->user as $index => $user) {
            $model = Service::getModelByType($type, $source_id);
            if ($model && isset($model['user_id'])) {
                $title = isset($model['title']) ? $model['title'] : '';
                $title = $title ? $title : ($type == 'answer' ? $model->question->title : "");
                Notification::record($title, "", 'at', $type, $source_id, $user->id, $model->user_id);
            }
        }
    }

    /**
     * 获取替换后的内容
     * @param string $content 内容
     * @return mixed
     */
    public function format($content)
    {
        $this->content = $content;

        //替换@
        $this->at();

        //替换问题
        $this->question();

        //Markdown
        $this->markdown();

        //替换关键字
        $this->keyword();

        return $this->content;
    }

    //@用户
    public function at()
    {
        $this->begin();
        $this->content = preg_replace_callback('/\B@(((?!\s).)*)/i', function ($match) {
            $user = User::getByUsername($match[1]);
            if ($user) {
                $this->user[] = $user;
                return '<a href="' . $user->url . '" data-type="user" data-id="' . $user->id . '" data-toggle="popover" data-title="' . $user->nickname . '">' . $match[0] . '</a>';
            } else {
                return $match[0];
            }
        }, $this->content);
        $this->end();
        return $this;
    }

    //#话题
    public function question()
    {
        $this->begin();
        $this->content = preg_replace_callback('/\B#([0-9]+)\[(.*?)\](\(([A-Z]+)\))?/i', function ($match) {
            $typeArr = ['Q' => 'question', 'A' => 'article', 'T' => 'tag'];
            $type = isset($match[4]) && in_array($match[4], ['Q', 'A', 'T']) ? $typeArr[$match[4]] : 'question';
            $model = Service::getModelByType($type, $match[1]);;
            if ($model) {
                return '<a href="' . $model->url . '" data-type="' . $type . '" data-id="' . $model->id . '">#' . ($type == 'answer' ? $model->name : $model->title) . '</a>';
            } else {
                return $match[0];
            }
        }, $this->content);
        $this->end();
        return $this;
    }

    public function markdown()
    {
        //Markdown格式化
        $markdown = Hyperdown::instance();
        $this->content = $markdown->makeHtml($this->content);

        return $this;
    }

    //关键字
    public function keyword()
    {
        $this->begin();
        $values = $this->autolinks;
        $this->content = preg_replace_callback('/(' . implode('|', array_keys($values)) . ')/i', function ($match) use ($values) {
            if (!isset($values[$match[1]])) {
                return $match[0];
            } else {
                return '<a href="' . $values[$match[1]] . '" target="_blank">' . $match[0] . '</a>';
            }
        }, $this->content);
        $this->end();
        return $this;
    }

    protected function begin()
    {
        $links = [];
        $this->content = preg_replace_callback('~(<a .*?>.*?</a>|<pre[\s\S]?.*?>.*?</pre>|<code[\s\S]?.*?>.*?</code>|<.*?>)~is', function ($match) use (&$links) {
            return '<' . array_push($links, $match[1]) . '>';
        }, $this->content);
        $this->links = $links;
    }

    protected function end()
    {
        $links = $this->links;
        $this->content = preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) {
            return $links[$match[1] - 1];
        }, $this->content);
    }
}
