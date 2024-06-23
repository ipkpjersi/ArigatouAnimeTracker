<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'channels' => [
        'anime_import' => [
            'driver' => 'daily',
            'path' => storage_path('logs/anime_import.log'),
            'level' => 'info',
            'days' => 365,
        ],
    ],

];
