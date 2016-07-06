<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Reply\ReplyInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Payout;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Sync;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_authorize()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token)->once()
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Authorize::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->authorize($request, $payumToken);
    }

    public function test_capture()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token)->once()
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Capture::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->capture($request, $payumToken);
    }

    public function test_notify()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Notify::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->notify($request, $payumToken);
    }

    public function test_notify_unsafe()
    {
        $payumToken = uniqid();
        $gatewayName = 'test';

        $sessionManager = m::mock(SessionManager::class);

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Notify::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->notifyUnsafe($gatewayName);
    }

    public function test_payout()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('invalidate')->with($token)->once()
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Payout::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->payout($request, $payumToken);
    }

    public function test_refund()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token)->once()
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Refund::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->refund($request, $payumToken);
    }

    public function test_sync()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('invalidate')->with($token)->once()
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Sync::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->sync($request, $payumToken);
    }

    public function test_capture_without_payum_token()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class)
            ->shouldReceive('get')->with('payum_token')->andReturn($payumToken)
            ->shouldReceive('forget')->with('payum_token')
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->shouldReceive('invalidate')->with($token)->once()
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Capture::class))->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->capture($request, null);
    }

    public function test_throw_reply_interface()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $sessionManager = m::mock(SessionManager::class)
            ->shouldReceive('set')->with('payum_token', $payumToken)->once()
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')->with([
                'payum_token' => $payumToken,
            ])->once()
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->with(m::type(Capture::class))->once()->andReturnUsing(function ($capture) {
                throw new HttpResponse('test');
            })
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $converter = m::mock(ReplyToSymfonyResponseConverter::class)
            ->shouldReceive('convert')->with(m::type(ReplyInterface::class))->once()
            ->mock();

        $controller = new PaymentController($payum, $converter, $sessionManager);
        $controller->capture($request, $payumToken);
    }
}

function redirect()
{
}

function response()
{
}
