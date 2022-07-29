<?php

return [
    'autoload' => false,
    'hooks' => [
        'app_init' => [
            'ask',
            'cms',
        ],
        'view_filter' => [
            'ask',
            'clicaptcha',
            'cms',
        ],
        'user_register_successed' => [
            'ask',
        ],
        'user_delete_successed' => [
            'ask',
        ],
        'xunsearch_config_init' => [
            'ask',
            'cms',
        ],
        'xunsearch_index_reset' => [
            'ask',
            'cms',
        ],
        'ask_zone_check' => [
            'ask',
        ],
        'action_begin' => [
            'clicaptcha',
        ],
        'upgrade' => [
            'cms',
        ],
        'user_sidenav_after' => [
            'cms',
            'signin',
        ],
        'leesignhook' => [
            'leesign',
        ],
    ],
    'route' => [
        '/ask/$' => 'ask/index/index',
        '/ask/tags' => 'ask/tag/index',
        '/ask/tag/[:id]' => 'ask/tag/show',
        '/ask/questions' => 'ask/question/index',
        '/ask/question/[:id]' => 'ask/question/show',
        '/ask/articles' => 'ask/article/index',
        '/ask/article/[:id]' => 'ask/article/show',
        '/u/[:id]$' => 'ask/user/index',
        '/u/[:id]/[:dispatch]$' => 'ask/user/dispatch',
        '/ask/experts$' => 'ask/expert/index',
        '/ask/search' => 'ask/search/index',
        '/ask/zones' => 'ask/zone/index',
        '/ask/zone/[:diyname]' => 'ask/zone/show',
        '/cms/$' => 'cms/index/index',
        '/cms/t/[:diyname]$' => 'cms/tag/index',
        '/cms/p/[:diyname]$' => 'cms/page/index',
        '/cms/s$' => 'cms/search/index',
        '/cms/d/[:diyname]$' => 'cms/diyform/index',
        '/cms/d/[:diyname]/post' => 'cms/diyform/post',
        '/cms/d/[:diyname]/[:id]' => 'cms/diyform/show',
        '/cms/special/[:diyname]' => 'cms/special/index',
        '/u/[:id]' => 'cms/user/index',
        '/cms/[:diyname]$' => 'cms/channel/index',
        '/cms/[:catename]/[:id]$' => 'cms/archives/index',
        '/leesign$' => 'leesign/index/index',
    ],
    'priority' => [],
    'domain' => '',
];
