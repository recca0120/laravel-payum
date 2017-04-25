<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Payum;
use Illuminate\Http\Request;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;

class PayumWrapper
{
    /**
     * $gatewayName.
     *
     * @var string
     */
    public $gatewayName;
    /**
     * $payum.
     *
     * @var \Payum\Core\Payum
     */
    protected $payum;

    /**
     * __construct.
     *
     * @param \Payum\Core\Payum $payum
     * @param string $gatewayName
     */
    public function __construct(Payum $payum, $gatewayName)
    {
        $this->payum = $payum;
        $this->gatewayName = $gatewayName;
    }

    /**
     * getPayum.
     *
     * @return \Payum\Core\Payum
     */
    public function getPayum()
    {
        return $this->payum;
    }

    /**
     * authorize.
     *
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    public function authorize(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('authorize', $callback, $afterPath, $afterParameters);
    }

    /**
     * cancel.
     *
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    public function cancel(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('cancel', $callback, $afterPath, $afterParameters);
    }

    /**
     * capture.
     *
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    public function capture(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('capture', $callback, $afterPath, $afterParameters);
    }

    /**
     * refund.
     *
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    public function refund(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('refund', $callback, $afterPath, $afterParameters);
    }

    /**
     * payout.
     *
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    public function payout(callable $callback, $afterPath = 'payum.done', array $afterParameters = [])
    {
        return $this->send('payout', $callback, $afterPath, $afterParameters);
    }

    /**
     * done.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @param callable $callback
     * @return mixed
     */
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

    /**
     * payout.
     *
     * @param string $type
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    protected function send($type, callable $callback, $afterPath, $afterParameters)
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
