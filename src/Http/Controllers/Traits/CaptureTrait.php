<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Capture;
use Recca0120\LaravelPayum\Payment;

trait CaptureTrait
{
    /**
     * capture.
     *
     * @method capture
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function capture(Payment $payment, Request $request, $payumToken = null)
    {
        return $payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
