<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Recca0120\LaravelPayum\Payment;

class PaymentController extends BaseController
{
    /**
     * $payment.
     *
     * @var \Recca0120\LaravelPayum\Payment
     */
    protected $payment;

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Recca0120\LaravelPayum\Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * authorize.
     *
     * @method authorize
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function authorize(Request $request, $payumToken)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }

    /**
     * capture.
     *
     * @method capture
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function capture(Request $request, $payumToken = null)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }

    /**
     * notify.
     *
     * @method notify
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function notify(Request $request, $payumToken)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Notify($token));

            return response(null, 204);
        });
    }

    /**
     * notifyUnsafe.
     *
     * @method notifyUnsafe
     *
     * @param string $gatewayName
     *
     * @return mixed
     */
    public function notifyUnsafe($gatewayName)
    {
        $gateway = $this->payment->getPayum()->getGateway($gatewayName);
        $gateway->execute(new Notify(null));

        return response(null, 204);
    }

    /**
     * payout.
     *
     * @method payout
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function payout(Request $request, $payumToken)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }

    /**
     * refund.
     *
     * @method refund
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function refund(Request $request, $payumToken)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);
            if ($token->getAfterUrl()) {
                return redirect($token->getAfterUrl());
            }

            return response(null, 204);
        });
    }

    /**
     * sync.
     *
     * @method sync
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function sync(Request $request, $payumToken)
    {
        return $this->payment->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
