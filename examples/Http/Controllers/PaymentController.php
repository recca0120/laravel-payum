<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\DoneBehavior;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\PrepareBehavior;

class PaymentController extends BaseController
{
    use PrepareBehavior,
        DoneBehavior;

    protected $gatewayName = 'esunbank';

    public function onPrepare($payment, $gatewayName, $storage, $payum)
    {
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('TWD');
        $payment->setTotalAmount(100);
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        $payment->setDetails([]);

        return $payment;
    }

    public function onDone($status, $payment, $gateway, $token)
    {
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
    }
}
