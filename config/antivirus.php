<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    | This skips the virus validation for current environment.
    |
    | Please note when true it won't connect to ClamAV and will skip the virus validation.
    */
    'enabled' => env('ANTIVIRUS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Preferred socket
    |--------------------------------------------------------------------------
    |
    | This option controls the socket which is used, which.
    |
    | Please note if the unix_socket is used and the socket-file is not found the tcp socket will be
    | used as fallback.
    |
    | options: "unix_socket", "tcp_socket"
    */
    'preferred_socket' => env('CLAMAV_PREFERRED_SOCKET', 'tcp_socket'),

    /*
    |--------------------------------------------------------------------------
    | Unix Socket
    |--------------------------------------------------------------------------
    | This option defines the location to the unix socket-file. For example
    | /var/run/clamav/clamd.ctl
    */
    'unix_socket' => env('CLAMAV_UNIX_SOCKET', '/var/run/clamav/clamd.ctl'),

    /*
    |--------------------------------------------------------------------------
    | TCP Socket
    |--------------------------------------------------------------------------
    | This option defines the TCP socket to the ClamAV instance.
    */
    'tcp_socket' => env('CLAMAV_TCP_SOCKET', 'tcp://127.0.0.1:3310'),

    /*
    |--------------------------------------------------------------------------
    | Socket connect timeout
    |--------------------------------------------------------------------------
    | This option defines the maximum time to wait in seconds for socket connection
    | attempts before failure or timeout, default null = no limit.
    */
    'socket_connect_timeout' => env('CLAMAV_SOCKET_CONNECT_TIMEOUT', null),

    /*
    |--------------------------------------------------------------------------
    | Socket read timeout
    |--------------------------------------------------------------------------
    | This option defines the maximum time to wait in seconds for a read.
    */
    'socket_read_timeout' => env('CLAMAV_SOCKET_READ_TIMEOUT', 30),
];
