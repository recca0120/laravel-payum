## [Payum](https://github.com/Payum/Payum) for Laravel 5

[![StyleCI](https://styleci.io/repos/62729797/shield?style=flat)](https://styleci.io/repos/62729797)
[![Build Status](https://travis-ci.org/recca0120/laravel-payum.svg)](https://travis-ci.org/recca0120/laravel-payum)
[![Total Downloads](https://poser.pugx.org/recca0120/laravel-payum/d/total.svg)](https://packagist.org/packages/recca0120/laravel-payum)
[![Latest Stable Version](https://poser.pugx.org/recca0120/laravel-payum/v/stable.svg)](https://packagist.org/packages/recca0120/laravel-payum)
[![Latest Unstable Version](https://poser.pugx.org/recca0120/laravel-payum/v/unstable.svg)](https://packagist.org/packages/recca0120/laravel-payum)
[![License](https://poser.pugx.org/recca0120/laravel-payum/license.svg)](https://packagist.org/packages/recca0120/laravel-payum)
[![Monthly Downloads](https://poser.pugx.org/recca0120/laravel-payum/d/monthly)](https://packagist.org/packages/recca0120/laravel-payum)
[![Daily Downloads](https://poser.pugx.org/recca0120/laravel-payum/d/daily)](https://packagist.org/packages/recca0120/laravel-payum)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/recca0120/laravel-payum/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/recca0120/laravel-payum/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/recca0120/laravel-payum/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/recca0120/laravel-payum/?branch=master)

## Installing

To get the latest version of Laravel Exceptions, simply require the project using [Composer](https://getcomposer.org):

```bash
composer require recca0120/laravel-payum
```

Instead, you may of course manually update your require block and run `composer update` if you so choose:

```json
{
    "require": {
        "recca0120/laravel-payum": "^1.0.6"
    }
}
```

Include the service provider within `config/app.php`. The service povider is needed for the generator artisan command.

```php
'providers' => [
    ...
    Recca0120\LaravelPayum\LaravelPayumServiceProvider::class,
    ...
];
```

## Config

```php
return [
    'route' => [
        'prefix' => 'payment',
        'as' => 'payment.',
        'middleware' => ['web'],
    ],

    'storage' => [
        // options: eloquent, filesystem
        'token' => 'filesystem',

        // options: eloquent, filesystem
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
```

## VerifyCsrfToken

```php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'payment/*'
    ];
}

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
use Recca0120\LaravelPayum\Service\PayumService;

class PaymentController extends BaseController
{
    public function capture(PayumService $payumService)
    {
        return $payumService->capture('allpay', function (
            PaymentInterface $payment,
            $gatewayName,
            StorageInterface $storage,
            Payum $payum
        ) {
            $payment->setNumber(uniqid());
            $payment->setCurrencyCode('TWD');
            $payment->setTotalAmount(2000);
            $payment->setDescription('A description');
            $payment->setClientId('anId');
            $payment->setClientEmail('foo@example.com');
            $payment->setDetails([
                'Items' => [
                    [
                        'Name' => '歐付寶黑芝麻豆漿',
                        'Price' => (int) '2000',
                        'Currency' => '元',
                        'Quantity' => (int) '1',
                        'URL' => 'dedwed',
                    ],
                ],
            ]);
        });
    }

    public function done(PayumService $payumService, $payumToken)
    {
        return $payumService->done($payumToken, function (
            GetHumanStatus $status,
            PaymentInterface $payment,
            GatewayInterface $gateway,
            TokenInterface $token
        ) {
            return response()->json([
                'status' => $status->getValue(),
                'client' => [
                    'id' => $payment->getClientId(),
                    'email' => $payment->getClientEmail(),
                ],
                'number' => $payment->getNumber(),
                'description' => $payment->getCurrencyCode(),
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
            ]);
        });
    }
}
```

## Router

```php
Route::get('payment', [
    'as'   => 'payment',
    'uses' => 'PaymentController@capture',
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
artisan vendor:publish --provider="Recca0120\LaravelPayum\LaravelPayumServiceProvider"
```

migrate

```bash
artisan migrate
```

modify config

```php

return [
    'route' => [
        'prefix' => 'payment',
        'as' => 'payment.',
        'middleware' => ['web'],
    ],

    'storage' => [
        // options: eloquent, eloquent
        'token' => 'filesystem',

        // options: eloquent, filesystem
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
