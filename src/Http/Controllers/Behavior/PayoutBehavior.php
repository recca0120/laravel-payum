<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait PayoutBehavior
{
    /**
     * payout.
     *
     * @method payout
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function payout(PayumService $payumService, $payumToken)
    {
        return $payumService->receivePayout($payumToken);
    }
}
