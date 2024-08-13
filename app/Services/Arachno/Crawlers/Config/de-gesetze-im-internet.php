<?php

/**
 * Slug: de-gesetze-im-internet
 * Title: Gesetze-im-internet.de
 * URL: https://www.gesetze-im-internet.de/.
 */
return array_merge(require ('default.php'), [
    'slug' => 'de-gesetze-im-internet',
    'start_urls' => [
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_A.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_9.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_8.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_7.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_6.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_5.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_4.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_3.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_2.html',
        ],
        [
            'url' => 'https://www.gesetze-im-internet.de/Teilliste_1.html',
        ],
    ],
    'follow_links_queries' => [
        [
            // example page this runs on: https://www.gesetze-im-internet.de/Teilliste_9.html
            'type' => 'xpath',
            'query' => '//*[@id="level2"]//*[@id="container"]//*[@id="paddingLR12"]//*[local-name()="p"]//*[local-name()="a"][1]/*[local-name()="abbr"]/..',
            // temporarily just fetch one work
            // 'query' => '//*[@id="level2"]//*[@id="container"]//*[@id="paddingLR12"]//*[local-name()="p"][2]//*[local-name()="a"][1]/*[local-name()="abbr"]/..'
        ],
    ],
    'capture_queries' => [
        [
            'is_capture_page_query' => [
                'type' => 'xpath',
                'query' => '//*[@id="container"]//*[@class="jnheader"]//*[text()="Nichtamtliches Inhaltsverzeichnis"]',
            ],
            'capture_body_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//*[@id="container"]//*[@id="paddingLR12"]',
                ],
            ],
            'capture_head_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//head//title',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//head//link[not(@media) or @media="screen"]',
                ],
            ],
            'capture_remove_queries' => [
                [
                    'type' => 'xpath',
                    'query' => '//*[@id="paddingLR12"]//*[text()="Nichtamtliches Inhaltsverzeichnis"]',
                ],
                [
                    'type' => 'xpath',
                    'query' => '//*[@id="paddingLR12"]/div[@class="jnheader"]/h1/text()[1]', // removes the work heading from each section
                ],
            ],
        ],
    ],
    'is_meta_page_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/\/index\.html$/',
    ],
    'unique_id_query' => [
        'type' => 'regex',
        'source' => 'url',
        'query' => '/www\.gesetze\-im\-internet\.de\/(.*)\/index\.html$/',
    ],
    'title_query' => [
        'type' => 'css',
        'query' => '#container h1.headline',
    ],
    'throttle_requests' => 200,
    'is_toc_page_query' => [
        'type' => 'xpath',
        'query' => '//*[@id="level2"]//*[@id="container"]//h2[@class="headline"]/a/abbr[text()="HTML"]',
    ],
    'toc_query' => [
        'type' => 'xpath',
        'query' => '//*[@id="level2"]//*[@id="container"]/*[@id="paddingLR12"]/table',
    ],
    'toc_item_determine_depth' => function ($nodeDto) {
        return match ($nodeDto['node']->parentNode->getAttribute('colspan')) {
            '3' => 1,
            '2' => 2,
            '', null => 3,
            default => 1,
        };
    },
]);
