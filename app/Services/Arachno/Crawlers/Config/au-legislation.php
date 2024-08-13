<?php

use App\Services\Arachno\Support\DomElementClass;

/*
|--------------------------------------------------------------------------
| Slug: au-legislation
| Title: AU Federal Legislation
| URL: https://www.legislation.gov.au
|--------------------------------------------------------------------------
*/

return array_merge(require ('default.php'), [
    'start_urls' => [
        [
            'url' => 'https://www.legislation.gov.au/Details/C2012C00168',
        ],
    ],
    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36',
    'throttle_requests' => 800,
    'follow_links_queries' => [],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'regex',
                'source' => 'url',
                'query' => '/\/Html\/Text$/',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'css',
                    'query' => '#ctl00_MainContent_RadMultiPage1 #MainContent_RadPageHTML #MainContent_Panel1 #MainContent_pnlHtmlControls > style',
                ],
                [
                    'type' => 'css',
                    'query' => '#ctl00_MainContent_RadMultiPage1 #MainContent_RadPageHTML #MainContent_Panel1 #MainContent_pnlHtmlControls > div',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                // more meta tags needed here... but just testing...
            ],
            'capture_remove_queries' => [],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.au\/Details\/[A-Z0-9]+$/',
    ],
    'unique_id_query' => [
        'type' => 'text',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.au\/Details\/([A-Z0-9]+)$/',
    ],
    'title_query' => [
        'type' => 'xpath',
        'query' => '//head//title',
    ],
    'is_toc_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^https:\/\/www\.legislation\.gov\.au\/Details\/[A-Z0-9]+$/',
    ],
    'toc_item_criteria' => function ($nodeDTO) {
        $n = $nodeDTO['node'];

        return (strtolower($n->nodeName) === 'a' || strtolower($n->nodeName) === 'span') && DomElementClass::hasClass($n, 'MainContent_ctl16_trTOC_0');
    },
    'toc_query' => [
        'type' => 'css',
        'query' => '#MainContent_divLeftBottom #MainContent_pnlForTOC #MainContent_ctl16_divLeftBottom #MainContent_ctl16_trTOC',
    ],
]);
