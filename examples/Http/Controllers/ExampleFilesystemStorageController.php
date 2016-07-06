<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Payum\Core\Model\Payment;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExampleFilesystemStorageController extends Controller
{
    public function index()
    {
        $gatewayName = 'offline';
        $storage = $this->payum->getStorage(Payment::class);
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('TWD');
        $payment->setTotalAmount(100);
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        $payment->setDetails([]);
        $storage->update($payment);
        $captureToken = $this->payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'payment.done');

        return redirect($captureToken->getTargetUrl());
    }

    public function done(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute($status = new GetHumanStatus($token));
            $payment = $status->getFirstModel();

            return response()->json([
                'status'  => $status->getValue(),
                'order'   => [
                    'number'        => $payment->getNumber(),
                    'total_amount'  => $payment->getTotalAmount(),
                    'currency_code' => $payment->getCurrencyCode(),
                    'details'       => $payment->getDetails(),
                ],
            ]);
        }, $request, $payumToken);
    }
}
