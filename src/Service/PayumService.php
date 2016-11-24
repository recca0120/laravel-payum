<?php

namespace Recca0120\LaravelPayum\Service;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;

class PayumService
{
    /**
     * $payum.
     *
     * @var \Payum\Core\Payum
     */
    protected $payum;

    /**
     * $sessionManager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $sessionManager;

    /**
     * $responseFactory.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * $converter.
     *
     * @var \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter
     */
    protected $converter;

    /**
     * $payumTokenId.
     *
     * @var string
     */
    protected $payumTokenId = 'payum_token';

    /**
     * __construct.
     *
     * @method __construct
     *
     * @param \Payum\Core\Payum                                          $payum
     * @param \Illuminate\Session\SessionManager                         $sessionManager
     * @param \Illuminate\Contracts\Routing\ResponseFactory              $responseFactory
     * @param \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter $converter
     */
    public function __construct(
        Payum $payum,
        SessionManager $sessionManager,
        ResponseFactory $responseFactory,
        ReplyToSymfonyResponseConverter $converter
    ) {
        $this->payum = $payum;
        $this->sessionManager = $sessionManager;
        $this->responseFactory = $responseFactory;
        $this->converter = $converter;
    }

    /**
     * getPayum.
     *
     * @method getPayum
     *
     * @return \Payum\Core\Payum
     */
    public function getPayum()
    {
        return $this->payum;
    }

    /**
     * getSession.
     *
     * @method getSession
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Session\Store
     */
    protected function getSession($request)
    {
        $session = $this->sessionManager->driver();
        if ($session->isStarted() === false) {
            $session->setId($request->cookies->get($session->getName()));
            $session->setRequestOnHandler($request);
            $session->start();
        }

        return $session;
    }

    /**
     * getToken.
     *
     * @method getToken
     *
     * @param \Payum\Core\Security\HttpRequestVerifierInterface $httpRequestVerifier
     * @param \Illuminate\Http\Request                          $request
     * @param string                                            $payumToken
     *
     * @return \Payum\Core\Model\Token
     */
    protected function getToken(HttpRequestVerifierInterface $httpRequestVerifier, Request $request, $payumToken = null)
    {
        if (empty($payumToken) === true) {
            $session = $this->getSession($request);
            $payumToken = $session->get($this->payumTokenId);
            $session->forget($this->payumTokenId);
            $session->save();
            $session->start();
        }
        $request->merge([$this->payumTokenId => $payumToken]);

        return $httpRequestVerifier->verify($request);
    }

    /**
     * getGateway.
     *
     * @method getGateway
     *
     * @param string    $gatewayName
     *
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway($gatewayName)
    {
        return $this->getPayum()->getGateway($gatewayName);
    }

    /**
     * send.
     *
     * @method send
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     * @param callable                 $closure
     *
     * @return mixed
     */
    public function send(Request $request, $payumToken, callable $closure)
    {
        $payum = $this->getPayum();
        $httpRequestVerifier = $payum->getHttpRequestVerifier();
        $token = $this->getToken($httpRequestVerifier, $request, $payumToken);
        $gateway = $this->getGateway($token->getGatewayName());
        try {
            return $closure($gateway, $token, $httpRequestVerifier);
        } catch (ReplyInterface $reply) {
            $session = $this->getSession($request);
            $session->set('payum_token', $payumToken);
            $session->save();

            return $this->converter->convert($reply);
        }
    }

    /**
     * getPaymentModelName.
     *
     * @method getPaymentModelName
     *
     * @return string
     */
    protected function getPaymentModelName($payum)
    {
        return (in_array(EloquentPayment::class, array_keys($payum->getStorages())) === true) ?
            EloquentPayment::class : PayumPayment::class;
    }

