<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait CaptureBehavior
{
    /**
     * capture.
     *
     * @method capture
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function capture(PayumService $payumService, $payumToken = null)
    {
        return $payumService->receiveCapture($payumToken);
    }
}
