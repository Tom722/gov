<?php

namespace addons\ask;

use addons\ask\library\FulltextSearch;
use addons\ask\model\User;
use app\common\library\Menu;
use think\Addons;
use think\Config;
use think\Request;

/**
 * 知识问答插件
 */
class Ask extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        $menu =
            [
                [
                    "name"    => "ask",
                    "title"   => "问答管理",
                    "icon"    => "fa fa-question-circle",
                    "ismenu"  => 1,
                    "sublist" => [
                        [
                            'name'    => 'ask/config',
                            'title'   => '站点配置',
                            'icon'    => 'fa fa-gears',
                            'ismenu'  => 1,
                            'weigh'   => '22',
                            'sublist' => [
                                ['name' => 'ask/config/index', 'title' => '修改'],
                            ],
                        ],
                        [
                            "name"    => "ask/answer",
                            "title"   => "回答管理",
                            "icon"    => "fa fa-comment",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/answer/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/answer/restore",
                                    "title" => "还原"
                                ],
                                [
                                    "name"  => "ask/answer/recyclebin",
                                    "title" => "回收站"
                                ],
                                [
                                    "name"  => "ask/answer/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/answer/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/answer/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/answer/destroy",
                                    "title" => "真实删除"
                                ],
                                [
                                    "name"  => "ask/answer/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/article",
                            "title"   => "文章管理",
                            "icon"    => "fa fa-file-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/article/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/article/restore",
                                    "title" => "还原"
                                ],
                                [
                                    "name"  => "ask/article/recyclebin",
                                    "title" => "回收站"
                                ],
                                [
                                    "name"  => "ask/article/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/article/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/article/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/article/destroy",
                                    "title" => "真实删除"
                                ],
                                [
                                    "name"  => "ask/article/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/block",
                            "title"   => "区块管理",
                            "icon"    => "fa fa-file-text-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/block/index",
                                    "title" => "查看"
                                ],
                                [
                                    "name"  => "ask/block/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/block/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/block/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/block/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/category",
                            "title"   => "分类管理",
                            "icon"    => "fa fa-archive",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/category/index",
                                    "title" => "查看"
                                ],
                                [
                                    "name"  => "ask/category/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/category/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/category/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/category/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/certification",
                            "title"   => "认证管理",
                            "icon"    => "fa fa-id-card-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/certification/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/certification/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/certification/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/certification/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/certification/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/comment",
                            "title"   => "评论管理",
                            "icon"    => "fa fa-comments",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/comment/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/comment/restore",
                                    "title" => "还原"
                                ],
                                [
                                    "name"  => "ask/comment/recyclebin",
                                    "title" => "回收站"
                                ],
                                [
                                    "name"  => "ask/comment/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/comment/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/comment/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/comment/destroy",
                                    "title" => "真实删除"
                                ],
                                [
                                    "name"  => "ask/comment/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/feed",
                            "title"   => "动态管理",
                            "icon"    => "fa fa-coffee",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/feed/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/feed/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/feed/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/feed/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/feed/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/message",
                            "title"   => "消息管理",
                            "icon"    => "fa fa-commenting-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/message/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/message/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/message/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/message/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/message/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/notification",
                            "title"   => "通知管理",
                            "icon"    => "fa fa-bell-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/notification/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/notification/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/notification/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/notification/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/notification/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/order",
                            "title"   => "付费订单",
                            "icon"    => "fa fa-list",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/order/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/order/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/order/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/order/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/order/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/question",
                            "title"   => "问题管理",
                            "icon"    => "fa fa-question-circle-o",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/question/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/question/restore",
                                    "title" => "还原"
                                ],
                                [
                                    "name"  => "ask/question/recyclebin",
                                    "title" => "回收站"
                                ],
                                [
                                    "name"  => "ask/question/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/question/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/question/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/question/destroy",
                                    "title" => "真实删除"
                                ],
                                [
                                    "name"  => "ask/question/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/report",
                            "title"   => "举报管理",
                            "icon"    => "fa fa-exclamation-triangle",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/report/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/report/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/report/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/report/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/report/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/statistics",
                            "title"   => "统计管理",
                            "icon"    => "fa fa-bar-chart",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/statistics/index",
                                    "title" => "查询统计"
                                ],
                                [
                                    "name"  => "ask/statistics/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/statistics/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/statistics/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/statistics/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/tag",
                            "title"   => "话题管理",
                            "icon"    => "fa fa-tag",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/tag/restore",
                                    "title" => "还原"
                                ],
                                [
                                    "name"  => "ask/tag/index",
                                    "title" => "查看"
                                ],
                                [
                                    "name"  => "ask/tag/recyclebin",
                                    "title" => "回收站"
                                ],
                                [
                                    "name"  => "ask/tag/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/tag/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/tag/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/tag/destroy",
                                    "title" => "真实删除"
                                ],
                                [
                                    "name"  => "ask/tag/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/thanks",
                            "title"   => "感谢管理",
                            "icon"    => "fa fa-heart",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/thanks/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/thanks/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/thanks/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/thanks/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/thanks/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/user",
                            "title"   => "会员管理",
                            "icon"    => "fa fa-user",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/user/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/user/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/user/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/user/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/user/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ],
                        [
                            "name"    => "ask/zone",
                            "title"   => "专区管理",
                            "icon"    => "fa fa-star",
                            "ismenu"  => 1,
                            "sublist" => [
                                [
                                    "name"  => "ask/zone/index",
                                    "title" => "首页"
                                ],
                                [
                                    "name"  => "ask/zone/add",
                                    "title" => "添加"
                                ],
                                [
                                    "name"  => "ask/zone/edit",
                                    "title" => "编辑"
                                ],
                                [
                                    "name"  => "ask/zone/del",
                                    "title" => "删除"
                                ],
                                [
                                    "name"  => "ask/zone/multi",
                                    "title" => "批量更新"
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        Menu::create($menu);
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        Menu::delete('ask');
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        Menu::enable('ask');
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        Menu::disable('ask');
        return true;
    }

    /**
     * 应用初始化
     */
    public function appInit()
    {
        $config = get_addon_config('ask');
        $taglib = Config::get('template.taglib_pre_load');
        Config::set('template.taglib_pre_load', ($taglib ? $taglib . ',' : '') . 'addons\\ask\\taglib\\Ask');
        Config::set('ask', $config);
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
        if ($addon != 'ask') {
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
     * 创建附表会员记录
     * @param \app\common\model\User $user
     */
    public function userRegisterSuccessed(\app\common\model\User $user)
    {
        $data = [
            'user_id' => $user->id
        ];
        User::create($data);
    }

    /**
     * 删除附表会员记录
     * @param \app\common\model\User $user
     */
    public function userDeleteSuccessed(\app\common\model\User $user)
    {
        User::where('user_id', $user->id)->delete();
    }

    public function xunsearchConfigInit()
    {
        return FulltextSearch::config();
    }

    public function xunsearchIndexReset($project)
    {
        if (!$project['isaddon'] || $project['name'] != 'ask') {
            return;
        }
        return FulltextSearch::reset();
    }

    /**
     * 问答专区验证钩子
     * @param array $zoneProductList
     * @return bool
     */
    public function askZoneCheck(&$zoneProductList)
    {
        //你需要在此编写专区验证代码
        //返回true表示允许进入此专区
        //返回false表示不允许进入此专区
        return false;
    }

}
