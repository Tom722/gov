<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Block;
use addons\cms\model\Channel;
use think\Config;
use think\Hook;

/**
 * 公共
 */
class Common extends Base
{
    protected $noNeedLogin = '*';

    /**
     * 初始化
     */
    public function init()
    {
        //焦点图
        $bannerList = [];
        $list = Block::getBlockList(['name' => 'wxappfocus', 'row' => 5]);
        foreach ($list as $index => $item) {
            $bannerList[] = ['image' => cdnurl($item['image'], true), 'url' => $item['url'], 'title' => $item['title']];
        }

        //首页Tab列表
        $indexTabList = $newsTabList = $productTabList = [['id' => 0, 'title' => '全部']];
        $channelList = Channel::where('status', 'normal')
            ->where('type', 'in', ['list'])
            ->field('id,parent_id,model_id,name,diyname')
            ->order('weigh desc,id desc')
            ->select();
        foreach ($channelList as $index => $item) {
            $data = ['id' => $item['id'], 'title' => $item['name']];
            $indexTabList[] = $data;
            if ($item['model_id'] == 1) {
                $newsTabList[] = $data;
            }
            if ($item['model_id'] == 2) {
                $productTabList[] = $data;
            }
        }

        //配置信息
        $upload = Config::get('upload');

        //如果非服务端中转模式需要修改为中转
        if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
            //临时修改上传模式为服务端中转
            set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

            $upload = \app\common\model\Config::upload();
            // 上传信息配置后
            Hook::listen("upload_config_init", $upload);

            $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
        }

        $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
        $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

        $config = [
            'upload' => $upload
        ];

        $data = [
            'bannerList'     => $bannerList,
            'indexTabList'   => $indexTabList,
            'newsTabList'    => $newsTabList,
            'productTabList' => $productTabList,
            'config'         => $config
        ];
        $this->success('', $data);
    }
}
