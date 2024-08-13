<?php

return [
    'check_crawl_complete_attempts' => 15,
    'check_crawl_complete_delay_minutes' => env('ARACHNO_CHECK_CRAWL_COMPLETE_DELAY', 3),
    'check_frontier_chunks' => env('ARACHNO_CHECK_FRONTIER_CHUNKS', 50),
    'check_frontier_search_chunks' => env('ARACHNO_CHECK_FRONTIER_SEARCH_CHUNKS', 500),
    'crawl_url_job_lock' => 'archno_crawl_url_job_lock_',
    'default_user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/93.0.4577.63 Safari/537.36',
    'guzzle_verify_ssl' => env('GUZZLE_VERIFY_SSL', true),
    'horizon_arachno_only' => env('HORIZON_ARACHNO_ONLY', false),
    'search_queues' => 3, // how many different horizon queues there are for search - see Horizon config supervisor-9
    'services' => [
        'sabinet' => [
            'access_token' => env('SERVICES_SABINET_API_TOKEN'),
            'token_cache_lifetime' => 3500, // the token expiry is one hour, ie 3600
        ],
    ],
    'throttle_cache_key' => 'arachno_throttle_last_request_',
];
