<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait PayoutBehavior
{
    /**
     * receivePayout.
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string $payumToken
     * @return mixed
     */
    public function receivePayout(PayumService $payumService, $payumToken)
    {
        return $payumService->receivePayout($payumToken);
    }
}
