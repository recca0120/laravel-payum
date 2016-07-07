<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Sync;
use Recca0120\LaravelPayum\Payment;

trait PaymentSync
{
    /**
     * sync.
     *
     * @method sync
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function sync(Payment $payment, Request $request, $payumToken)
    {
        return $payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
