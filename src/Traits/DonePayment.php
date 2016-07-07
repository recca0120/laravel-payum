<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Payment;

trait DonePayment
{
    /**
     * done.
     *
     * @method done
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function done(Payment $payment, Request $request, $payumToken)
    {
        return $payment->done($request, $payumToken, function ($status, $payment, $gateway, $token) {
            return $this->onDone($status, $payment, $gateway, $token);
        });
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
    abstract protected function onDone($status, $payment, $gateway, $token);
}
