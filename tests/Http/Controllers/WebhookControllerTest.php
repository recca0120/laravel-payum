<?php

namespace Recca0120\LaravelPayum\Tests\Http\Controllers;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Reply\HttpRedirect;
use Recca0120\LaravelPayum\Http\Controllers\WebhookController;

class WebhookControllerTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testHandleAuthorize()
    {
        $this->assertHandleReceived('handleAuthorize', 'Payum\Core\Request\Authorize');
    }

    public function testHandleCancel()
    {
        $this->assertHandleReceived('handleCancel', 'Payum\Core\Request\Cancel');
    }

    public function testHandleCapture()
    {
        $this->assertHandleReceived('handleCapture', 'Payum\Core\Request\Capture', null, null);
    }

    public function testHandleCaptureThrowReply()
    {
        $this->assertHandleReceived('handleCapture', null, function ($payum, $responseFactory, $replyToSymfonyResponseConverter, $gateway, $token, $httpRequestVerifier, $request) {
            $gateway->shouldReceive('execute')->once()->andReturnUsing(function () {
                throw new HttpRedirect('http://dev');
            });
            $request->shouldReceive('session')->andReturn(
                $session = m::mock('stdClass')
            );
            $session->shouldReceive('put')->with('payum_token', 'foo.payum_token');
            $replyToSymfonyResponseConverter->shouldReceive('convert')->once()->andReturn('foo');
        });
    }

    public function testHandleNotify()
    {
        $this->assertHandleReceived('handleNotify', 'Payum\Core\Request\Notify', function ($payum, $responseFactory) {
            $responseFactory->shouldReceive('make')->once()->with(null, 204)->andReturn('foo');
        });
    }

    public function testHandleNotifyUnsafe()
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum'),
            $responseFactory = m::mock('\Illuminate\Contracts\Routing\ResponseFactory'),
            $replyToSymfonyResponseConverter = m::mock('\Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')
        );

        $gatewayName = 'foo.gateway_name';
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Notify'));
        $responseFactory->shouldReceive('make')->once()->with(null, 204)->andReturn('foo');

        $this->assertSame('foo', $controller->handleNotifyUnsafe($gatewayName));
    }

    public function testHandleNotifyUnsafeThrowReply()
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum'),
            $responseFactory = m::mock('\Illuminate\Contracts\Routing\ResponseFactory'),
            $replyToSymfonyResponseConverter = m::mock('\Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')
        );
        $gatewayName = 'foo.gateway_name';
        $payum->shouldReceive('getGateway')->once()->andThrow(new HttpRedirect('http://dev'));
        $replyToSymfonyResponseConverter->shouldReceive('convert')->once()->andReturn('foo');
        $this->assertSame('foo', $controller->handleNotifyUnsafe($gatewayName));
    }

    public function testHandleRefund()
    {
        $this->assertHandleReceived('handleRefund', 'Payum\Core\Request\Refund');
    }

    public function testHandlePayout()
    {
        $this->assertHandleReceived('handlePayout', 'Payum\Core\Request\Payout');
    }

    public function testHandleSync()
    {
        $this->assertHandleReceived('handleSync', 'Payum\Core\Request\Sync');
    }

    protected function assertHandleReceived($method, $assertRequest, callable $callback = null, $payumToken = 'foo.payum_token')
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum'),
            $responseFactory = m::mock('\Illuminate\Contracts\Routing\ResponseFactory'),
            $replyToSymfonyResponseConverter = m::mock('\Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter')
        );

        $request = m::mock('\Illuminate\Http\Request');

        if (is_null($payumToken) === true) {
            $request->shouldReceive('session->remove')->once()->with('payum_token')->andReturn('foo.payum_token');
        }

        $request->shouldReceive('duplicate')->once()->andReturn(
            $duplicateRequest = m::mock('\Illuminate\Http\Request')
        );

        $duplicateRequest->shouldReceive('merge')->once()->with([
            'payum_token' => 'foo.payum_token',
        ]);

        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn(
            $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface')
        );
        $httpRequestVerifier->shouldReceive('verify')->once()->with($duplicateRequest)->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getGatewayName')->once()->andReturn($gatewayName = 'foo.gateway_name');
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        if (is_null($assertRequest) === false) {
            $gateway->shouldReceive('execute')->once()->with(m::on(function ($request) use ($assertRequest) {
                return is_a($request, $assertRequest) === true;
            }));
        }

        if (is_null($callback) === true) {
            $httpRequestVerifier->shouldReceive('invalidate')->once()->with($token);
            $token->shouldReceive('getAfterUrl')->once()->andReturn($afterUrl = 'foo.after_url');
            $responseFactory->shouldReceive('redirectTo')->once()->with($afterUrl)->andReturn('foo');
        } else {
            $callback(
                $payum,
                $responseFactory,
                $replyToSymfonyResponseConverter,
                $gateway,
                $token,
                $httpRequestVerifier,
                $request
            );
        }

        $this->assertSame('foo', call_user_func_array([$controller, $method], [$request, $payumToken]));
    }
}
