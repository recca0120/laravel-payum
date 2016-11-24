<?php

use Mockery as m;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Recca0120\LaravelPayum\Service\PayumService;

class PayumServiceTest extends PHPUnit_Framework_TestCase
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

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);

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

        $this->assertSame($payum, $payumService->getPayum());
    }

    public function test_send()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $payumService->send($request, $payumToken, function () use ($gateway, $token, $httpRequestVerifier) {
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

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $request->cookies = m::mock('stdClass');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $payumService->send($request, null, function () use ($gateway, $token, $httpRequestVerifier) {
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

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $request->cookies = m::mock('stdClass');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $reply = m::mock('Payum\Core\Reply\ReplyInterface');
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

        $replyToSymfonyResponseConverter->shouldReceive('convert')->with(m::type('Payum\Core\Reply\ReplyInterface'))->andReturn($reply);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($reply, $payumService->send($request, $payumToken, function () {
            throw new HttpResponse('testing');
        }));
    }

    public function test_prepare()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage
            ->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum
            ->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->prepare($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_capture()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->capture($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_authorize()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->authorize($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_refund()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->refund($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_cancel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->cancel($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_request_payout()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $gatewayName = 'fooGatewayName';
        $storage = m::mock('stdClass');
        $eloquentPayment = m::mock('Recca0120\LaravelPayum\Model\Payment');
        $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $excepted = 'fooTargetUrl';

        $storage->shouldReceive('create')->once()->andReturn($eloquentPayment)
            ->shouldReceive('update')->once()->andReturn($eloquentPayment);

        $payum->shouldReceive('getStorages')->once()->andReturn([
                'Recca0120\LaravelPayum\Model\Payment' => 'storage',
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

        $payumService->payout($gatewayName, function () {
        }, 'payment.done', []);
    }

    public function test_done()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHumanStatus'))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payumService->done($request, $payumToken, function ($status) use ($gateway, $token, $httpRequestVerifier) {
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

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Authorize'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receiveAuthorize($request, $payumToken));
    }

    public function test_receive_capture()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Capture'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receiveCapture($request, $payumToken));
    }

    public function test_receive_notify()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Notify'));

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName);

        $responseFactory->shouldReceive('make')->once()->andReturn(204);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $response = $payumService->receiveNotify($request, $payumToken);
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

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $payum->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Notify'));

        $responseFactory->shouldReceive('make')->once()->andReturn(204);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $response = $payumService->receiveNotifyUnsafe($gatewayName);
        $this->assertSame(204, $response);
    }

    public function test_receive_notify_unsafe_throw_reply()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $payumTokenId = 'payum_token';
        $gatewayName = 'fooGatewayName';
        $throwResponse = new HttpResponse('testing');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $payum->shouldReceive('getGateway')->with($gatewayName)->once()->andReturn($gateway);

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Notify'))->andReturnUsing(function () use ($throwResponse) {
            throw $throwResponse;
        });

        $replyToSymfonyResponseConverter->shouldReceive('convert')->once()->andReturn($throwResponse);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $response = $payumService->receiveNotifyUnsafe($gatewayName);
        $this->assertSame($throwResponse, $response);
        $this->assertInstanceOf('Payum\Core\Reply\ReplyInterface', $response);
    }

    public function test_receive_payout()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Payout'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receivePayout($request, $payumToken));
    }

    public function test_receive_cancel()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Cancel'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receiveCancel($request, $payumToken));
    }

    public function test_receive_refund()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Refund'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receiveRefund($request, $payumToken));
    }

    public function test_receive_sync()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $request = m::mock('Illuminate\Http\Request');
        $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface');
        $token = m::mock('Payum\Core\Security\TokenInterface');
        $payumToken = uniqid();
        $gateway = m::mock('Payum\Core\GatewayInterface');
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

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'));

        $responseFactory->shouldReceive('redirectTo')->once()->andReturn($excepted);

        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName)
            ->shouldReceive('getAfterUrl')->once()->andReturn($excepted);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertSame($excepted, $payumService->receiveSync($request, $payumToken));
    }

    public function test_sync()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $payum = m::mock('Payum\Core\Payum');
        $sessionManager = m::mock('Illuminate\Session\SessionManager');
        $responseFactory = m::mock('Illuminate\Contracts\Routing\ResponseFactory');
        $replyToSymfonyResponseConverter = m::mock('Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter');
        $payumService = new PayumService($payum, $sessionManager, $responseFactory, $replyToSymfonyResponseConverter);
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $payment = m::mock('Payum\Core\Model\Payment');
        $tokenInterface = m::mock('Payum\Core\Security\TokenInterface');
        $storage = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $payum
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->shouldReceive('getStorage')->once()->andReturn($storage)
            ->shouldReceive('getStorages')->once()->andReturn([
                'Payum\Core\Model\Payment' => 'storage',
            ]);

        $storage
            ->shouldReceive('create')->once()->andReturn($payment);

        $payment
            ->shouldReceive('setDetails')->once()
            ->shouldReceive('setNumber')->with('foo.number');

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\Convert'))->once()
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\Generic'))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $payumService->sync('foo', function ($payment) {
            $payment->setNumber('foo.number');
        });
    }
}
