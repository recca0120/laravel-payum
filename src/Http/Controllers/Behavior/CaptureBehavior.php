<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait CaptureBehavior
{
    /**
     * receiveCapture.
     *
     * @method receiveCapture
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function receiveCapture(PayumService $payumService, $payumToken = null)
    {
        return $payumService->receiveCapture($payumToken);
    }
}
