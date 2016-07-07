<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Authorize;
use Recca0120\LaravelPayum\Payment;

trait AuthorizePayment
{
    /**
     * authorize.
     *
     * @method authorize
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function authorize(Payment $payment, Request $request, $payumToken)
    {
        return $payment->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
