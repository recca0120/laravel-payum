<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Payum\Core\Payum;
use Illuminate\Http\Request;
use Payum\Core\Request\Sync;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Authorize;
use Illuminate\Routing\Controller;
use Payum\Core\Reply\ReplyInterface;
use Illuminate\Contracts\Routing\ResponseFactory;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;

class WebhookController extends Controller
{
    /**
     * $payum.
     *
     * @var \Payum\Core\Payum
     */
    protected $payum;

    /**
     * $responseFactory.
     *
     * @var \Illuminate\Contracts\Routing\ResponseFactory
     */
    protected $responseFactory;

    /**
     * $replyToSymfonyResponseConverter.
     *
     * @var \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter
     */
    protected $replyToSymfonyResponseConverter;

    /**
     * __construct.
     *
     * @param \Payum\Core\Payum $payum
     * @param \Illuminate\Contracts\Routing\ResponseFactory $responseFactory
     * @param \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter
     */
    public function __construct(
        Payum $payum,
        ResponseFactory $responseFactory,
        ReplyToSymfonyResponseConverter $replyToSymfonyResponseConverter
    ) {
        $this->payum = $payum;
        $this->responseFactory = $responseFactory;
        $this->replyToSymfonyResponseConverter = $replyToSymfonyResponseConverter;
    }

    /**
     * handleAuthorize.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleAuthorize(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * handleCancel.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleCancel(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
     * handleCapture.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleCapture(Request $request, $payumToken = null)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * handleNotify.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleNotify(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Notify($token));

            return $this->responseFactory->make(null, 204);
        });
    }

    /**
     * handleNotifyUnsafe.
     *
     * @param string $gatewayName
     * @return \Illuminate\Http\Response
     */
    public function handleNotifyUnsafe($gatewayName)
    {
        try {
            $gateway = $this->getPayum()->getGateway($gatewayName);
            $gateway->execute(new Notify(null));

            return $this->responseFactory->make(null, 204);
        } catch (ReplyInterface $reply) {
            return $this->convertReply($reply);
        }
    }

    /**
     * handleRefund.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleRefund(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
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
     * handlePayout.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handlePayout(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * handleSync.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @return \Illuminate\Http\Response
     */
    public function handleSync(Request $request, $payumToken)
    {
        return $this->handleReceived($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return $this->responseFactory->redirectTo($token->getAfterUrl());
        });
    }

    /**
     * handleReceived.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @param callable $callback
     * @return \Illuminate\Http\Response
     */
    protected function handleReceived(Request $request, $payumToken, callable $callback)
    {
        $tokenName = 'payum_token';
        if (is_null($payumToken) === true) {
            $payumToken = $request->session()->remove($tokenName);
        }

        if (is_null($payumToken) === false) {
            $request->merge([$tokenName => $payumToken]);
        }

        $httpRequestVerifier = $this->getPayum()->getHttpRequestVerifier();
        $token = $httpRequestVerifier->verify($request);
        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            return $callback($gateway, $token, $httpRequestVerifier, $request);
        } catch (ReplyInterface $reply) {
            $session = $request->session();
            $method = method_exists($session, 'set') === true ? 'set' : 'put';
            call_user_func_array([$session, $method], [$tokenName, $payumToken]);

            return $this->convertReply($reply);
        }
    }

    protected function getPayum()
    {
        return $this->payum;
    }

    protected function convertReply($reply)
    {
        return $this->replyToSymfonyResponseConverter->convert($reply);
    }
}
