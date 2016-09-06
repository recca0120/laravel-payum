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
use Recca0120\LaravelPayum\Service\Payum as PayumService;

class PaymentController extends BaseController
{
    public function prepare(PayumService $payumService)
    {
        return $payumService->prepare('allpay', function (
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

    public function done(PayumService $payumService, Request $request, $payumToken)
    {
        return $payumService->done($request, $payumToken, function (
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
