<?php

use Illuminate\Support\Str;
use Laravel\Scout\Jobs\MakeSearchable;
use Laravel\Scout\Jobs\RemoveFromSearch;

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('HORIZON_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    */

    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_') . '-horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    */

    'waits' => [
        'redis:default' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    */

    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    |
    | Silencing a job will instruct Horizon to not place the job in the list
    | of completed jobs within the Horizon dashboard. This setting may be
    | used to fully remove any noisy jobs from the completed jobs list.
    |
    */

    'silenced' => [
        MakeSearchable::class,
        RemoveFromSearch::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Here you can configure how many snapshots should be kept to display in
    | the metrics graph. This will get used in combination with Horizon's
    | `horizon:snapshot` schedule to define how long to retain metrics.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    */

    'fast_termination' => true,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon master
    | supervisor may consume before it is terminated and restarted. For
    | configuring these limits on your workers, see the next section.
    |
    */

    'memory_limit' => 512,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    */

    'defaults' => [
        'supervisor-1' => [
            'connection' => 'redis',
            // 'queue' => ['default'],
            'balance' => 'auto',
            'maxProcesses' => 1,
            'memory' => 128,
            'tries' => 2,
            'nice' => 0,
            'timeout' => 120,
        ],
        'supervisor-2' => [
            'connection' => 'redis',
            'balance' => 'auto',
        ],
        'supervisor-3' => [
            'connection' => 'redis-long-running',
            'balance' => 'auto',
        ],
        'supervisor-4' => [
            'connection' => 'redis-long-running',
            'balance' => 'auto',
        ],
        'supervisor-5' => [
            'connection' => 'redis-long-running',
            'balance' => 'auto',
        ],
        'supervisor-6' => [
            'connection' => 'redis',
            'balance' => 'auto',
        ],
        'supervisor-7' => [
            'connection' => 'redis',
            'balance' => 'auto',
            'queue' => ['arachno-frontier-checks'],
            'maxProcesses' => 1,
            'tries' => 2,
            'timeout' => 60,
            'retry_after' => 65,
        ],
        'supervisor-8' => [
            'connection' => 'redis',
            'balance' => 'auto',
            'timeout' => 120,
            'retry_after' => 150,
        ],
        'supervisor-9' => [
            'connection' => 'redis',
            'balance' => 'auto',
            'memory' => 128,
            'timeout' => 120,
            'retry_after' => 125,
        ],
        'supervisor-10' => [
            'connection' => 'redis-long-running',
            'balance' => 'auto',
            'timeout' => 700,
            'retry_after' => 710,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'queue' => ['default', 'otp', 'notifications', 'norma-app'],
                'maxProcesses' => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'timeout' => 300,
                'retry_after' => 305,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['exports', 'caching'],
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 605,
            ],
            'supervisor-3' => [
                'connection' => 'redis-long-running',
                'queue' => ['long-running'],
                'maxProcesses' => 4,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800,
                'retry_after' => 10805,
            ],
            // arachno
            'supervisor-4' => [
                'connection' => 'redis-long-running',
                'queue' => ['arachno', 'arachno-checks', 'ocr'],
                'maxProcesses' => 4,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 1800,
                'retry_after' => 1805,
            ],
            'supervisor-5' => [
                'connection' => 'redis-long-running',
                'queue' => ['compilation'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800,
                'retry_after' => 10805,
            ],
            'supervisor-6' => [
                'connection' => 'redis',
                'queue' => ['enhance-processing'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 630,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['arachno-frontier-checks'],
                'maxProcesses' => 1,
            ],
            'supervisor-8' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'maxProcesses' => 1,
            ],
            'supervisor-9' => [
                'connection' => 'redis',
                'queue' => ['arachno-search-1', 'arachno-search-2', 'arachno-search-3'],
                'maxProcesses' => 3,
            ],
            'supervisor-10' => [
                'connection' => 'redis-long-running',
                'queue' => ['norma-ai', 'ai-classifier'],
                'maxProcesses' => 5,
            ],
        ],

        'staging' => [
            'supervisor-1' => [
                'queue' => ['default', 'otp', 'notifications', 'norma-app'],
                'maxProcesses' => 10,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 3,
                'timeout' => 300,
                'retry_after' => 305,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['exports', 'caching'],
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 605,
            ],
            'supervisor-3' => [
                'connection' => 'redis-long-running',
                'queue' => ['long-running'],
                'maxProcesses' => 4,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800,
                'retry_after' => 10805,
            ],
            // arachno
            'supervisor-4' => [
                'connection' => 'redis-long-running',
                'queue' => ['arachno', 'arachno-checks', 'ocr'],
                'maxProcesses' => 4,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 1800,
                'retry_after' => 1805,
            ],
            'supervisor-5' => [
                'connection' => 'redis-long-running',
                'queue' => ['compilation'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800,
                'retry_after' => 10805,
            ],
            'supervisor-6' => [
                'connection' => 'redis',
                'queue' => ['enhance-processing'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 630,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['arachno-frontier-checks'],
                'maxProcesses' => 1,
            ],
            'supervisor-8' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'maxProcesses' => 1,
            ],
            'supervisor-9' => [
                'connection' => 'redis',
                'queue' => ['arachno-search-1', 'arachno-search-2', 'arachno-search-3'],
                'maxProcesses' => 3,
            ],
            'supervisor-10' => [
                'connection' => 'redis-long-running',
                'queue' => ['norma-ai'],
                'maxProcesses' => 2,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'queue' => ['default', 'otp', 'notifications', 'norma-app'],
                'maxProcesses' => 3,
            ],
            'supervisor-2' => [
                'connection' => 'redis',
                'queue' => ['exports', 'caching'],
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 605,
            ],
            'supervisor-3' => [
                'connection' => 'redis-long-running',
                'queue' => ['long-running'],
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800, // some document imports take quite a while
                'retry_after' => 10805,
            ],
            'supervisor-4' => [
                'connection' => 'redis-long-running',
                'queue' => ['arachno', 'arachno-checks', 'ocr'],
                'maxProcesses' => 4,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 1,
                'memory' => 512,
                'timeout' => 1800,
                'retry_after' => 1805,
            ],
            'supervisor-5' => [
                'connection' => 'redis-long-running',
                'queue' => ['compilation'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 10800,
                'retry_after' => 10805,
            ],
            'supervisor-6' => [
                'connection' => 'redis',
                'queue' => ['enhance-processing'],
                'maxProcesses' => 1,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 3,
                'memory' => 512,
                'timeout' => 600,
                'retry_after' => 630,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['arachno-frontier-checks'],
                'maxProcesses' => 1,
            ],
            'supervisor-8' => [
                'connection' => 'redis',
                'queue' => ['scout'],
                'maxProcesses' => 1,
            ],
            'supervisor-9' => [
                'connection' => 'redis',
                'queue' => ['arachno-search-1', 'arachno-search-2', 'arachno-search-3'],
                'maxProcesses' => 3,
            ],
            'supervisor-10' => [
                'connection' => 'redis-long-running',
                'queue' => ['norma-ai'],
                'maxProcesses' => 2,
            ],
        ],
        'arachno-only-production' => [
            // arachno
            'supervisor-4' => [
                'connection' => 'redis-long-running',
                'queue' => ['arachno', 'arachno-checks'],
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 1800,
                'retry_after' => 1805,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['arachno-frontier-checks'],
                'maxProcesses' => 1,
            ],
        ],
        'arachno-only-local' => [
            // arachno
            'supervisor-4' => [
                'connection' => 'redis-long-running',
                'queue' => ['arachno', 'arachno-checks'],
                'maxProcesses' => 2,
                'balanceMaxShift' => 1,
                'balanceCooldown' => 2,
                'tries' => 2,
                'memory' => 512,
                'timeout' => 1800,
                'retry_after' => 1805,
            ],
            'supervisor-7' => [
                'connection' => 'redis',
                'queue' => ['arachno-frontier-checks'],
                'maxProcesses' => 1,
            ],
        ],
    ],
];
