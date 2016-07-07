<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Refund;
use Recca0120\LaravelPayum\Payment;

trait RefundPayment
{
    /**
     * refund.
     *
     * @method refund
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function refund(Payment $payment, Request $request, $payumToken)
    {
        return $payment->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);
            if (empty($token->getAfterUrl()) === false) {
                return redirect($token->getAfterUrl());
            }

            return response(null, 204);
        });
    }
}
