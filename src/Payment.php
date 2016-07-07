<?php

namespace Recca0120\LaravelPayum;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Model\Payment as PayumPayment;
use Payum\Core\Payum;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Recca0120\LaravelPayum\Model\Payment as EloquentPayment;
use Symfony\Component\HttpFoundation\Response;

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
     * create.
     *
     * @method create
     *
     * @param string  $gatewayName
     * @param Closure $closure
     *
     * @return mixed
     */
    public function prepare($gatewayName, Closure $closure)
    {
        $storage = $this->payum->getStorage($this->getPaymentModelName());
        $payment = $storage->create();
        $closure($payment, $storage, $this->payum);
        $storage->update($payment);
        $captureToken = $this->payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'payment.done');

        return redirect($captureToken->getTargetUrl());
    }

    /**
     * getPaymentModelName.
     *
     * @method getPaymentModelName
     *
     * @return string
     */
    protected function getPaymentModelName()
    {
        return (in_array(EloquentPayment::class, array_keys($this->payum->getStorages())) === true) ?
            EloquentPayment::class : PayumPayment::class;
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
        return $this->doAction($request, $payumToken, function ($httpRequestVerifier, $gateway, $token) use ($closure) {
            $gateway->execute($status = new GetHumanStatus($token));
            $payment = $status->getFirstModel();

            return $closure($payment, $status);
        });
    }

    /**
     * doAction.
     *
     * @method doAction
     *
     * @param \Illuminate\Http\Request $request
     * @param string                   $payumToken
     * @param \Closure                 $closure
     *
     * @return mixed
     */
    public function doAction(Request $request, $payumToken, Closure $closure)
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
