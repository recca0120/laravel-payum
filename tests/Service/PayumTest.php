<?php

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum as CorePayum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Core\Security\HttpRequestVerifierInterface;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Service\Payum as PayumService;
use Payum\Core\Security\TokenFactoryInterface;

class PayumTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_payum()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($payum, $payment->getPayum());
    }

    public function test_send()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->once()->andReturn($token);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->send($request, $payumToken, function () use ($gateway, $token, $httpRequestVerifier) {
            $args = func_get_args();
            $this->assertSame($args[0], $gateway);
            $this->assertSame($args[1], $token);
            $this->assertSame($args[2], $httpRequestVerifier);
        });
    }

    public function test_get_token_without_payum_token()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $request->cookies = m::mock(stdClass::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';
        $sessionName = 'foo';
        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $sessionManager
            ->shouldReceive('driver')->once()->andReturnSelf()
            ->shouldReceive('isStarted')->once()->andReturn(false)
            ->shouldReceive('getName')->once()->andReturn($sessionName)
            ->shouldReceive('setId')->with($sessionName)->once()
            ->shouldReceive('setRequestOnHandler')->with($request)->once()
            ->shouldReceive('start')->twice()
            ->shouldReceive('forget')->with($payumTokenId)->once()->andReturn(true)
            ->shouldReceive('get')->once()->andReturn($payumToken)
            ->shouldReceive('save')->once();

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $request->cookies->shouldReceive('get')->with($sessionName)->andReturn($sessionName);

        $httpRequestVerifier->shouldReceive('verify')->once()->andReturn($token);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->send($request, null, function () use ($gateway, $token, $httpRequestVerifier) {
            $args = func_get_args();
            $this->assertSame($args[0], $gateway);
            $this->assertSame($args[1], $token);
            $this->assertSame($args[2], $httpRequestVerifier);
        });
    }

    public function test_convert_reply()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $request->cookies = m::mock(stdClass::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $reply = m::mock(ReplyInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';
        $sessionName = 'foo';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $request->cookies->shouldReceive('get')->with($sessionName)->andReturn($sessionName);

        $httpRequestVerifier->shouldReceive('verify')->once()->andReturn($token);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        $sessionManager
            ->shouldReceive('driver')->once()->andReturnSelf()
            ->shouldReceive('isStarted')->once()->andReturn(false)
            ->shouldReceive('getName')->once()->andReturn($sessionName)
            ->shouldReceive('setId')->with($sessionName)->once()
            ->shouldReceive('setRequestOnHandler')->with($request)->once()
            ->shouldReceive('start')->once()
            ->shouldReceive('set')->with($payumTokenId, $payumToken)
            ->shouldReceive('save')->once();

        $replyToSymfonyResponseConverter->shouldReceive('convert')->with(m::type(ReplyInterface::class))->andReturn($reply);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($reply, $payment->send($request, $payumToken, function () {
            throw new HttpResponse('testing');
        }));
    }

    public function test_request_capture()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createCaptureToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->capture($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_authorize()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createAuthorizeToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->authorize($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_refund()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createRefundToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->refund($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_cancel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createCancelToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->cancel($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_payout()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createPayoutToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->payout($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_notify()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $gatewayName = 'fooGatewayName';
        $storage = m::mock(stdClass::class);
        $eloquentPayment = m::mock(EloquentPayment::class);
        $tokenFactory = m::mock(TokenFactoryInterface::class);
        $token = m::mock(TokenInterface::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getTokenFactory')->once()->andReturn($tokenFactory);

        $token->shouldReceive('getTargetUrl')->once()->andReturn($excepted);

        $tokenFactory->shouldReceive('createNotifyToken')->once()->andReturn($token);

        $responseFactory->shouldReceive('redirectTo')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->notify($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_done()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        $gateway->shouldReceive('execute')->with(m::type(GetHumanStatus::class))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payment->done($request, $payumToken, function ($status) use ($gateway, $token, $httpRequestVerifier) {
            $args = func_get_args();
            $this->assertSame($token, $status->getModel());
            $this->assertSame($args[2], $gateway);
            $this->assertSame($args[3], $token);
            $this->assertSame($args[4], $httpRequestVerifier);
        });
    }

    public function test_receive_authorize()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Authorize::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receiveAuthorize($request, $payumToken));
    }

    public function test_receive_capture()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Capture::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receiveCapture($request, $payumToken));
    }

    public function test_receive_notify()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Notify::class));

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        $responseFactory->shouldReceive('make')->once()->andReturn(204);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $response = $payment->receiveNotify($request, $payumToken);
        $this->assertNull($response[0]);
        $this->assertSame(204, $response);
    }

    public function test_receive_notify_unsafe()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $payum->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $gateway->shouldReceive('execute')->with(m::type(Notify::class));

        $responseFactory->shouldReceive('make')->once()->andReturn(204);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $response = $payment->receiveNotifyUnsafe($gatewayName);
        $this->assertSame(204, $response);
    }

    public function test_receive_payout()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Payout::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receivePayout($request, $payumToken));
    }

    public function test_receive_cancel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Cancel::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receiveCancel($request, $payumToken));
    }

    public function test_receive_refund()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Refund::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receiveRefund($request, $payumToken));
    }

    public function test_receive_sync()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock(CorePayum::class);
        $sessionManager = m::mock(SessionManager::class);
        $responseFactory = m::mock(ResponseFactory::class);
        $replyToSymfonyResponseConverter = m::mock(ReplyToSymfonyResponseConverter::class);
        $payment = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock(Request::class);
        $httpRequestVerifier = m::mock(HttpRequestVerifierInterface::class);
        $token = m::mock(TokenInterface::class);
        $payumToken = uniqid();
        $gateway = m::mock(GatewayInterface::class);
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $excepted = 'fooTargetUrl';

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $request->shouldReceive('merge')->with([
            $payumTokenId => $payumToken,
        ])->once();

        $httpRequestVerifier->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token);

        $gateway->shouldReceive('execute')->with(m::type(Sync::class));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payment->receiveSync($request, $payumToken));
    }
}
