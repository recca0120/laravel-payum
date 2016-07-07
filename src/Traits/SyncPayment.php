<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Sync;
use Recca0120\LaravelPayum\Payment;

trait SyncPayment
{
    /**
     * sync.
     *
     * @method sync
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function sync(Payment $payment, Request $request, $payumToken)
    {
        return $payment->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
