<?php

namespace Recca0120\LaravelPayum\Service;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Payment
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
     * @param \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter $converter
     */
    public function __construct(
        Payum $payum,
        SessionManager $sessionManager,
        ReplyToSymfonyResponseConverter $converter
    ) {
        $this->payum = $payum;
        $this->converter = $converter;
        $this->sessionManager = $sessionManager;
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
     * getToken.
     *
     * @method getToken
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return \Payum\Core\Model\Token
     */
    protected function getToken(HttpRequestVerifierInterface $httpRequestVerifier, Request $request, $payumToken = null)
    {
        if (empty($payumToken) === true) {
            if ($this->sessionManager->isStarted() === false) {
                throw new HttpException(400, 'Session must be started.');
            }

            $payumToken = $this->sessionManager->get($this->payumTokenId);
            $this->sessionManager->forget($this->payumTokenId);
        }
        $request->merge([$this->payumTokenId => $payumToken]);

        return $httpRequestVerifier->verify($request);
    }

    /**
     * send.
     *
     * @method send
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     * @param \Closure                 $closure
     *
     * @return mixed
     */
    public function send(Request $request, $payumToken, Closure $closure)
    {
        $payum = $this->getPayum();
        $httpRequestVerifier = $payum->getHttpRequestVerifier();
        $token = $this->getToken($httpRequestVerifier, $request, $payumToken);
        $gateway = $payum->getGateway($token->getGatewayName());
        try {
            return $closure($gateway, $token, $httpRequestVerifier);
        } catch (ReplyInterface $reply) {
            return $this->convertReply($reply, $payumToken);
        }
    }

    /**
     * convertReply.
     *
     * @method convertReply
     *
     * @param ReplyInterface $reply
     * @param string         $payumToken
     *
     * @return Response
     */
    protected function convertReply(ReplyInterface $reply, $payumToken)
    {
        $this->sessionManager->set('payum_token', $payumToken);

        return $this->converter->convert($reply);
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
     * create.
     *
     * @method create
     *
     * @param string   $gatewayName
     * @param \Closure $closure
     * @param string   $afterPath
     * @param array    $afterParameters
     *
     * @return mixed
     */
    public function prepare($gatewayName, Closure $closure, $afterPath = 'payment.done', array $afterParameters = [])
    {
        $payum = $this->getPayum();
        $storage = $payum->getStorage($this->getPaymentModelName($payum));
        $payment = $storage->create();
        $closure($payment, $gatewayName, $storage, $this->payum);
        $storage->update($payment);
        $captureToken = $this->payum
            ->getTokenFactory()
            ->createCaptureToken($gatewayName, $payment, $afterPath, $afterParameters);

        return redirect($captureToken->getTargetUrl());
    }

    /**
     * done.
     *
     * @method done
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     * @param \Closure                 $closure
     *
     * @return mixed
     */
    public function done(Request $request, $payumToken, Closure $closure)
    {
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) use ($closure) {
            $gateway->execute($status = new GetHumanStatus($token));
            $payment = $status->getFirstModel();

            return $closure($status, $payment, $gateway, $token, $httpRequestVerifier);
        });
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
        $gateway = $this->getPayum()->getGateway($gatewayName);
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);
            $afterUrl = $token->getAfterUrl();
            if (empty($afterUrl) === false) {
                return redirect($afterUrl);
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
        return $this->send($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return redirect($token->getAfterUrl());
        });
    }
}
