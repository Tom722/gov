<?php

namespace addons\cms;

use addons\cms\library\FulltextSearch;
use addons\cms\library\Service;
use addons\cms\model\Archives;
use addons\cms\model\Modelx;
use app\common\library\Auth;
use app\common\library\Menu;
use think\Addons;
use think\Config;
use think\Db;
use think\Loader;
use think\Request;

/**
 * CMS插件
 */
class Cms extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu = include ADDON_PATH . 'cms' . DS . 'data' . DS . 'menu.php';
        Menu::create($menu);

        //首次安装测试数据
        if (version_compare(config('fastadmin.version'), '1.3.0', '<') && Db::name("cms_model")->count() == 0) {
            $this->importTestData();
        }
        return true;
    }

    /**
     * 导入测试数据
     */
    protected function importTestData()
    {
        $sqlFile = ADDON_PATH . 'cms' . DS . 'testdata.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::getPdo()->exec($templine);
                    } catch (\Exception $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('cms');
        return true;
    }

    /**
     * 插件启用方法
     */
    public function enable()
    {
        Menu::enable('cms');

        $prefix = Config::get('database.prefix');
        // 1.4.0表字段升级
        $modelList = Modelx::whereRaw("FIND_IN_SET('price', `fields`)")->select();
        foreach ($modelList as $index => $item) {
            Db::startTrans();
            try {
                //更新表数据
                Db::execute("UPDATE {$prefix}cms_archives a,{$prefix}{$item['table']} e SET a.price = e.price WHERE a.id = e.id");
                //更新表结构
                $field = \app\admin\model\cms\Fields::where('source', 'model')->where('name', 'price')->where('source_id', $item['id'])->find();
                if ($field) {
                    $field->delete();
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
            }
        }
    }

    /**
     * 插件禁用方法
     */
    public function disable()
    {
        Menu::disable('cms');
    }

    /**
     * 插件升级方法
     */
    public function upgrade()
    {
        $menu = include ADDON_PATH . 'cms' . DS . 'data' . DS . 'menu.php';
        Menu::upgrade('cms', $menu);
    }

    /**
     * 应用初始化
     */
    public function appInit()
    {
        $config = get_addon_config('cms');
        $hashids_key_length = $config['hashids_key_length'] ?? 10;
        // 自定义路由变量规则
        \think\Route::pattern([
            'diyname' => "/[a-zA-Z0-9\-_\x{4e00}-\x{9fa5}]+/u",
            'id'      => "\d+",
            'eid'     => "\w{{$hashids_key_length}}",
        ]);

        //添加命名空间
        if (!class_exists('\Hashids\Hashids')) {
            Loader::addNamespace('Hashids', ADDON_PATH . 'cms' . DS . 'library' . DS . 'hashids' . DS);
        }
        $taglib = Config::get('template.taglib_pre_load');
        Config::set('template.taglib_pre_load', ($taglib ? $taglib . ',' : '') . 'addons\\cms\\taglib\\Cms');
        Config::set('cms', $config);
    }

    /**
     * 脚本替换
     */
    public function viewFilter(& $content)
    {
        $request = \think\Request::instance();
        $dispatch = $request->dispatch();

        if ($request->module() || !isset($dispatch['method'][0]) || $dispatch['method'][0] != '\think\addons\Route') {
            return;
        }
        $addon = isset($dispatch['var']['addon']) ? $dispatch['var']['addon'] : $request->param('addon');
        if ($addon != 'cms') {
            return;
        }
        $style = '';
        $script = '';
        $result = preg_replace_callback("/<(script|style)\s(data\-render=\"(script|style)\")([\s\S]*?)>([\s\S]*?)<\/(script|style)>/i", function ($match) use (&$style, &$script) {
            if (isset($match[1]) && in_array($match[1], ['style', 'script'])) {
                ${$match[1]} .= str_replace($match[2], '', $match[0]);
            }
            return '';
        }, $content);
        $content = preg_replace_callback('/^\s+(\{__STYLE__\}|\{__SCRIPT__\})\s+$/m', function ($matches) use ($style, $script) {
            return $matches[1] == '{__STYLE__}' ? $style : $script;
        }, $result ? $result : $content);
    }

    /**
     * 会员中心边栏后
     * @return mixed
     * @throws \Exception
     */
    public function userSidenavAfter()
    {
        $request = Request::instance();
        $controllername = strtolower($request->controller());
        $actionname = strtolower($request->action());
        $config = get_addon_config('cms');
        $sidenav = array_filter(explode(',', $config['usersidenav']));
        if (!$sidenav) {
            return '';
        }
        $user = Auth::instance()->getUser();
        $data = [
            'user'           => $user,
            'controllername' => $controllername,
            'actionname'     => $actionname,
            'sidenav'        => $sidenav
        ];

        return $this->fetch('view/hook/user_sidenav_after', $data);
    }

    public function xunsearchConfigInit()
    {
        return FulltextSearch::config();
    }

    public function xunsearchIndexReset($project)
    {
        if (!$project['isaddon'] || $project['name'] != 'cms') {
            return;
        }
        return FulltextSearch::reset();
    }

}
