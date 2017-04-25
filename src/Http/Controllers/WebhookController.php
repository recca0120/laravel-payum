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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * __construct.
     *
     * @param \Payum\Core\Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Authorize($token));
            $httpRequestVerifier->invalidate($token);

            return new RedirectResponse($token->getAfterUrl());
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Cancel($token));
            $httpRequestVerifier->invalidate($token);

            $afterUrl = $token->getAfterUrl();
            if (empty($afterUrl) === false) {
                return new RedirectResponse($afterUrl);
            }

            return new Response(null, 204);
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Capture($token));
            $httpRequestVerifier->invalidate($token);

            return new RedirectResponse($token->getAfterUrl());
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Notify($token));

            return new Response(null, 204);
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

            return new Response(null, 204);
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Refund($token));
            $httpRequestVerifier->invalidate($token);

            $afterUrl = $token->getAfterUrl();
            if (empty($afterUrl) === false) {
                return new RedirectResponse($afterUrl);
            }

            return new Response(null, 204);
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Payout($token));
            $httpRequestVerifier->invalidate($token);

            return new RedirectResponse($token->getAfterUrl());
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
        return $this->handleResponse($request, $payumToken, function ($gateway, $token, $httpRequestVerifier) {
            $gateway->execute(new Sync($token));
            $httpRequestVerifier->invalidate($token);

            return new RedirectResponse($token->getAfterUrl());
        });
    }

    /**
     * handleResponse.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $payumToken
     * @param callable $callback
     * @return \Illuminate\Http\Response
     */
    protected function handleResponse(Request $request, $payumToken, callable $callback)
    {
        $tokenName = 'payum_token';

        $session = $request->session();

        $payumToken = $payumToken ?: $session->remove($tokenName);

        $httpRequestVerifier = $this->getPayum()->getHttpRequestVerifier();

        $token = $httpRequestVerifier->verify(
            $request->duplicate(null, null, [$tokenName => $payumToken])
        );

        $gateway = $this->getPayum()->getGateway($token->getGatewayName());

        try {
            return $callback($gateway, $token, $httpRequestVerifier, $request);
        } catch (ReplyInterface $reply) {
            call_user_func_array([
                $session,
                method_exists($session, 'set') === true ? 'set' : 'put',
            ], [$tokenName, $payumToken]);

            return $this->convertReply($reply);
        }
    }

    /**
     * getPayum.
     *
     * @return \Payum\Core\Payum
     */
    protected function getPayum()
    {
        return $this->payum;
    }

    /**
     * convertReply.
     *
     * @param  \Payum\Core\Reply\ReplyInterface $reply
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertReply($reply)
    {
        return (new ReplyToSymfonyResponseConverter)->convert($reply);
    }
}
