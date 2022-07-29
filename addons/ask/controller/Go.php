<?php

namespace addons\ask\controller;

/**
 * 跳转控制器
 * Class Go
 * @package addons\ask\controller
 */
class Go extends Base
{
    protected $layout = 'default';

    public function index()
    {
        $url = $this->request->get("url", "");
        //$this->redirect($url);
        return '
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="referrer" content="never">
<title></title>
</head>
<body>
<script>
    location.href="' . $url . '";
</script>
</body>
</html>';
    }

}
