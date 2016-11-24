<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Behavior;

use Illuminate\Http\Request;
use Recca0120\LaravelPayum\Service\PayumService;

trait AuthorizeBehavior
{
    /**
     * authorize.
     *
     * @method authorize
     *
     * @param \Recca0120\LaravelPayum\Service\Payum $payumService
     * @param \Illuminate\Http\Request              $request
     * @param string                                $payumToken
     *
     * @return mixed
     */
    public function authorize(PayumService $payumService, Request $request, $payumToken)
    {
        return $payumService->receiveAuthorize($request, $payumToken);
    }
}
