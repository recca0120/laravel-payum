<?php

namespace Recca0120\LaravelPayum;

use Payum\Core\Payum;
use Illuminate\Http\Request;
use Payum\Core\Request\Sync;
use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;

class Gateway
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
     * $payum.
     *
     * @var \Payum\Core\Payum
     */
    protected $request;

    /**
     * __construct.
     *
     * @param \Payum\Core\Payum $payum
     * @param \Illuminate\Http\Request $request
     * @param string $name
     */
    public function __construct(Payum $payum, Request $request, $name)
    {
        $this->payum = $payum;
        $this->request = $request;
        $this->name = $name;
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
     * getName.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * driver.
     *
     * @return string
     */
    public function driver()
    {
        return $this->getName();
    }

    /**
     * getGateway.
     *
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->getPayum()->getGateway($this->name);
    }

    /**
     * execute.
     *
     * @param mixed $request
     * @param bool $catchReply
     * @return mixed
     */
    public function execute($request, $catchReply = false)
    {
        $this->getGateway($request, $catchReply);

        return $request;
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
     * sync.
     *
     * @param callable $callback
     * @return mixed
     */
    public function sync(callable $callback)
    {
        $gateway = $this->getGateway();
        $storage = $this->getStorage();
        $payment = $storage->create();

        $callback($payment, $storage, $this->driver(), $this->getPayum());

        $request = new Sync($payment);
        $convert = new Convert($payment, 'array', $request->getToken());

        $gateway->execute($convert);
        $payment->setDetails($convert->getResult());

        $gateway->execute($request);

        return $request->getModel();
    }

    /**
     * getStatus.
     *
     * @param string $payumToken
     * @return \Payum\Core\Request\GetHumanStatus
     */
    public function getStatus($payumToken)
    {
        $token = $this->getPayum()->getHttpRequestVerifier()->verify(
            $this->request->duplicate(null, null, ['payum_token' => $payumToken])
        );
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());
        $gateway->execute($status = new GetHumanStatus($token));

        return $status;
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
        $callback($payment, $this->name);
        $storage->update($payment);
        $tokenFactory = $this->getPayum()->getTokenFactory();
        $token = call_user_func_array(
            [$tokenFactory, sprintf('create%sToken', ucfirst($method))],
            [$this->name, $payment, $afterPath, $afterParameters]
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
