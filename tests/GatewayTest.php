<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\Gateway;

class GatewayTest extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        m::close();
    }

    public function testAuthorize()
    {
        $this->assertSend('authorize');
    }

    public function testCancel()
    {
        $this->assertSend('cancel');
    }

    public function testCapture()
    {
        $this->assertSend('capture');
    }

    public function testRefund()
    {
        $this->assertSend('refund');
    }

    public function testPayout()
    {
        $this->assertSend('payout');
    }

    public function testSync()
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $payum->shouldReceive('getGateway')->once()->with($name)->andReturn(
            $payumGateway = m::mock('Payum\Core\GatewayInterface')
        );

        $payum->shouldReceive('getStorages')->once()->andReturn([
            'Payum\Core\Model\Paymen' => null,
        ]);

        $payum->shouldReceive('getStorage')->once()->with('Payum\Core\Model\Payment')->andReturn(
            $storage = m::mock('Payum\Core\Storage\StorageInterface')
        );

        $storage->shouldReceive('create')->once()->andReturn(
            $payment = m::mock('Payum\Core\Model\Payment')
        );

        $payumGateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Convert'));
        $payment->shouldReceive('setDetails');

        $payumGateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\Sync'));

        $this->assertInstanceOf('Payum\Core\Model\Payment', $gateway->sync(function () {
        }));
    }

    public function testDriver()
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $this->assertSame($name, $gateway->driver());
    }

    public function testGetPayum()
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $this->assertSame($payum, $gateway->getPayum());
    }

    public function testGetGateway()
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $payum->shouldReceive('getGateway')->once()->with($name)->andReturn(
            $payumGateway = m::mock('Payum\Core\GatewayInterface')
        );

        $this->assertSame($payumGateway, $gateway->getGateway());
    }

    public function testGetStatus()
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $payumToken = 'foo.payum_token';

        $request->shouldReceive('duplicate')->once()->with(null, null, ['payum_token' => $payumToken])->andReturn(
            $duplicateRequest = m::mock('Illuminate\Http\Request')
        );
        $payum->shouldReceive('getHttpRequestVerifier')->once()->andReturn(
            $httpRequestVerifier = m::mock('Payum\Core\Security\HttpRequestVerifierInterface')
        );
        $httpRequestVerifier->shouldReceive('verify')->once()->with($duplicateRequest)->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );
        $token->shouldReceive('getGatewayName')->once()->andReturn(
            $name = 'foo.gateway_name'
        );
        $payum->shouldReceive('getGateway')->once()->with($name)->andReturn(
            $payumGateway = m::mock('Payum\Core\GatewayInterface')
        );
        $payumGateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHumanStatus'));

        $this->assertInstanceOf('Payum\Core\Request\GetHumanStatus', $gateway->getStatus($payumToken));
    }

    protected function assertSend($method)
    {
        $gateway = new Gateway(
            $payum = m::mock('Payum\Core\Payum'),
            $request = m::mock('illuminate\Http\Request'),
            $name = 'offline'
        );

        $payum->shouldReceive('getStorages')->once()->andReturn([
            'Payum\Core\Model\Paymen' => null,
        ]);

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

        $tokenFactory->shouldReceive('create'.ucfirst($method).'Token')->once()->with(
            $name, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $callback = function () {
        };
        $this->assertSame($targetUrl, call_user_func_array([$gateway, $method], [$callback, $afterPath, $afterParameters]));
    }
}
