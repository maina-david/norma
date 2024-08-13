<?php

/*
|--------------------------------------------------------------------------
| Slug: us-qcode
| Title: Quality Code Publishing
| URL: http://www.qcode.us/codes.html
|--------------------------------------------------------------------------
*/

return array_merge(require ('default.php'), [
    'start_urls' => [
        [
            'url' => 'http://qcode.us/codes/americancanyon/view.php?view=mobile',
        ],
        [
            'url' => 'http://qcode.us/codes/artesia/view.php?view=mobile',
        ],
        [
            'url' => 'http://qcode.us/codes/atascadero/view.php?view=mobile',
        ],
        // [
        //    'url' => 'http://qcode.us/codes/basin/view.php?view=mobile',
        // ],
        // [
        //    'url' => 'http://qcode.us/codes/bell/view.php?view=mobile',
        // ],
        // [
        //    'url' => 'http://qcode.us/codes/bellflower/view.php?view=mobile',
        // ],
    ],
    'default_cookies' => [
        'view' => 'mobile',
    ],
    'follow_links_queries' => [],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'css',
                'query' => '.content .textContent .content-fragment',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'css',
                    'query' => '.content .currentTopic',
                ],
                [
                    'type' => 'css',
                    'query' => '.content .textContent',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//link[@rel="stylesheet" and contains(@href, "view-common.css")]',
                ],
            ],
            'capture_remove_queries' => [
                [
                    'type' => 'css',
                    'query' => '.content .noprint',
                ],
            ],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^.*qcode\.us\/codes\/.+\/view\.php\?view=mobile$/',
    ],
    'unique_id_query' => [
        'type' => 'text',
        'source' => 'url',
        'query' => '/^.*qcode\.us\/codes\/(.+)\/view\.php\?view=mobile$/',
    ],
    'title_query' => [
        'type' => 'xpath',
        'query' => '//head//title',
    ],
    'is_toc_page_query' => [
        'type' => 'css',
        'query' => '.content>ul.list-group:nth-of-type(1)>li',
    ],
    'toc_query' => [
        'type' => 'css',
        'query' => '.content>ul.list-group:nth-of-type(1)',
    ],
]);
