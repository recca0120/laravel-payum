<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait RefundBehavior
{
    /**
     * receiveRefund.
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string $payumToken
     * @return mixed
     */
    public function receiveRefund(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveRefund($payumToken);
    }
}
