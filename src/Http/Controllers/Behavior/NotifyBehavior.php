<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\Payum as PayumService;

trait NotifyBehavior
{
    /**
     * notify.
     *
     * @method notify
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param \Illuminate\Http\Request              $request
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function notify(PayumService $payumService, Request $request, $payumToken)
    {
        return $payumService->notify($request, $payumToken);
    }

    /**
     * notifyUnsafe.
     *
     * @method notifyUnsafe
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $gatewayName
     *
     * @return mixed
     */
    public function notifyUnsafe(PayumService $payumService, $gatewayName)
    {
        return $payumService->notifyUnsafe($gatewayName);
    }
}
