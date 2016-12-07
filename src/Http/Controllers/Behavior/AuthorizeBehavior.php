<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Recca0120\LaravelPayum\Service\PayumService;

trait AuthorizeBehavior
{
    /**
     * authorize.
     *
     * @method authorize
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function authorize(PayumService $payumService, $payumToken)
    {
        return $payumService->receiveAuthorize($payumToken);
    }
}
