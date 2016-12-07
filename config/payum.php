<?php

return [
    'path' => storage_path('app/payum'),
    'route' => [
        'prefix' => 'payment',
        'as' => 'payment.',
        'middleware' => ['web']
    ],
    'storage' => [
        // optioins: eloquent, filesystem
        'token' => 'filesystem',

        // optioins: eloquent, filesystem
        'gatewayConfig' => 'filesystem',
    ],

    'gatewayConfigs' => [
        // 'customFactoryName' => [
        //     'factory'  => 'FactoryClass',
        //     'username' => 'username',
        //     'password' => 'password',
        //     'sandbox'  => false
        // ],
    ],
];
