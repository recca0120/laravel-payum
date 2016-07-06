<?php

namespace Recca0120\LaravelPayum\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Session\SessionManager;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller extends BaseController
{
    /**
     * $payum.
     *
     * @var \Payum\Core\Payum
     */
    protected $payum;

    /**
     * $converter.
     *
     * @var \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter
     */
    protected $converter;

    /**
     * $sessionManager.
     *
     * @var \Illuminate\Session\SessionManager
     */
    protected $sessionManager;

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
     * @param \Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter $converter
     * @param \Illuminate\Session\SessionManager                         $sessionManager
     */
    public function __construct(
        Payum $payum,
        ReplyToSymfonyResponseConverter $converter,
        SessionManager $sessionManager
    ) {
        $this->payum = $payum;
        $this->converter = $converter;
        $this->sessionManager = $sessionManager;
    }

    /**
     * doAction.
     *
     * @method doAction
     *
     * @param \Closure                 $closure
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     *
     * @return mixed
     */
    protected function doAction(Closure $closure, Request $request, $payumToken = null)
    {
        $httpRequestVerifier = $this->payum->getHttpRequestVerifier();
        $token = $this->getToken($httpRequestVerifier, $request, $payumToken);
        $gateway = $this->payum->getGateway($token->getGatewayName());
        try {
            return $closure($httpRequestVerifier, $gateway, $token);
        } catch (ReplyInterface $reply) {
            return $this->convertReply($reply, $payumToken);
        }
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
            $payumToken = $this->sessionManager->get($this->payumTokenId);
            $this->sessionManager->forget($this->payumTokenId);
        }
        $request->merge([$this->payumTokenId => $payumToken]);

        return $httpRequestVerifier->verify($request);
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
}
