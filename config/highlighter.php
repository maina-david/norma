<?php

return [
    /*
     |--------------------------------------------------------------------------
     | The Highlighting api
     |--------------------------------------------------------------------------
     */

    'enabled' => env('HIGHLIGHTER_ENABLED', true),
    'end_point' => env('HIGHLIGHTER_API_ENDPOINT', 'https://highlighting.norma.rocks'),
    'guzzle_verify_ssl' => env('GUZZLE_VERIFY_SSL', true),
];
