<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Payout;
use Recca0120\LaravelPayum\Payment;

trait PayoutTrait
{
    /**
     * payout.
     *
     * @method payout
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function payout(Payment $payment, Request $request, $payumToken)
    {
        return $payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
