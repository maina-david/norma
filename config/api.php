<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    'filter_operators' => [
        'eq' => '=',
        'neq' => '!=',
        'lt' => '<',
        'gt' => '>',
        'lte' => '<=',
        'gte' => '>=',
        'like' => 'LIKE',
        'in' => 'In',
        'between' => 'between',
    ],

    'corpus_webhook_secret' => env('CORPUS_WEBHOOK_SECRET'),
];
