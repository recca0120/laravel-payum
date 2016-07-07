<?php

namespace Recca0120\LaravelPayum\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Payout;
use Recca0120\LaravelPayum\Payment;

trait PaymentDone
{
    /**
     * payout.
     *
     * @method payout
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request        $request
     * @param string                          $payumToken
     *
     * @return mixed
     */
    public function done(Payment $payment, Request $request, $payumToken)
    {
        return $payment->done($request, $payumToken, function ($payment, $status) {
            return $this->showPayment($payment, $status);
        });
    }

    /**
     * showPayment.
     *
     * @method showPayment
     *
     * @param  \Payum\Core\Model\PaymentInterface $payment
     * @param  mixed      $status
     *
     * @return mixed
     */
    abstract protected function showPayment($payment, $status);
}
