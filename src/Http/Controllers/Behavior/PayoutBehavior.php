<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payum as PayumService;

trait PayoutBehavior
{
    /**
     * payout.
     *
     * @method payout
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param \Illuminate\Http\Request              $request
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function payout(PayumService $payumService, Request $request, $payumToken)
    {
        return $payumService->payout($request, $payumToken);
    }
}
