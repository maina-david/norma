<?php

use App\Enums\Storage\FileType;

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', env('FILESYSTEM_DRIVER', 'local')), // changed in Laravel 9

    'default-images' => env('FILESYSTEM_IMAGES_DRIVER', 'local-images'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'local-documents' => [
            'driver' => 'local',
            'root' => storage_path('app/documents'),
        ],

        'local-images' => [
            'driver' => 'local',
            'root' => public_path() . '/document-images',
            'url' => env('APP_URL') . '/document-images',
            'visibility' => 'public',
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        's3-images' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_IMAGES_BUCKET'),
            'url' => env('AWS_IMAGES_URL'),
        ],

        's3-tmp' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_TMP_BUCKET', 'norma-tmp'),
            'url' => env('AWS_URL'),
        ],

        's3-documents' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_DOCUMENTS_BUCKET', 'norma-documents'),
            'url' => env('AWS_URL'),
        ],

        'casemaker-ftp' => [
            'driver' => 'ftp',
            'host' => env('CASEMAKER_FTP_HOST'),
            'username' => env('CASEMAKER_FTP_USERNAME'),
            'password' => env('CASEMAKER_FTP_PASSWORD'),
            'passive' => true,
            'ssl' => true,
            'timeout' => 30,
            'root' => env('CASEMAKER_FTP_ROOT', '/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    'filetypes' => [
        FileType::collaborators()->value => [
            'path' => 'attachments/collaborators',
        ],
        FileType::tasks()->value => [
            'path' => 'attachments/tasks',
        ],
        FileType::my()->value => [
            'path' => 'files',
        ],
        FileType::myAttachment()->value => [
            'path' => 'attachments',
        ],
        FileType::content()->value => [
            'disk' => env('FILESYSTEM_CONTENT_DISK', 'local'),
        ],
    ],

    'images' => [
        'path' => 'images',
    ],

    'paths' => [
        'works' => 'documents',
        'temp' => 'tmp',
    ],

    'supported' => [
        'uploads' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.ms-excel.sheet.macroenabled',
            'application/vnd.ms-excel.sheet.macroenabled.12',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.visio',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/octet-stream',
            'application/x-rar-compressed',
            'text/plain',
        ],
        'images' => [
            'image/jpeg',
            'image/png',
        ],
    ],
];
