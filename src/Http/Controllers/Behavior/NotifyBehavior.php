<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait NotifyBehavior
{
    /**
     * notify.
     *
     * @method notify
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function notify(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveNotify($payumToken);
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
        return $payumService->receiveNotifyUnsafe($gatewayName);
    }
}
