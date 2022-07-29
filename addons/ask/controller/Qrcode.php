<?php

namespace addons\ask\controller;

use think\Response;

class Qrcode extends Base
{

    protected $layout = '';

    //生成二维码
    public function build()
    {
        $info = get_addon_info("qrcode");
        if (!$info || !$info['state']) {
            $this->error("请在后台插件管理中安装并启用《二维码生成插件》后重试");
        }
        $text = $this->request->get('text', 'hello world');
        $size = $this->request->get('size', 250);
        $padding = $this->request->get('padding', 15);
        $errorlevel = $this->request->get('errorlevel', 'medium');
        $foreground = $this->request->get('foreground', "#000000");
        $background = $this->request->get('background', "#ffffff");
        $logo = $this->request->get('logo');
        $logosize = $this->request->get('logosize');
        $label = $this->request->get('label');
        $labelfontsize = $this->request->get('labelfontsize');
        $labelalignment = $this->request->get('labelalignment');

        $params = [
            'text'           => $text,
            'size'           => $size,
            'padding'        => $padding,
            'errorlevel'     => $errorlevel,
            'foreground'     => $foreground,
            'background'     => $background,
            'logo'           => $logo,
            'logosize'       => $logosize,
            'label'          => $label,
            'labelfontsize'  => $labelfontsize,
            'labelalignment' => $labelalignment,
        ];

        $qrCode = \addons\qrcode\library\Service::qrcode($params);

        $response = Response::create()->header("Content-Type", "image/png");

        // 直接显示二维码
        header('Content-Type: ' . $qrCode->getContentType());
        $response->content($qrCode->writeString());
        return $response;
    }

}
