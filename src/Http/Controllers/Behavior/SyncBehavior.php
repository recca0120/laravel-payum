<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payment;

trait SyncBehavior
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
        return $payment->sync($request, $payumToken);
    }
}
