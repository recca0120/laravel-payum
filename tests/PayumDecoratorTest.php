<?php

namespace Recca0120\LaravelPayum\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Recca0120\LaravelPayum\PayumDecorator;

class PayumDecoratorTest extends TestCase
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

    public function testGateway()
    {
        $payumDecorator = new PayumDecorator(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $this->assertSame($gateway, $payumDecorator->getGateway());
    }

    public function testDone()
    {
        $payumDecorator = new PayumDecorator(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $request = m::mock('Illuminate\Http\Request');
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
            $gatewayName = 'foo.gateway_name'
        );
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHumanStatus'));
        $payumDecorator->done($request, $payumToken, function ($status) {
            $this->assertInstanceOf('Payum\Core\Request\GetHumanStatus', $status);
        });
    }

    public function testGetStatus()
    {
        $payumDecorator = new PayumDecorator(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $request = m::mock('Illuminate\Http\Request');
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
            $gatewayName = 'foo.gateway_name'
        );
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHumanStatus'));

        $this->assertInstanceOf('Payum\Core\Request\GetHumanStatus', $payumDecorator->getStatus($payumToken, $request));
    }

    public function testGetResult()
    {
        $payumDecorator = new PayumDecorator(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
        );

        $request = m::mock('Illuminate\Http\Request');
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
        $token->shouldReceive('getGatewayName')->twice()->andReturn(
            $gatewayName = 'foo.gateway_name'
        );
        $payum->shouldReceive('getGateway')->once()->with($gatewayName)->andReturn(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $payment = m::mock('stdClass');

        $gateway->shouldReceive('execute')->once()->with(m::type('Payum\Core\Request\GetHumanStatus'))->andReturnUsing(function ($status) use ($payment) {
            $status->setModel($payment);
            $status->markCaptured();
        });

        $payment->shouldReceive('getClientEmail')->once()->andReturn($clientEmail = 'foo@bar.com');
        $payment->shouldReceive('getClientId')->once()->andReturn($clientId = 'foobar');
        $payment->shouldReceive('getCreditCard')->once()->andReturn($creditcard = 'foo-creditcard');
        $payment->shouldReceive('getCurrencyCode')->once()->andReturn($currencyCode = 'NTD');
        $payment->shouldReceive('getDescription')->once()->andReturn($description = 'foo-description');
        $payment->shouldReceive('getDetails')->once()->andReturn($details = ['foo' => 'bar']);
        $payment->shouldReceive('getNumber')->once()->andReturn($number = uniqid());
        $payment->shouldReceive('getTotalAmount')->once()->andReturn($totalAmount = rand(100, 1000));

        $this->assertSame([
            'client_email' => $clientEmail,
            'client_id' => $clientId,
            'creditcard' => $creditcard,
            'currency_code' => $currencyCode,
            'description' => $description,
            'details' => $details,
            'gatewayName' => $gatewayName,
            'number' => $number,
            'status' => 'captured',
            'total_amount' => $totalAmount,
        ], $payumDecorator->getResult($payumToken, $request));
    }

    protected function assertSend($method)
    {
        $payumDecorator = new PayumDecorator(
            $payum = m::mock('Payum\Core\Payum'),
            $gatewayName = 'offline'
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
            $gatewayName, $payment, $afterPath = 'foo.done', $afterParameters = ['foo' => 'bar']
        )->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn($targetUrl = 'foo.target_url');

        $callback = function () {
        };
        $this->assertSame($targetUrl, call_user_func_array([$payumDecorator, $method], [$callback, $afterPath, $afterParameters]));
    }
}
