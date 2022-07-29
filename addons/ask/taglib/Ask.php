<?php

namespace addons\ask\taglib;

use fast\Random;
use think\template\TagLib;

class Ask extends TagLib
{

    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'block'        => ['attr' => 'id,name', 'close' => 0],
        'config'       => ['attr' => 'name', 'close' => 0],
        'execute'      => ['attr' => 'sql', 'close' => 0],
        'query'        => ['attr' => 'id,empty,key,mod,sql,cache,bind', 'close' => 1],
        'blocklist'    => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,name', 'close' => 1],
        'userlist'     => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,name', 'close' => 1],
        'questionlist' => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type,aid,pid,fragment', 'close' => 1],
        'articlelist'  => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type,aid,pid,fragment', 'close' => 1],
        'taglist'      => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type', 'close' => 1],
    ];

    public function tagBlock($tag)
    {
        return \addons\ask\model\Block::getBlockContent($tag);
    }

    public function tagConfig($tag)
    {
        $name = $tag['name'];
        $parse = '<?php ';
        $parse .= 'echo \think\Config::get("' . $name . '");';
        $parse .= ' ?>';
        return $parse;
    }

    public function tagExecute($tag, $content)
    {
        $sql = isset($tag['sql']) ? $tag['sql'] : '';
        $sql = addslashes($sql);
        $parse = '<?php ';
        $parse .= '\think\Db::execute(\'' . $sql . '\');';
        $parse .= ' ?>';
        return $parse;
    }

    public function tagQuery($tag, $content)
    {
        $id = isset($tag['id']) ? $tag['id'] : 'item';
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['bind'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);

        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\Question::getQueryList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagPrevNext($tag, $content)
    {
        $id = isset($tag['id']) ? $tag['id'] : 'prevnext';
        $type = isset($tag['type']) ? $tag['type'] : 'prev';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['archives', 'channel'])) {
                $v = $this->autoBuildVar($v);
                $v = preg_match("/^\d+[0-9\,]+\d+$/i", $v) ? '"' . $v . '"' : $v;
            }
        }
        $archives = isset($tag['archives']) ? $tag['archives'] : 0;
        $channel = isset($tag['channel']) ? $tag['channel'] : '';
        $parse = '<?php ';
        $parse .= '$' . $id . ' = \addons\ask\model\Question::getPrevNext("' . $type . '", ' . $archives . ', ' . $channel . ');';
        $parse .= 'if($' . $id . '):';
        $parse .= ' ?>';
        $parse .= $content;
        $parse .= '<?php endif;?>';
        return $parse;
    }

    public function tagBlocklist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['condition'])) {
                $v = $this->autoBuildVar($v);
            }
            $v = '"' . $v . '"';
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\Block::getBlockList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagUserlist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['condition'])) {
                $v = $this->autoBuildVar($v);
            }
            $v = '"' . $v . '"';
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\User::getUserList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagQuestionlist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['condition'])) {
                $v = $this->autoBuildVar($v);
            }
            $v = '"' . $v . '"';
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\Question::getQuestionList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagArticlelist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['condition'])) {
                $v = $this->autoBuildVar($v);
            }
            $v = '"' . $v . '"';
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\Article::getArticleList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    /**
     * 话题列表
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagTaglist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            if (in_array($k, ['condition'])) {
                $v = $this->autoBuildVar($v);
            }
            $v = '"' . $v . '"';
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\ask\model\Tag::getTagList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function autoBuildVar(&$name)
    {
        //如果是字符串则特殊处理
        if (preg_match("/^('|\")(.*)('|\")\$/i", $name, $matches)) {
            $quote = $matches[1] == '"' ? "'" : '"';
            $name = $quote . $matches[2] . $quote;
            return $name;
        }
        return parent::autoBuildVar($name);
    }

}
