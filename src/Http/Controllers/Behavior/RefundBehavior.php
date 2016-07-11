<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payment;

trait RefundBehavior
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
        return $payment->refund($request, $payumToken);
    }
}
