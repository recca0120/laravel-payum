<?php

return [
    'router' => [
        'prefix'     => 'payment',
        'as'         => 'payment.',
        // if laravel 5.1 remove web
        'middleware' => 'web',
    ],

    'storage' => [
        // optioins: eloquent, filesystem
        'token' => 'filesystem',

        // optioins: eloquent, filesystem
        'gatewayConfig' => 'filesystem',
    ],

    // [
    //     'customFactoryName' => \GateFactoryClass::class,
    //     'customFactoryName2' => \GateFactoryClass2::class,
    // ]
    'gatewayFactories' => [
    ],

    // 'customFactoryName' => [
    //     'gatewayName' => 'customGatewayName',
    //     'config'      => [
    //         'sandbox' => false
    //     ],
    // ],
    'gatewayConfigs' => [
    ],
];
