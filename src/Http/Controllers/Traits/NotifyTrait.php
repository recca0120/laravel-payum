<?php

namespace Recca0120\LaravelPayum\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Payum\Core\Request\Notify;
use Recca0120\LaravelPayum\Payment;

trait NotifyTrait
{
    /**
     * notify.
     *
     * @method notify
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function notify(Payment $payment, Request $request, $payumToken)
    {
        return $payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Notify($token));

            return response(null, 204);
        });
    }

    /**
     * notifyUnsafe.
     *
     * @method notifyUnsafe
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     * @param string $gatewayName
     *
     * @return mixed
     */
    public function notifyUnsafe(Payment $payment, $gatewayName)
    {
        $gateway = $payment->getPayum()->getGateway($gatewayName);
        $gateway->execute(new Notify(null));

        return response(null, 204);
    }
}
