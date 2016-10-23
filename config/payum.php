<?php

return [
    'path' => stroage_path('app/payum'),
    'route' => [
        'prefix' => 'payment',
        'as' => 'payment.',
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
