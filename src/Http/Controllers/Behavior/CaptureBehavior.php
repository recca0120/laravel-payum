<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payment;

trait CaptureBehavior
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
        return $payment->capture($request, $payumToken);
    }
}
