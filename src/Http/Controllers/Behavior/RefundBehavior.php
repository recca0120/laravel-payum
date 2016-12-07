<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait RefundBehavior
{
    /**
     * refund.
     *
     * @method refund
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function refund(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveRefund($payumToken);
    }
}
