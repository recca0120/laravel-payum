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
        $response = $this->assertHandleResponse('handleAuthorize', 'Payum\Core\Request\Authorize');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());

    }

    public function testHandleCancel()
    {
        $response = $this->assertHandleResponse('handleCancel', 'Payum\Core\Request\Cancel');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());
    }

    public function testHandleCapture()
    {
        $response = $this->assertHandleResponse('handleCapture', 'Payum\Core\Request\Capture', null, null);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());
    }

    public function testHandleCaptureThrowReply()
    {
        $response = $this->assertHandleResponse('handleCapture', null, function ($gateway, $session) {
            $gateway->shouldReceive('execute')->once()->andReturnUsing(function () {
                throw new HttpRedirect('http://dev');
            });
            $session->shouldReceive('put')->once()->with('payum_token', 'foo.payum_token');
        });
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertContains('http://dev', $response->getContent());
    }

    public function testHandleNotify()
    {
        $response = $this->assertHandleResponse('handleNotify', 'Payum\Core\Request\Notify', function ($gateway) {
        });
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('', $response->getContent());
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testHandleNotifyUnsafe()
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum')
        );

        $gatewayName = 'foo.gateway_name';
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Notify'));
        $response = $controller->handleNotifyUnsafe($gatewayName);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('', $response->getContent());
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testHandleNotifyUnsafeThrowReply()
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum')
        );
        $gatewayName = 'foo.gateway_name';
        $payum->shouldReceive('getGateway')->once()->andThrow(new HttpRedirect('http://dev'));
        $response = $controller->handleNotifyUnsafe($gatewayName);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame(302, $response->getStatusCode());
        $this->assertContains('http://dev', $response->getContent());
    }

    public function testHandleRefund()
    {
        $response = $this->assertHandleResponse('handleRefund', 'Payum\Core\Request\Refund');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());
    }

    public function testHandlePayout()
    {
        $response = $this->assertHandleResponse('handlePayout', 'Payum\Core\Request\Payout');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());
    }

    public function testHandleSync()
    {
        $response = $this->assertHandleResponse('handleSync', 'Payum\Core\Request\Sync');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertSame('foo.after_url', $response->getTargetUrl());
    }

    protected function assertHandleResponse($method, $assertRequest, callable $callback = null, $payumToken = 'foo.payum_token')
    {
        $controller = new WebhookController(
            $payum = m::mock('\Payum\Core\Payum')
        );

        $request = m::mock('\Illuminate\Http\Request');

        $request->shouldReceive('session')->once()->andReturn(
            $session = m::mock('stdClass')
        );

        if (is_null($payumToken) === true) {
            $session->shouldReceive('remove')->once()->with('payum_token')->andReturn('foo.payum_token');
        }
        $request->shouldReceive('duplicate')->once()->with(null, null, ['payum_token' => 'foo.payum_token'])->andReturn(
            $duplicateRequest = m::mock('\Illuminate\Http\Request')
        );
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
            $callback = function($gateway, $session, $httpRequestVerifier, $token) {
                $httpRequestVerifier->shouldReceive('invalidate')->once()->with($token);
                $token->shouldReceive('getAfterUrl')->once()->andReturn($afterUrl = 'foo.after_url');
            };
        }

        $callback($gateway, $session, $httpRequestVerifier, $token);

        return call_user_func_array([$controller, $method], [$request, $payumToken]);
    }
}
