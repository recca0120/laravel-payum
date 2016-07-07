<?php

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Mockery as m;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Bridge\Symfony\Security\HttpRequestVerifier;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Payout;
use Payum\Core\Security\TokenInterface;
use Recca0120\LaravelPayum\Http\Controllers\PaymentController;
use Recca0120\LaravelPayum\Payment;

class PayoutControllerTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_payout()
    {
        $payumToken = uniqid();

        $token = m::mock(TokenInterface::class)
            ->shouldReceive('getGatewayName')->andReturn('test')
            ->shouldReceive('getAfterUrl')->andReturn('test')
            ->mock();

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

        $sessionManager = m::mock(SessionManager::class);

        $converter = m::mock(ReplyToSymfonyResponseConverter::class);

        $payment = new Payment($payum, $sessionManager, $converter);
        $controller = new PaymentController();
        $controller->payout($payment, $request, $payumToken);
    }
}
