## [Payum](https://github.com/Payum/Payum) for Laravel 5

[![Latest Stable Version](https://poser.pugx.org/recca0120/laravel-payum/v/stable)](https://packagist.org/packages/recca0120/laravel-payum)
[![Total Downloads](https://poser.pugx.org/recca0120/laravel-payum/downloads)](https://packagist.org/packages/recca0120/laravel-payum)
[![Latest Unstable Version](https://poser.pugx.org/recca0120/laravel-payum/v/unstable)](https://packagist.org/packages/recca0120/laravel-payum)
[![License](https://poser.pugx.org/recca0120/laravel-payum/license)](https://packagist.org/packages/recca0120/laravel-payum)
[![Monthly Downloads](https://poser.pugx.org/recca0120/laravel-payum/d/monthly)](https://packagist.org/packages/recca0120/laravel-payum)
[![Daily Downloads](https://poser.pugx.org/recca0120/laravel-payum/d/daily)](https://packagist.org/packages/recca0120/laravel-payum)

## Installing

To get the latest version of Laravel Exceptions, simply require the project using [Composer](https://getcomposer.org):

```bash
composer require recca0120/laravel-payum
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "recca0120/laravel-payum": "^0.0.1"
    }
}
```

Include the service provider within `config/app.php`. The service povider is needed for the generator artisan command.

```php
'providers' => [
    ...
    Recca0120\LaravelPayum\ServiceProvider::class,
    ...
];
```

## Config

```php
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
        'offline' => []
    ],
];
```

## Controller

```php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Recca0120\LaravelPayum\Service\Payment;

class PaymentController extends BaseController
{
    public function prepare(Payment $payment)
    {
        return $payment->prepare('offline', function (PaymentInterface $payment, $gatewayName, StorageInterface $storage, Payum $payum) {
            $payment->setNumber(uniqid());
            $payment->setCurrencyCode('TWD');
            $payment->setTotalAmount(100);
            $payment->setDescription('A description');
            $payment->setClientId('anId');
            $payment->setClientEmail('foo@example.com');
            $payment->setDetails([]);
        });
    }

    public function done(Payment $payment, Request $request, $payumToken)
    {
        return $payment->done($request, $payumToken, function (GetHumanStatus $status, PaymentInterface $payment, GatewayInterface $gateway, TokenInterface $token) {
            return response()->json([
                'status' => $status->getValue(),
                'client' => [
                    'id'    => $payment->getClientId(),
                    'email' => $payment->getClientEmail(),
                ],
                'number'        => $payment->getNumber(),
                'description'   => $payment->getCurrencyCode(),
                'total_amount'  => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details'       => $payment->getDetails(),
            ]);
        });
    }
}
```

## Router

```php
Route::get('payment', [
    'as'   => 'payment',
    'uses' => 'PaymentController@prepare',
]);

Route::any('payment/done/{payumToken}', [
    'as'   => 'payment.done',
    'uses' => 'PaymentController@done',
]);
```

## Eloquent

If you want use eloquent you need change config.php and create database


### Migrate

publish vendor

```bash
artisan vendor:publish --provider="Recca0120\LaravelPayum\ServiceProvider"
```

migrate

```bash
artisan migrate
```

modify config

```php

return [
    'router' => [
        'prefix'     => 'payment',
        'as'         => 'payment.',
        // if laravel 5.1 remove web
        'middleware' => 'web',
    ],

    'storage' => [
        // optioins: eloquent, eloquent
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
        'offline' => []
    ],
];
```
