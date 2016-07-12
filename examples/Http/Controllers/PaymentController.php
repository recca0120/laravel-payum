<?php

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
