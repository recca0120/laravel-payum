<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Traits\PaymentAuthorize;
use Recca0120\LaravelPayum\Traits\PaymentCapture;
use Recca0120\LaravelPayum\Traits\PaymentDone;
use Recca0120\LaravelPayum\Traits\PaymentNotify;
use Recca0120\LaravelPayum\Traits\PaymentPayout;
use Recca0120\LaravelPayum\Traits\PaymentPrepare;
use Recca0120\LaravelPayum\Traits\PaymentRefund;
use Recca0120\LaravelPayum\Traits\PaymentSync;

class FullPaymentController extends BaseController
{
    use PaymentAuthorize,
        PaymentCapture,
        PaymentNotify,
        PaymentPayout,
        PaymentRefund,
        PaymentSync,
        PaymentPrepare,
        PaymentDone;

    // gateway name
    protected $gatewayName = 'esunbank';

    // prepare payment
    protected function preparePayment($payment)
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

    // show payment
    protected function showPayment($payment, $status)
    {
        return response()->json([
            'status'  => $status->getValue(),
            'order'   => [
                'number'        => $payment->getNumber(),
                'total_amount'  => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details'       => $payment->getDetails(),
            ],
        ]);
    }
}
