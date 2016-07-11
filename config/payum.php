<?php

return [
    'router' => [
        'prefix'     => 'payment',
        'as'         => 'payment',
        // don't remove web
        'middleware' => 'web',
    ],

    'storage' => [
        // optioins: database, filesystem
        'token' => 'eloquent',

        // optioins: database, filesystem
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
