<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait SyncBehavior
{
    /**
     * receiveSync.
     *
     * @method receiveSync
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function receiveSync(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveSync($payumToken);
    }
}
