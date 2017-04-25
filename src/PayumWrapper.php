<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Payum;
use Illuminate\Http\Request;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;

class PayumWrapper
{
    public $gatewayName;

    protected $payum;

    public function __construct(Payum $payum, $gatewayName)
    {
        $this->payum = $payum;
        $this->gatewayName = $gatewayName;
    }

    public function getPayum()
    {
        return $this->payum;
    }

    public function capture(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('capture', $callback, $afterPath, $afterParameters);
    }

    public function authorize(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('authorize', $callback, $afterPath, $afterParameters);
    }

    public function refund(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('refund', $callback, $afterPath, $afterParameters);
    }

    public function cancel(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('cancel', $callback, $afterPath, $afterParameters);
    }

    public function payout(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('payout', $callback, $afterPath, $afterParameters);
    }

    public function done(Request $request, $payumToken, callable $callback)
    {
        $request->merge([
            'payum_token' => $payumToken,
        ]);
        $token = $this->getPayum()->getHttpRequestVerifier()->verify($request);
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());
        $gateway->execute($status = new GetHumanStatus($token));

        return $callback($status, $status->getFirstModel(), $token, $gateway);
    }

    protected function send($type, $callback, $afterPath, $afterParameters)
    {
        $storage = $this->getPayum()->getStorage(Payment::class);
        $payment = $storage->create();
        $callback($payment, $this->gatewayName);
        $storage->update($payment);
        $tokenFactory = $this->getPayum()->getTokenFactory();
        $token = call_user_func_array(
            [$tokenFactory, sprintf('create%sToken', ucfirst($type))],
            [$this->gatewayName, $payment, $afterPath, $afterParameters]
        );

        return $token->getTargetUrl();
    }
}
