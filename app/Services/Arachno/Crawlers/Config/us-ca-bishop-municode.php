<?php

/*
|--------------------------------------------------------------------------
| Slug: us-municode
| Title: municode.com
| URL: https://library.municode.com
|--------------------------------------------------------------------------
*/

return array_merge(require ('default.php'), [
    'start_urls' => [
        [
            'url' => 'https://library.municode.com/ca/bishop/codes/code_of_ordinances',
            'wait_for' => '#toc .toc-wrapper ul li',
            'needs_browser' => true,
        ],
    ],
    'follow_links_queries' => [],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'css',
                'query' => '#codesContentContainer #codesContent',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'css',
                    'query' => '#codesContentContainer #codesContent',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//link[contains(@href, "dist/css")]',
                ],
            ],
            'capture_remove_queries' => [
                [
                    'type' => 'css',
                    'query' => '.table-toolbar, .mcc_codes_content_action_bar, .alert, .chunk-nav',
                ],
            ],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/library\.municode\.com\/[a-z]{2}\/.*\/[^?]+$/',
    ],
    'unique_id_query' => [
        'type' => 'text',
        'source' => 'url',
        'query' => '/library\.municode\.com\/(.*)$/',
    ],
    'title_query' => [
        'type' => 'xpath',
        'query' => '//*[@id="toc"]//*[@id="genToc"]//ul/li[1]/a/span[1]',
        'transforms' => [
            ['function' => 'title'],
            ['function' => 'replace', 'args' => [' Of ', ' of ']],
        ],
    ],
    'is_toc_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/library\.municode\.com\/[a-z]{2}\/.*\/[^?]+$/',
    ],
    'toc_query' => [
        'type' => 'css',
        'query' => '#toc .toc-wrapper',
        'wait_for' => '#codesContent .chunks',
    ],
]);
