<?php

return [
    'router' => [
        'prefix'     => 'payment',
        'as'         => 'payment.',
        'middleware' => [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        ],
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
    ],
];
