<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payment;

trait NotifyBehavior
{
    /**
     * notify.
     *
     * @method notify
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function notify(Payment $payment, Request $request, $payumToken)
    {
        return $payment->notify($request, $payumToken);
    }

    /**
     * notifyUnsafe.
     *
     * @method notifyUnsafe
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param string                          $gatewayName
     *
     * @return mixed
     */
    public function notifyUnsafe(Payment $payment, $gatewayName)
    {
        return $payment->notifyUnsafe($gatewayName);
    }
}
