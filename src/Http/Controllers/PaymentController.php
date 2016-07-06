<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Http\Request;
use Payum\Core\Payum;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;

class PaymentController extends Controller
{
    public function authorize(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        }, $request, $payumToken);
    }

    public function capture(Request $request, $payumToken = null)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        }, $request, $payumToken);
    }

    public function notify(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Notify($token));

            return response(null, 204);
        }, $request, $payumToken);
    }

    public function notifyUnsafe($gatewayName)
    {
        $gateway = $this->payum->getGateway($gatewayName);
        $gateway->execute(new Notify(null));

        return response(null, 204);
    }

    public function payout(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        }, $request, $payumToken);
    }

    public function refund(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);
            if ($token->getAfterUrl()) {
                return redirect($token->getAfterUrl());
            }

            return response(null, 204);
        }, $request, $payumToken);
    }

    public function sync(Request $request, $payumToken)
    {
        return $this->doAction(function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        }, $request, $payumToken);
    }
}
