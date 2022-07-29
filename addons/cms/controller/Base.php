<?php

namespace addons\cms\controller;

use addons\cms\library\IntCode;
use addons\cms\library\Service;
use addons\cms\model\SpiderLog;
use think\Config;
use think\Request;

/**
 * CMS控制器基类
 */
class Base extends \think\addons\Controller
{

    // 初始化
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $config = get_addon_config('cms');
        // 设定主题模板目录
        $this->view->engine->config('view_path', $this->view->engine->config('view_path') . $config['theme'] . DS);
        // 加载自定义标签库
        //$this->view->engine->config('taglib_pre_load', 'addons\cms\taglib\Cms');
        // 默认渲染栏目为空
        $this->view->assign('__CHANNEL__', null);
        $this->view->assign('isWechat', strpos($this->request->server('HTTP_USER_AGENT'), 'MicroMessenger') !== false);

        // 定义CMS首页的URL
        Config::set('cms.indexurl', addon_url('cms/index/index', [], false));
        // 定义分页类
        Config::set('paginate.type', '\\addons\\cms\\library\\Bootstrap');

        //判断站点状态
        if (isset($config['openedsite']) && !in_array('pc', explode(',', $config['openedsite']))) {
            if ($this->controller != 'order' && $this->action != 'epay') {
                $this->error('站点已关闭');
            }
        }
    }

    public function _initialize()
    {
        parent::_initialize();
        // 如果请求参数action的值为一个方法名,则直接调用
        $action = $this->request->post("action");
        if ($action && $this->request->isPost()) {
            return $this->$action();
        }
    }

    /**
     * 是否加密ID处理
     */
    protected function hashids($name = 'id')
    {
        $config = get_addon_config('cms');
        $getValue = $this->request->get($name);
        $postValue = $this->request->post($name);
        if ($config['archiveshashids'] && ($getValue || $postValue)) {
            if ($getValue) {
                $getValue = (int)IntCode::decode($getValue);
                $this->request->get([$name => $getValue]);
            }
            if ($postValue) {
                $postValue = (int)IntCode::decode($postValue);
                $this->request->post([$name => $postValue]);
            }
            $this->request->param('');
        }
    }

}
