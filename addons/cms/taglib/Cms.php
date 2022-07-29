<?php

namespace addons\cms\taglib;

use fast\Random;
use think\Cache;
use think\template\TagLib;

class Cms extends TagLib
{

    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'channel'     => ['attr' => 'name', 'close' => 0],
        'archives'    => ['attr' => 'name', 'close' => 0],
        'special'     => ['attr' => 'name', 'close' => 0],
        'tag'         => ['attr' => 'name', 'close' => 0],
        //@deprecated use tag instead
        'tags'        => ['attr' => 'name', 'close' => 0],
        'block'       => ['attr' => 'id,name,field', 'close' => 0],
        'config'      => ['attr' => 'name', 'close' => 0],
        'page'        => ['attr' => 'name', 'close' => 0],
        'diyform'     => ['attr' => 'name', 'close' => 0],
        'nav'         => ['attr' => 'name,maxlevel,condition,cache', 'close' => 0],
        'execute'     => ['attr' => 'sql,bind', 'close' => 0],
        'query'       => ['attr' => 'id,empty,key,mod,sql,cache,bind', 'close' => 1],
        'prevnext'    => ['attr' => 'id,empty,type,archives,channel,condition', 'close' => 1],
        'blocklist'   => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,type,name,paginate', 'close' => 1],
        'commentlist' => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type,aid,pid,fragment', 'close' => 1],
        'breadcrumb'  => ['attr' => 'id,empty,key,mod', 'close' => 1],
        'channelinfo' => ['attr' => 'id,cid,empty,cache,imgwidth,imgheight,orderby,orderway,condition', 'close' => 1],
        'channellist' => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,model,type,typeid,field,paginate', 'close' => 1],
        'arclist'     => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,model,type,special,field,flag,channel,tags,addon,paginate', 'close' => 1],
        'speciallist' => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,flag,paginate', 'close' => 1],
        'taglist'     => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type,paginate', 'close' => 1],
        //@deprecated use taglist instead
        'tagslist'    => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,type,paginate', 'close' => 1],
        'userlist'    => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,name,paginate', 'close' => 1],
        'diydatalist' => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,condition,diyform,paginate', 'close' => 1],
        'pagefilter'  => ['attr' => 'id,empty,key,mod', 'close' => 1],
        'pageorder'   => ['attr' => 'id,empty,key,mod', 'close' => 1],
        'pagelist'    => ['attr' => 'id,empty,key,mod,imgwidth,imgheight,paginate', 'close' => 1],
        'spagelist'   => ['attr' => 'id,row,limit,empty,key,mod,cache,orderby,orderway,imgwidth,imgheight,condition,type,paginate', 'close' => 1],
        'spageinfo'   => ['attr' => 'id,sid,empty,cache,imgwidth,imgheight,orderby,orderway,condition', 'close' => 1],
        'pageinfo'    => ['attr' => 'type', 'close' => 0],
        'commentinfo' => ['attr' => 'type', 'close' => 0],
    ];

    public function tagBreadcrumb($tag, $content)
    {
        $id = isset($tag['id']) ? $tag['id'] : 0;
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';

        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Channel::getBreadcrumb($__CHANNEL__??[], $__ARCHIVES__??[], $__TAGS__??[], $__PAGE__??[], $__DIYFORM__??[], $__SPECIAL__??[]);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagExecute($tag, $content)
    {
        $sql = isset($tag['sql']) ? $tag['sql'] : '';
        $bind = isset($tag['bind']) ? $tag['bind'] : '';
        $bind = explode(',', $bind);
        $sql = addslashes($sql);
        $parse = '<?php ';
        $parse .= '\think\Db::execute(\'' . $sql . '\', ' . json_encode($bind) . ');';
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
        $parse .= '$__' . $var . '__ = \addons\cms\model\Archives::getQueryList([' . implode(',', $params) . ']);';
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
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['archives', 'channel', 'condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '<?php ';
        $parse .= '$' . $id . ' = \addons\cms\model\Archives::getPrevNext([' . implode(',', $params) . ']);';
        $parse .= 'if($' . $id . '):';
        $parse .= ' ?>';
        $parse .= $content;
        $parse .= '<?php else:?>';
        $parse .= $empty;
        $parse .= '<?php endif;?>';
        return $parse;
    }

    public function tagChannel($tag)
    {
        return '{$__CHANNEL__.' . $tag['name'] . '}';
    }

    public function tagArchives($tag)
    {
        return '{$__ARCHIVES__.' . $tag['name'] . '}';
    }

    public function tagSpecial($tag)
    {
        return '{$__SPECIAL__.' . $tag['name'] . '}';
    }

    public function tagPage($tag)
    {
        return '{$__PAGE__.' . $tag['name'] . '}';
    }

    public function tagDiyform($tag)
    {
        return '{$__DIYFORM__.' . $tag['name'] . '}';
    }

    /**
     * @deprecated use tagTag instead
     */
    public function tagTags($tag)
    {
        return $this->tagTag($tag);
    }

    public function tagTag($tag)
    {
        return '{$__TAG__.' . $tag['name'] . '}';
    }

    public function tagBlock($tag)
    {
        return \addons\cms\model\Block::getBlockContent($tag);
    }

    public function tagNav($tag)
    {
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Channel::getNav(isset($__CHANNEL__)?$__CHANNEL__:[], [' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{$__' . $var . '__}';
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
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Block::getBlockList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagPagefilter($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = $__FILTERLIST__;';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagPageorder($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = $__ORDERLIST__;';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagPagelist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';

        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = $__PAGELIST__;';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagSpagelist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';

        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Page::getPageList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagSpageinfo($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';

        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '<?php ';
        $parse .= '$' . $id . ' = \addons\cms\model\Page::getPageInfo([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{if $' . $id . '}';
        $parse .= $content;
        $parse .= '{else /}';
        $parse .= '<?php echo "' . $empty . '" ;?>';
        $parse .= '{/if}';
        return $parse;
    }

    public function tagPageinfo($tag, $content)
    {
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '{$__PAGELIST__->render([' . implode(',', $params) . '])}';
        return $parse;
    }

    /**
     * 标签列表
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
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Tag::getTagList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }


    /**
     * @deprecated use tagTaglist instead
     */
    public function tagTagslist($tag, $content)
    {
        return $this->tagTaglist($tag, $content);
    }

    /**
     * 评论列表
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagCommentlist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '<?php ';
        $parse .= '$__COMMENTLIST__ = \addons\cms\model\Comment::getCommentList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__COMMENTLIST__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__COMMENTLIST__;{/php}';
        return $parse;
    }

    /**
     * 评论分页
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagCommentinfo($tag, $content)
    {
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '{$__COMMENTLIST__->render([' . implode(',', $params) . '])}';
        return $parse;
    }

    /**
     * 获取单个栏目信息
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagChannelinfo($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';

        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $parse = '<?php ';
        $parse .= '$' . $id . ' = \addons\cms\model\Channel::getChannelInfo([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{if $' . $id . '}';
        $parse .= $content;
        $parse .= '{else /}';
        $parse .= '<?php echo "' . $empty . '" ;?>';
        $parse .= '{/if}';
        return $parse;
    }

    /**
     * 栏目标签
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagChannellist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $paginate = !isset($tag['paginate']) ? false : $tag['paginate'];
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['typeid', 'model', 'condition', 'special'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Channel::getChannelList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        if ($paginate) {
            $parse .= '{php}$__PAGELIST__=$__' . $var . '__;{/php}';
        }

        return $parse;
    }

    public function tagArclist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['channel', 'model', 'condition', 'tags'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Archives::getArchivesList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    /**
     * 专题列表
     * @param array  $tag
     * @param string $content
     * @return string
     */
    public function tagSpeciallist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Special::getSpecialList([' . implode(',', $params) . ']);';
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
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\User::getUserList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagDiydatalist($tag, $content)
    {
        $id = $tag['id'];
        $empty = isset($tag['empty']) ? $tag['empty'] : '';
        $key = !empty($tag['key']) ? $tag['key'] : 'i';
        $mod = isset($tag['mod']) ? $tag['mod'] : '2';
        $params = [];
        foreach ($tag as $k => & $v) {
            $origin = $v;
            if (in_array($k, ['condition'])) {
                $this->autoBuildVar($v);
            }
            $v = $origin == $v ? '"' . $v . '"' : $v;
            $params[] = '"' . $k . '"=>' . $v;
        }
        $var = Random::alnum(10);
        $parse = '<?php ';
        $parse .= '$__' . $var . '__ = \addons\cms\model\Diyform::getDiydataList([' . implode(',', $params) . ']);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__' . $var . '__" id="' . $id . '" empty="' . $empty . '" key="' . $key . '" mod="' . $mod . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        $parse .= '{php}$__LASTLIST__=$__' . $var . '__;{/php}';
        return $parse;
    }

    public function tagConfig($tag)
    {
        $name = $tag['name'];
        $parse = '{$Think.config.' . $name . '}';
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