    /**
     * prepare.
     *
     * @method prepare
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     * @param string   $tokenType
     *
     * @return mixed
     */
    public function prepare($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [], $tokenType = 'Capture')
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, $tokenType);
    }

    /**
     * request.
     *
     * @method request
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     * @param string   $tokenType
     *
     * @return mixed
     */
    public function request($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [], $tokenType = 'Capture')
    {
        $payum = $this->getPayum();
        $storage = $payum->getStorage($this->getPaymentModelName($payum));
        $payment = $storage->create();
        $closure($payment, $gatewayName, $storage, $this->payum);
        $storage->update($payment);
        $tokenFactory = $this->payum->getTokenFactory();
        $method = 'create'.ucfirst($tokenType).'Token';
        $token = call_user_func_array([$tokenFactory, $method], [
            $gatewayName,
            $payment,
            $afterPath,
            $afterParameters,
        ]);

        return $this->responseFactory->redirectTo($token->getTargetUrl());
    }

    /**
     * capture.
     *
     * @method capture
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function capture($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, 'Capture');
    }

    /**
     * authorize.
     *
     * @method authorize
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function authorize($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, 'Authorize');
    }

    /**
     * refund.
     *
     * @method refund
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function refund($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, 'Refund');
    }

    /**
     * cancel.
     *
     * @method cancel
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function cancel($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, 'Cancel');
    }

    /**
     * payout.
     *
     * @method payout
     *
     * @param string   $gatewayName
     * @param callable $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function payout($gatewayName, callable $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        return $this->request($gatewayName, $closure, $afterPath, $afterParameters, 'Payout');
    }

    /**
     * sync.
     *
     * @method sync
     *
     * @param string    $gatewayName
     * @param callable  $closure
     */
    public function sync($gatewayName, callable $closure)
    {
        $payum = $this->getPayum();
        $gateway = $this->getGateway($gatewayName);
        $storage = $payum->getStorage($this->getPaymentModelName($payum));
        $payment = $storage->create();
        $closure($payment, $gatewayName, $storage, $this->payum);

        $request = new Sync($payment);
        $convert = new Convert($payment, 'array', $request->getToken());
        $gateway->execute($convert);
        $payment->setDetails($convert->getResult());
        $gateway->execute($request);

        return $request->getModel();
    }

    /**
     * done.
     *
     * @method done
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     * @param callable                 $closure
     *
     * @return mixed
     */
    public function done(Request $request, $payumToken, callable $closure)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) use ($closure) {
            $gateway->execute($status = new GetHumanStatus($token));
            $payment = $status->getFirstModel();

            return $closure($status, $payment, $gateway, $token, $httpRequestVerifier);
        });
    }

    /**
     * receiveAuthorize.
     *
     * @method receiveAuthorize
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveAuthorize(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * receiveCapture.
     *
     * @method receiveCapture
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveCapture(Request $request, $payumToken = null)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * receiveNotify.
     *
     * @method receiveNotify
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveNotify(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Notify($token));

            return $this->responseFactory->make(null, 204);
        });
    }

    /**
     * receiveNotifyUnsafe.
     *
     * @method receiveNotifyUnsafe
     *
     * @param string $gatewayName
     *
     * @return mixed
     */
    public function receiveNotifyUnsafe($gatewayName)
    {
        try {
            $gateway = $this->getPayum()->getGateway($gatewayName);
            $gateway->execute(new Notify(null));

            return $this->responseFactory->make(null, 204);
        } catch (ReplyInterface $reply) {
            return $this->converter->convert($reply);
        }
    }

    /**
     * receivePayout.
     *
     * @method receivePayout
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receivePayout(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * receiveCancel.
     *
     * @method receiveCancel
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveCancel(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Cancel($token));
            $httpRequestVerifier->invalidate($token);
            $afterUrl = $token->getAfterUrl();
            if (empty($afterUrl) === false) {
                return $this->responseFactory->redirectTo($afterUrl);
            }

            return $this->responseFactory->make(null, 204);
        });
    }

    /**
     * receiveRefund.
     *
     * @method receiveRefund
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveRefund(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);
            $afterUrl = $token->getAfterUrl();
            if (empty($afterUrl) === false) {
                return $this->responseFactory->redirectTo($afterUrl);
            }

            return $this->responseFactory->make(null, 204);
        });
    }

    /**
     * receiveSync.
     *
     * @method receiveSync
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    public function receiveSync(Request $request, $payumToken)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }
}
