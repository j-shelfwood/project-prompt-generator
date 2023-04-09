<?php

return [
    'default' => 'file',

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => env('CACHE_FOLDER', storage_path('framework/cache/data')),
        ],
    ],
];
