<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Recca0120\LaravelPayum\Traits\DonePayment;
use Recca0120\LaravelPayum\Traits\PreparePayment;

class PrepareDoneController extends BaseController
{
    use PreparePayment, DonePayment;

    /**
     * Set payment gateway name.
     *
     * @var string
     */
    protected $gatewayName = 'offline';

    /**
     * Prepare you payment.
     *
     * @method onPrepare
     *
     * @param \Payum\Core\Model\PaymentInterface   $payment
     * @param \Payum\Core\Storage\StorageInterface $storage
     * @param \Payum\Core\Payum
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    protected function onPrepare($payment)
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
    protected function onDone($status, $payment, $gateway, $token)
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
