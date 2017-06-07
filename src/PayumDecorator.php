<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Payum;
use Illuminate\Http\Request;
use Payum\Core\Model\Payment;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;

class PayumDecorator
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
     * getGatewayName.
     *
     * @return string
     */
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * getGateway.
     *
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->getPayum()->getGateway($this->gatewayName);
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
        return $this->sendRequest('authorize', $callback, $afterPath, $afterParameters);
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
        return $this->sendRequest('cancel', $callback, $afterPath, $afterParameters);
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
        return $this->sendRequest('capture', $callback, $afterPath, $afterParameters);
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
        return $this->sendRequest('refund', $callback, $afterPath, $afterParameters);
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
        return $this->sendRequest('payout', $callback, $afterPath, $afterParameters);
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
        $token = $this->getPayum()->getHttpRequestVerifier()->verify(
            $request->duplicate(null, null, ['payum_token' => $payumToken])
        );
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());
        $gateway->execute($status = new GetHumanStatus($token));

        return $callback($status, $status->getFirstModel(), $token, $gateway);
    }

    /**
     * sendRequest.
     *
     * @param string $method
     * @param callable $callback
     * @param string $afterPath
     * @param array $afterParameters
     * @return string
     */
    protected function sendRequest($method, callable $callback, $afterPath, $afterParameters)
    {
        $storage = $this->getStorage();
        $payment = $storage->create();
        $callback($payment, $this->gatewayName);
        $storage->update($payment);
        $tokenFactory = $this->getPayum()->getTokenFactory();
        $token = call_user_func_array(
            [$tokenFactory, sprintf('create%sToken', ucfirst($method))],
            [$this->gatewayName, $payment, $afterPath, $afterParameters]
        );

        return $token->getTargetUrl();
    }

    /**
     * getStorage.
     *
     * @return \Payum\Core\Model\PaymentInterface
     */
    protected function getStorage()
    {
        return $this->getPayum()->getStorage(
            in_array(EloquentPayment::class, array_keys($this->getPayum()->getStorages()), true) ?
                EloquentPayment::class : Payment::class
        );
    }
}
