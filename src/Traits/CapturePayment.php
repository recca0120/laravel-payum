<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Capture;
use Recca0120\LaravelPayum\Payment;

trait CapturePayment
{
    /**
     * capture.
     *
     * @method capture
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function capture(Payment $payment, Request $request, $payumToken = null)
    {
        return $payment->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
