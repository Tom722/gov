<?php

namespace addons\cms\controller\wxapp;

use addons\cms\model\Archives;
use addons\cms\model\Block;
use addons\cms\model\Channel;

/**
 * 首页
 */
class Index extends Base
{
    protected $noNeedLogin = '*';

    /**
     * 首页
     */
    public function index()
    {        
        $archivesList = Archives::getArchivesList(['cache' => false]);
        $archivesList = collection($archivesList)->toArray();
        foreach ($archivesList as $index => &$item) {
            $item['url'] = $item['fullurl'];
            //小程序只显示3张图
            $item['images_list'] = array_slice(array_filter(explode(',', $item['images'])), 0, 3);
            unset($item['imglink'], $item['textlink'], $item['channellink'], $item['taglist'], $item['weigh'], $item['status'], $item['deletetime'], $item['memo'], $item['img']);
        }
        $data = [            
            'archivesList' => $archivesList
        ];
        $this->success('', $data);
    }
}
