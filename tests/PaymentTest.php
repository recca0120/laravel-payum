<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Payment;

class PaymentServiceTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_prepare()
    {
        $gatewayName = 'gatewayName';

        $payment = m::mock(EloquentPayment::class)
            ->shouldReceive('setClientEmail')->once()
            ->mock();

        $storage = m::mock(stdClass::class)
            ->shouldReceive('create')->andReturn($payment)
            ->shouldReceive('update')->andReturn($payment)
            ->mock();

        $captureToken = m::mock(stdClass::class)
            ->shouldReceive('getTargetUrl')->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getStorages')->andReturn([
                EloquentPayment::class => 'storage',
            ])
            ->shouldReceive('getStorage')->andReturn($storage)
            ->shouldReceive('getTokenFactory')->andReturnSelf()
            ->shouldReceive('createCaptureToken')->andReturn($captureToken)
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->prepare($gatewayName, function ($payment) {
            $payment->setClientEmail('test@test.com');
        });
    }

    public function test_done()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

        $request = m::mock(Request::class)
            ->shouldReceive('merge')
            ->mock();

        $httpRequestVerifier = m::mock(HttpRequestVerifier::class)
            ->shouldReceive('verify')->with($request)->once()->andReturn($token)
            ->mock();

        $gateway = m::mock(GatewayInterface::class)
            ->shouldReceive('execute')->once()
            ->mock();

        $payum = m::mock(Payum::class)
            ->shouldReceive('getHttpRequestVerifier')->once()->andReturn($httpRequestVerifier)
            ->shouldReceive('getGateway')->once()->andReturn($gateway)
            ->mock();

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $payment->done($request, $token, function ($payment) {
        });
    }
}
