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

    // 'customFactoryName' => [
    //     'factory'  => 'FactoryClass',
    //     'username' => 'username',
    //     'password' => 'password',
    //     'sandbox'  => false
    // ],
    'gatewayConfigs' => [
        'allpay' => [
            'factory' => PayumTW\Allpay\AllpayGatewayFactory::class,
            'MerchantID' => '2000132',
            'HashKey' => '5294y06JbISpM5x9',
            'HashIV' => 'v77hoKGq4kWxNNIS',
            'sandbox' => true,
        ],
    ],
];
