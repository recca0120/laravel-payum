<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\PayumWrapper;

class PayumWrapperTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCapture()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $storage->shouldReceive('update')->once()->with($payment);

        $payum->shouldReceive('getTokenFactory')->once()->andReturn(
            $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface')
        );

        $tokenFactory->shouldReceive('createCaptureToken')->once()->with(
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $this->assertSame($targetUrl, $payumWrapper->capture(function () {
        }, $afterPath, $afterParameters));
    }

    public function testAuthorize()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $storage->shouldReceive('update')->once()->with($payment);

        $payum->shouldReceive('getTokenFactory')->once()->andReturn(
            $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface')
        );

        $tokenFactory->shouldReceive('createAuthorizeToken')->once()->with(
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $this->assertSame($targetUrl, $payumWrapper->authorize(function () {
        }, $afterPath, $afterParameters));
    }

    public function testRefund()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $storage->shouldReceive('update')->once()->with($payment);

        $payum->shouldReceive('getTokenFactory')->once()->andReturn(
            $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface')
        );

        $tokenFactory->shouldReceive('createRefundToken')->once()->with(
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $this->assertSame($targetUrl, $payumWrapper->refund(function () {
        }, $afterPath, $afterParameters));
    }

    public function testCancel()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $storage->shouldReceive('update')->once()->with($payment);

        $payum->shouldReceive('getTokenFactory')->once()->andReturn(
            $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface')
        );

        $tokenFactory->shouldReceive('createCancelToken')->once()->with(
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $this->assertSame($targetUrl, $payumWrapper->cancel(function () {
        }, $afterPath, $afterParameters));
    }

    public function testPayout()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $storage->shouldReceive('update')->once()->with($payment);

        $payum->shouldReceive('getTokenFactory')->once()->andReturn(
            $tokenFactory = m::mock('Payum\Core\Security\TokenFactoryInterface')
        );

        $tokenFactory->shouldReceive('createPayoutToken')->once()->with(
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $this->assertSame($targetUrl, $payumWrapper->payout(function () {
        }, $afterPath, $afterParameters));
    }

    public function testDone()
    {
        $payumWrapper = new PayumWrapper(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $request = m::mock('Illuminate\Http\Request');
        $payumToken = 'foo.payum_token';

        $request->shouldReceive('merge')->once()->with([
            'payum_token' => $payumToken,
        ]);
        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn(
            $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface')
        );
        $httpRequestVerifier->shouldReceive('verify')->once()->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getGatewayName')->once()->andReturn(
            $gatewayName = 'foo.gateway_name'
        );
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHumanStatus'));
        $payumWrapper->done($request, $payumToken, function ($status) {
            $this->assertInstanceOf('Payum\Core\Request\GetHumanStatus', $status);
        });
    }
}
