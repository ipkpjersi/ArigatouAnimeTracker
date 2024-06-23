<?php

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
