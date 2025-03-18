<?php

return [
    'compilation' => [
        /*
        |--------------------------------------------------------------------------
        | Cache time
        |--------------------------------------------------------------------------
        |
        | How many seconds to keep compiled norma cache's - 25 minutes
        */

        'cache_key_prefix' => 'norma_compilation',
        'cache_key_prefix_ids' => 'norma_compilation_ids',
        'cache_lifetime' => 60 * 25,
        'tag' => 'norma_compilation',
        'legal_report_cache_ids' => 'norma_legal_report',
        'legal_report_lifetime' => 60 * 25,
    ],
    'auth' => [
        'last_login_prefix' => 'last_login_',
        'user' => [
            'active-user-metrics' => [
                'prefix' => 'ACTIVE_USER_METRICS',
                'expiry' => 86400, // expires in 1 day
            ],
            'user-2fa-backup' => [
                'prefix' => 'TWO_FACTOR_BACKUP_',
            ],
            'user-activity-breakdown' => [
                'prefix' => 'USER_ACTIVITY_BREAKDOWN',
                'expiry' => 86400, // expires in 1 day
            ],
            'user-invite-sent' => [
                'prefix' => 'USER_INVITE_SENT_',
                'expiry' => 86400, // expires in 1 day
            ],
            'user-multisite-access' => [
                'prefix' => 'USER_MULTISITE_',
                'expiry' => 10080, // expires in 1 week
            ],
            'user-multiorg-access' => [
                'prefix' => 'USER_MULTIORG_',
                'expiry' => 10080, // expires in 1 week
            ],
            'user-pending-deactivation' => [
                'prefix' => 'USER_PENDING_DEACTIVATION_',
                'expiry' => 2764800, // expires in 32 days
            ],
            'user-places' => [
                'prefix' => 'USER_PLACE_IDS_',
                'expiry' => 300, // expires in 5 minutes
            ],
        ],
    ],
    'ontology' => [
        'category' => [
            'type-control' => [
                'key' => 'CONTROL_CATEGORIES',
                'expiry' => 1440, // expires in 24 hrs
            ],
            'type-economic' => [
                'key' => 'ECONOMIC_CATEGORIES',
                'expiry' => 1440, // expires in 24 hrs
            ],
        ],
    ],
    'corpus' => [
        'generate_citations' => [
            'prefix' => 'GENERATE_CITATIONS_',
            'expiry' => 240, // expires in 4 hrs
        ],
    ],
];
