<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait SyncBehavior
{
    /**
     * sync.
     *
     * @method sync
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function sync(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveSync($payumToken);
    }
}
