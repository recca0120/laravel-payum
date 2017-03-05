<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait NotifyBehavior
{
    /**
     * receiveNotify.
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string $payumToken
     * @return mixed
     */
    public function receiveNotify(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveNotify($payumToken);
    }

    /**
     * receiveNotifyUnsafe.
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string $gatewayName
     * @return mixed
     */
    public function receiveNotifyUnsafe(PayumService $payumService, $gatewayName)
    {
        return $payumService->receiveNotifyUnsafe($gatewayName);
    }
}
