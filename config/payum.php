<?php

return [
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
