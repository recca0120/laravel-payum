<?php

return [
    'default' => 'offline',
    'path' => storage_path('app/payum'),
    'route' => [
        'prefix' => 'payum',
        'as' => 'payum.',
        'middleware' => ['web'],
    ],
    'storage' => [
        'token' => 'files',
        'gateway_config' => 'files',
    ],
    'gateway_configs' => [],
];
