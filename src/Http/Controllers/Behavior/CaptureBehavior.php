<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payum as PayumService;

trait CaptureBehavior
{
    /**
     * capture.
     *
     * @method capture
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param \Illuminate\Http\Request              $request
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function capture(PayumService $payumService, Request $request, $payumToken = null)
    {
        return $payumService->capture($request, $payumToken);
    }
}
