<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait CancelBehavior
{
    /**
     * receiveCancel.
     *
     * @method receiveCancel
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function receiveCancel(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveCancel($payumToken);
    }
}
