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
use Recca0120\LaravelPayum\Http\Controllers\Behavior\DoneBehavior;
use Recca0120\LaravelPayum\Http\Controllers\Behavior\PrepareBehavior;
use Recca0120\LaravelPayum\Service\Payment;

class PaymentController extends BaseController
{
    use PrepareBehavior,
        DoneBehavior;

    protected $gatewayName = 'esunbank';

    /**
     * Prepare you payment.
     *
     * @method onPrepare
     *
     * @param \Illuminate\Http\Request             $request
     * @param \Payum\Core\Model\PaymentInterface   $payment
     * @param string                               $gatewayName
     * @param \Payum\Core\Storage\StorageInterface $storage
     * @param \Payum\Core\Payum
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    protected function onPrepare(Request $request, PaymentInterface $payment, $gatewayName, StorageInterface $storage, Payum $payum)
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

    /**
     * onDone.
     *
     * @method onDone
     *
     * @param \Payum\Core\Request\GetHumanStatus  $status
     * @param \Payum\Core\Model\PaymentInterface  $payment
     * @param \Payum\Core\GatewayInterface        $payment
     * @param \Payum\Core\Security\TokenInterface $token
     *
     * @return mixed
     */
    protected function onDone(GetHumanStatus $status, PaymentInterface $payment, GatewayInterface $gateway, TokenInterface $token)
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
