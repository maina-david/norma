<?php

/*
|--------------------------------------------------------------------------
| Slug: br-trabalho-gov-br
| Title: Brazil: Ministério do Trabalho e Previdência
| URL: https://sit.trabalho.gov.br/
|--------------------------------------------------------------------------
*/

return array_merge(require ('default.php'), [
    'start_urls' => [
        [
            'url' => 'http://www4.planalto.gov.br/legislacao/portal-legis/legislacao-1/codigos-1',
            'needs_browser' => true,
        ],
    ],
    'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36',
    'verify_ssl' => false,
    'throttle_requests' => 300,
    'follow_links_queries' => [
        [
            'type' => 'css',
            'query' => '#portal-column-content #content #content-core a.external-link',
        ],
    ],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'regex',
                'source' => 'url',
                'query' => '/^http:\/\/www\.planalto\.gov\.br\/.*\.htm$/',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//body/*[not(local-name()="script")]',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//link[not(contains(@href, "font.css")) and not(contains(@href, "fontawesome"))]',
                ],
                // more meta tags needed here... but just testing...
            ],
            'capture_remove_queries' => [],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/^http:\/\/www\.planalto\.gov\.br\/.*\.htm$/',
    ],
    'unique_id_query' => [
        'type' => 'text',
        'source' => 'url',
        'query' => '/^http:\/\/www\.planalto\.gov\.br\/ccivil_03(.*)\.htm$/',
    ],
    'title_query' => [
        'type' => 'xpath',
        'query' => '//body//p[@align="CENTER" or @align="center"][1]',
    ],
]);
