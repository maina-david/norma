<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Elasticsearch hosts
    |--------------------------------------------------------------------------
    */

    'enabled' => env('ELASTICSEARCH_ENABLED', true),
    'hosts' => [
        [
            'host' => env('ELASTICSEARCH_HOST', '127.0.0.1'),
            'port' => env('ELASTICSEARCH_PORT', 9200),
            'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
        ],
    ],
    // 'synonyms_path' => env('ELASTICSEARCH_SYNONYMS_PATH', 'analysis/synonyms.txt'),

    'aws' => [
        'region' => env('AWS_REGION'),
    ],
];
